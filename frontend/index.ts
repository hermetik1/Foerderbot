/**
 * Main entry point for the KI Kraft chat widget.
 */

import './styles/base.css';
import './styles/layout.css';
import './styles/components.css';
import './styles/themes.css';

interface Message {
	role: 'user' | 'assistant';
	content: string;
	sources?: Array<{ title: string; score: number }>;
}

interface BrandingConfig {
	product_name: string;
	primary_color: string;
	secondary_color: string;
	theme: string;
	icon_color: string;
	header_text_color: string;
	faq_header_title: string;
	advisor_header_title: string;
	logo_url?: string;
	favicon_url?: string;
	footer_text?: string;
	privacy_url?: string;
	imprint_url?: string;
	powered_by?: boolean;
}

interface KraftAIChatSettings {
	general: Record<string, any>;
	accounts: Record<string, any>;
}

interface KraftAIChatConfig {
	apiUrl: string;
	nonce: string;
	version: string;
	user: {
		loggedIn: boolean;
		userId?: number;
		displayName?: string;
		avatarUrl?: string;
		roles?: string[];
	};
	branding: BrandingConfig;
	settings: KraftAIChatSettings;
	i18n: {
		send: string;
		typing: string;
		placeholder: string;
		close: string;
	};
}

declare global {
	interface Window {
		kraftAIChatConfig: KraftAIChatConfig;
		kraftAIChatBranding: BrandingConfig;
		KIKraftConfig: KraftAIChatConfig; // Legacy support
	}
}

// 🧠 Universal Safe Config Initialization
// Ensure window.kraftAIChatConfig always exists with safe defaults
window.kraftAIChatConfig = window.kraftAIChatConfig || {} as KraftAIChatConfig;
window.kraftAIChatConfig.apiUrl = window.kraftAIChatConfig.apiUrl || '';
window.kraftAIChatConfig.nonce = window.kraftAIChatConfig.nonce || '';
window.kraftAIChatConfig.version = window.kraftAIChatConfig.version || '1.0.0';
window.kraftAIChatConfig.branding = window.kraftAIChatConfig.branding || {} as BrandingConfig;
window.kraftAIChatConfig.settings = window.kraftAIChatConfig.settings || { general: {}, accounts: {} };
window.kraftAIChatConfig.user = window.kraftAIChatConfig.user || { loggedIn: false };
window.kraftAIChatConfig.i18n = window.kraftAIChatConfig.i18n || {
	send: 'Send',
	typing: 'Typing...',
	placeholder: 'Type your message...',
	close: 'Close'
};

const DEFAULT_BRANDING: BrandingConfig = {
	product_name: 'Chat Assistant',
	primary_color: '#2563eb',
	secondary_color: '#60a5fa',
	theme: 'auto',
	icon_color: '#2563eb',
	header_text_color: '#111827',
	faq_header_title: 'Häufige Fragen',
	advisor_header_title: 'Mitglieder-Chat',
};

// ✅ Merge defaults with localized config
const branding: BrandingConfig = { ...DEFAULT_BRANDING, ...window.kraftAIChatConfig.branding };
// Expose unified branding globally for use in components
window.kraftAIChatBranding = branding;

// Legacy support: Keep KIKraftConfig as alias to kraftAIChatConfig
window.KIKraftConfig = window.kraftAIChatConfig;

class KIKraftWidget {
	private container: HTMLElement | null = null;
	private railOpen = false;
	private sidebarOpen = false;
	private messages: Message[] = [];
	private sessionId: string | null = null;
	private isTyping = false;
	private isFloating = false;
	private type: string = 'faq';
	private sessions: any[] = [];
	private currentSessionId: string | null = null;

	constructor() {
		this.init();
	}

	private init() {
		// Prevent double-mount
		if (document.querySelector('.kk-widget.kk-initialized')) {
			return;
		}

		// Find widget containers
		const containers = document.querySelectorAll('.kk-widget');
		if (containers.length === 0) return;

		containers.forEach((container) => {
			this.container = container as HTMLElement;
			this.isFloating = this.container.classList.contains('kk-floating');
			this.type = this.container.dataset.type || 'faq';
			this.container.classList.add('kk-initialized');
			this.render();
		});
	}

	private render() {
		if (!this.container) return;

		const type = this.container.getAttribute('data-type') || 'faq';
		const config = window.kraftAIChatConfig;

		// Check if member chat requires login
		if (type === 'member' && !config.user.loggedIn) {
			this.renderLoginPrompt();
			return;
		}

		// Check for fullscreen mode (member type without floating class)
		if (type === 'member' && !this.isFloating) {
			this.renderFullscreen();
			return;
		}

		// Floating mode: render bubble + sidebar
		if (this.isFloating) {
			this.renderFloating(type);
		} else {
			// Regular mode: render rail + sidebar
			this.renderRegular(type);
		}

		this.attachEventListeners();
		this.restoreState();
	}

	private renderFloating(type: string) {
		if (!this.container) return;

		const branding = window.kraftAIChatBranding;
		const config = window.kraftAIChatConfig;

		this.container.innerHTML = `
			<button class="kk-floating-bubble" aria-label="Open chat" title="Chat">
				💬
			</button>
			
			<div class="kk-sidebar" data-theme="light" style="display: none;">
				<div class="kk-sidebar__header">
					<div class="kk-header-info">
						<strong>${branding.product_name}</strong>
						<span class="kk-type-badge">${type.toUpperCase()}</span>
					</div>
					<button class="kk-close-btn" aria-label="Close">✕</button>
				</div>
				<div class="kk-chat" role="log" aria-live="polite">
					<div class="kk-messages"></div>
				</div>
				<div class="kk-composer">
					<textarea 
						class="kk-input" 
						placeholder="${config.i18n.placeholder}"
						rows="1"
					></textarea>
					<button class="kk-button-primary kk-send-btn">${config.i18n.send}</button>
				</div>
			</div>
		`;
	}

	private renderRegular(type: string) {
		if (!this.container) return;

		const branding = window.kraftAIChatBranding;
		const config = window.kraftAIChatConfig;

		this.container.innerHTML = `
			<div class="kk-rail" data-theme="light">
				${
					config.user.loggedIn && config.user.avatarUrl
						? `<img src="${config.user.avatarUrl}" class="kk-rail-avatar" alt="User" />`
						: '<div class="kk-rail-avatar-placeholder">?</div>'
				}
				<button class="kk-rail-btn kk-chat-btn" aria-label="Open chat" title="Chat">
					💬
				</button>
				<button class="kk-rail-btn kk-theme-toggle" aria-label="Toggle theme" title="Theme">
					🌗
				</button>
			</div>
			
			<div class="kk-sidebar" data-theme="light">
				<div class="kk-sidebar__header">
					<div class="kk-header-info">
						<strong>${branding.product_name}</strong>
						<span class="kk-type-badge">${type.toUpperCase()}</span>
					</div>
					<button class="kk-close-btn" aria-label="Close">✕</button>
				</div>
				<div class="kk-chat" role="log" aria-live="polite">
					<div class="kk-messages"></div>
				</div>
				<div class="kk-composer">
					<textarea 
						class="kk-input" 
						placeholder="${config.i18n.placeholder}"
						rows="1"
					></textarea>
					<button class="kk-button-primary kk-send-btn">${config.i18n.send}</button>
				</div>
			</div>
		`;
	}

	private renderFullscreen() {
		if (!this.container) return;

		const branding = window.kraftAIChatBranding;
		const config = window.kraftAIChatConfig;
		const savedTheme = localStorage.getItem('kk_theme') || 'light';
		const sidebarOpen = localStorage.getItem('kk_member_sidebar_open') !== 'false';

		this.container.innerHTML = `
			<div class="kk-fullscreen" role="application" aria-label="Mitglieder-Chat">
				<aside class="kk-rail" data-theme="${savedTheme}" aria-label="Navigation">
					${
						config.user.loggedIn && config.user.avatarUrl
							? `<img src="${config.user.avatarUrl}" class="kk-rail-avatar" alt="${config.user.displayName || 'User'}" />`
							: '<div class="kk-rail-btn kk-rail-avatar" title="Profil">👤</div>'
					}
					<button class="kk-rail-btn kk-rail-new" title="Neue Unterhaltung">＋</button>
					<button class="kk-rail-btn kk-rail-collapse" title="Seitenleiste ein-/ausklappen">❮</button>
					<button class="kk-rail-btn kk-rail-search" title="Suchen">🔎</button>
					<div class="kk-rail-lang" aria-label="Sprache">EN</div>
					<button class="kk-rail-btn kk-rail-settings" title="Einstellungen">⚙️</button>
				</aside>

				<section class="kk-sidebar ${sidebarOpen ? 'open' : ''}" data-theme="${savedTheme}" aria-label="Chatbereich">
					<header class="kk-sidebar__header">
						<div class="kk-header-left">
							<div class="kk-brand">
								${branding.logo_url ? `<img class="kk-brand-logo" src="${branding.logo_url}" alt="${branding.product_name}" />` : ''}
								<span class="kk-brand-title">${branding.advisor_header_title || 'Berater-Chat'}</span>
							</div>
							<div class="kk-conv-title"></div>
						</div>
						<div class="kk-header-actions">
							<button class="kk-header-close" aria-label="Schließen">✕</button>
						</div>
					</header>

					<div class="kk-body">
						<nav class="kk-history" aria-label="Conversations">
							<div class="kk-history-search">
								<input type="search" class="kk-history-search-input" placeholder="Search..." />
							</div>
							<button class="kk-new-chat-btn">+ New Chat</button>
							<div class="kk-history-list">
								<!-- Conversation items will be rendered here -->
							</div>
						</nav>

						<main class="kk-chat" role="log" aria-live="polite" aria-relevant="additions">
							<div class="kk-messages">
								<!-- Messages will be rendered here -->
							</div>
						</main>
					</div>

					<footer class="kk-composer">
						<button class="kk-mic-btn" aria-label="Spracheingabe">🎤</button>
						<textarea class="kk-input" rows="1" placeholder="Nachricht schreiben..."></textarea>
						<button class="kk-send-btn" aria-label="Senden">✈️</button>
					</footer>

					<div class="kk-legal">
						<span class="kk-legal-text">${branding.footer_text || ''}</span>
						${branding.privacy_url ? `<a class="kk-legal-link" href="${branding.privacy_url}">Datenschutzerklärung</a>` : ''}
					</div>
				</section>
			</div>
		`;

		this.sidebarOpen = sidebarOpen;
		this.attachEventListeners();
		this.loadSessions();
	}

	private renderMemberFullScreen() {
		// Create fullscreen UI attached to body for floating member widgets
		const branding = window.kraftAIChatBranding;
		const config = window.kraftAIChatConfig;
		const savedTheme = localStorage.getItem('kk_theme') || 'light';
		const sidebarOpen = localStorage.getItem('kk_member_sidebar_open') !== 'false';

		// Check if fullscreen already exists and remove it
		const existingFullscreen = document.body.querySelector('.kk-fullscreen-wrapper');
		if (existingFullscreen) {
			existingFullscreen.remove();
		}

		// Create a wrapper div to attach to body
		const wrapper = document.createElement('div');
		wrapper.className = 'kk-fullscreen-wrapper';
		wrapper.innerHTML = `
			<div class="kk-fullscreen" role="application" aria-label="Mitglieder-Chat">
				<aside class="kk-rail" data-theme="${savedTheme}" aria-label="Navigation">
					${
						config.user.loggedIn && config.user.avatarUrl
							? `<img src="${config.user.avatarUrl}" class="kk-rail-avatar" alt="${config.user.displayName || 'User'}" />`
							: '<div class="kk-rail-btn kk-rail-avatar" title="Profil">👤</div>'
					}
					<button class="kk-rail-btn kk-rail-new" title="Neue Unterhaltung">＋</button>
					<button class="kk-rail-btn kk-rail-collapse" title="Seitenleiste ein-/ausklappen">❮</button>
					<button class="kk-rail-btn kk-rail-search" title="Suchen">🔎</button>
					<div class="kk-rail-lang" aria-label="Sprache">EN</div>
					<button class="kk-rail-btn kk-rail-settings" title="Einstellungen">⚙️</button>
				</aside>

				<section class="kk-sidebar ${sidebarOpen ? 'open' : ''}" data-theme="${savedTheme}" aria-label="Chatbereich">
					<header class="kk-sidebar__header">
						<div class="kk-header-left">
							<div class="kk-brand">
								${branding.logo_url ? `<img class="kk-brand-logo" src="${branding.logo_url}" alt="${branding.product_name}" />` : ''}
								<span class="kk-brand-title">${branding.advisor_header_title || 'Berater-Chat'}</span>
							</div>
							<div class="kk-conv-title"></div>
						</div>
						<div class="kk-header-actions">
							<button class="kk-header-close" aria-label="Schließen">✕</button>
						</div>
					</header>

					<div class="kk-body">
						<nav class="kk-history" aria-label="Conversations">
							<div class="kk-history-search">
								<input type="search" class="kk-history-search-input" placeholder="Search..." />
							</div>
							<button class="kk-new-chat-btn">+ New Chat</button>
							<div class="kk-history-list">
								<!-- Conversation items will be rendered here -->
							</div>
						</nav>

						<main class="kk-chat" role="log" aria-live="polite" aria-relevant="additions">
							<div class="kk-messages">
								<!-- Messages will be rendered here -->
							</div>
						</main>
					</div>

					<footer class="kk-composer">
						<button class="kk-mic-btn" aria-label="Spracheingabe">🎤</button>
						<textarea class="kk-input" rows="1" placeholder="Nachricht schreiben..."></textarea>
						<button class="kk-send-btn" aria-label="Senden">✈️</button>
					</footer>

					<div class="kk-legal">
						<span class="kk-legal-text">${branding.footer_text || ''}</span>
						${branding.privacy_url ? `<a class="kk-legal-link" href="${branding.privacy_url}">Datenschutzerklärung</a>` : ''}
					</div>
				</section>
			</div>
		`;

		// Attach to body
		document.body.appendChild(wrapper);

		// Update container reference to the new wrapper for event listeners
		this.container = wrapper;
		this.sidebarOpen = sidebarOpen;
		this.attachEventListeners();
		this.loadSessions();
	}

	private renderLoginPrompt() {
		if (!this.container) return;

		// Get login URL from settings with fallback chain
		const settings = window.kraftAIChatConfig.settings || {};
		const accounts = settings.accounts || {};
		let loginUrl = '/wp-login.php'; // Default fallback

		// Priority: profile_url_override > profile_url > account_page_id permalink > default
		if (accounts.profile_url_override) {
			loginUrl = accounts.profile_url_override;
		} else if (accounts.profile_url) {
			loginUrl = accounts.profile_url;
		} else if (accounts.account_page_id) {
			// In a real implementation, this would resolve to the permalink
			// For now, use a reasonable fallback
			loginUrl = '/account/';
		}
		
		if (this.isFloating) {
			this.container.innerHTML = `
				<button class="kk-floating-bubble" aria-label="Login required" title="Login">
					💬
				</button>
				<div class="kk-sidebar" data-theme="light" style="display: none;">
					<div class="kk-sidebar__header">
						<div class="kk-header-info">
							<strong>Mitglieder-Chat</strong>
						</div>
						<button class="kk-close-btn" aria-label="Close">✕</button>
					</div>
					<div class="kk-login-prompt">
						<h3>Mitglieder-Chat</h3>
						<p>Bitte melde dich an, um den Mitglieder-Chat zu nutzen.</p>
						<a href="${loginUrl}" class="kk-btn">Jetzt einloggen</a>
					</div>
				</div>
			`;
		} else {
			this.container.innerHTML = `
				<div class="kk-login-prompt">
					<h3>Mitglieder-Chat</h3>
					<p>Bitte melde dich an, um den Mitglieder-Chat zu nutzen.</p>
					<a href="${loginUrl}" class="kk-btn">Jetzt einloggen</a>
				</div>
			`;
		}

		// Still attach close button listener
		const closeBtn = this.container.querySelector('.kk-close-btn');
		const bubble = this.container.querySelector('.kk-floating-bubble');
		if (closeBtn) {
			closeBtn.addEventListener('click', () => this.toggleSidebar());
		}
		if (bubble) {
			bubble.addEventListener('click', () => this.toggleSidebar());
		}
	}

	private restoreState() {
		// Restore sidebar state from localStorage (only for floating mode)
		if (this.isFloating) {
			const savedState = localStorage.getItem('kraft_ai_chat_open');
			if (savedState === 'true') {
				this.openSidebar();
			}
		}
	}

	private saveState() {
		// Save sidebar state to localStorage (only for floating mode)
		if (this.isFloating) {
			localStorage.setItem('kraft_ai_chat_open', this.sidebarOpen.toString());
		}
	}

	private attachEventListeners() {
		if (!this.container) return;

		// For floating mode
		const floatingBubble = this.container.querySelector('.kk-floating-bubble');
		if (floatingBubble) {
			floatingBubble.addEventListener('click', () => {
				// If member type, force fullscreen mode
				if (this.type === 'member') {
					this.renderMemberFullScreen();
					return;
				}
				// FAQ keeps original behavior (toggle panel)
				this.toggleSidebar();
			});
		}

		// Rail buttons (regular mode)
		const chatBtn = this.container.querySelector('.kk-chat-btn');
		const themeToggle = this.container.querySelector('.kk-theme-toggle');
		const closeBtn = this.container.querySelector('.kk-close-btn');
		const sendBtn = this.container.querySelector('.kk-send-btn');
		const input = this.container.querySelector('.kk-input') as HTMLTextAreaElement;

		chatBtn?.addEventListener('click', () => this.toggleSidebar());
		closeBtn?.addEventListener('click', () => this.toggleSidebar());
		themeToggle?.addEventListener('click', () => this.toggleTheme());
		sendBtn?.addEventListener('click', () => this.sendMessage());
		input?.addEventListener('keydown', (e) => {
			if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
				this.sendMessage();
			}
		});

		// Fullscreen mode specific events
		const railNew = this.container.querySelector('.kk-rail-new');
		const railCollapse = this.container.querySelector('.kk-rail-collapse');
		const railSettings = this.container.querySelector('.kk-rail-settings');
		const headerClose = this.container.querySelector('.kk-header-close');
		const newChatBtn = this.container.querySelector('.kk-new-chat-btn');
		const micBtn = this.container.querySelector('.kk-mic-btn');

		railNew?.addEventListener('click', () => this.createNewChat());
		railCollapse?.addEventListener('click', () => this.toggleFullscreenSidebar());
		railSettings?.addEventListener('click', () => this.toggleTheme());
		headerClose?.addEventListener('click', () => this.closeFullscreen());
		newChatBtn?.addEventListener('click', () => this.createNewChat());
		micBtn?.addEventListener('click', () => this.handleVoiceInput());

		// History item clicks (delegate)
		const historyList = this.container.querySelector('.kk-history-list');
		historyList?.addEventListener('click', (e) => {
			const target = e.target as HTMLElement;
			const item = target.closest('.kk-history-item');
			
			if (target.closest('.kk-history-rename')) {
				e.stopPropagation();
				const sessionId = item?.getAttribute('data-session-id');
				if (sessionId) this.renameSession(sessionId);
			} else if (target.closest('.kk-history-delete')) {
				e.stopPropagation();
				const sessionId = item?.getAttribute('data-session-id');
				if (sessionId) this.deleteSession(sessionId);
			} else if (item) {
				const sessionId = item.getAttribute('data-session-id');
				if (sessionId) this.loadSession(sessionId);
			}
		});

		// ESC key to close sidebar or fullscreen
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				// Check for body-attached fullscreen first
				const bodyFullscreen = document.body.querySelector('.kk-fullscreen-wrapper');
				if (bodyFullscreen) {
					this.closeFullscreen();
				} else if (this.container?.querySelector('.kk-fullscreen')) {
					this.closeFullscreen();
				} else if (this.sidebarOpen) {
					this.toggleSidebar();
				}
			}
		});
	}

	private toggleSidebar() {
		if (!this.container) return;

		const sidebar = this.container.querySelector('.kk-sidebar') as HTMLElement;
		if (!sidebar) return;

		this.sidebarOpen = !this.sidebarOpen;
		
		if (this.sidebarOpen) {
			this.openSidebar();
		} else {
			this.closeSidebar();
		}

		this.saveState();
	}

	private openSidebar() {
		if (!this.container) return;

		const sidebar = this.container.querySelector('.kk-sidebar') as HTMLElement;
		if (!sidebar) return;

		this.sidebarOpen = true;
		
		if (this.isFloating) {
			sidebar.style.display = 'flex';
			// Trigger reflow for animation
			sidebar.offsetHeight;
			sidebar.classList.add('open');
		} else {
			sidebar.classList.add('open');
		}

		if (!this.sessionId && window.kraftAIChatConfig.user.loggedIn) {
			this.createSession();
		}
	}

	private closeSidebar() {
		if (!this.container) return;

		const sidebar = this.container.querySelector('.kk-sidebar') as HTMLElement;
		if (!sidebar) return;

		this.sidebarOpen = false;
		sidebar.classList.remove('open');

		if (this.isFloating) {
			// Wait for animation before hiding
			setTimeout(() => {
				sidebar.style.display = 'none';
			}, 300);
		}
	}

	private toggleTheme() {
		if (!this.container) return;

		const rail = this.container.querySelector('.kk-rail');
		const sidebar = this.container.querySelector('.kk-sidebar');

		const currentTheme = rail?.getAttribute('data-theme') || 'light';
		const newTheme = currentTheme === 'light' ? 'dark' : 'light';

		rail?.setAttribute('data-theme', newTheme);
		sidebar?.setAttribute('data-theme', newTheme);
		
		// Save theme preference
		localStorage.setItem('kk_theme', newTheme);
	}

	private toggleFullscreenSidebar() {
		if (!this.container) return;

		const sidebar = this.container.querySelector('.kk-sidebar');
		if (!sidebar) return;

		this.sidebarOpen = !this.sidebarOpen;
		
		if (this.sidebarOpen) {
			sidebar.classList.add('open');
		} else {
			sidebar.classList.remove('open');
		}

		localStorage.setItem('kk_member_sidebar_open', this.sidebarOpen.toString());
	}

	private closeFullscreen() {
		if (!this.container) return;
		const fullscreen = this.container.querySelector('.kk-fullscreen');
		if (fullscreen) {
			// Check if this is a body-attached fullscreen
			const wrapper = document.body.querySelector('.kk-fullscreen-wrapper');
			if (wrapper) {
				wrapper.remove();
				// Restore container reference to original widget element
				this.container = document.querySelector('.kk-widget.kk-initialized') as HTMLElement;
			} else {
				// For now, just hide it. In a real app, this might navigate away
				this.container.style.display = 'none';
			}
		}
	}

	private handleVoiceInput() {
		// Placeholder for voice input functionality
		console.log('Voice input not yet implemented');
	}

	private async loadSessions() {
		if (!window.kraftAIChatConfig.apiUrl) return;

		try {
			const response = await fetch(`${window.kraftAIChatConfig.apiUrl}/member/sessions`, {
				method: 'GET',
				headers: {
					'X-WP-Nonce': window.kraftAIChatConfig.nonce,
				},
			});

			if (!response.ok) {
				console.error('Failed to load sessions');
				return;
			}

			const data = await response.json();
			this.sessions = data.success ? (data.data || []) : [];
			this.renderSessions();
		} catch (error) {
			console.error('Error loading sessions:', error);
		}
	}

	private renderSessions() {
		if (!this.container) return;

		const historyList = this.container.querySelector('.kk-history-list');
		if (!historyList) return;

		if (this.sessions.length === 0) {
			historyList.innerHTML = '<div style="text-align:center;color:var(--kk-text-muted);padding:20px;">No conversations yet</div>';
			return;
		}

		historyList.innerHTML = this.sessions.map(session => `
			<div class="kk-history-item ${session.session_id === this.currentSessionId ? 'active' : ''}" data-session-id="${session.session_id}">
				<span class="kk-history-title">${this.escapeHtml(session.title || 'Untitled')}</span>
				<div class="kk-history-actions">
					<button class="kk-history-rename" title="Umbenennen">✎</button>
					<button class="kk-history-delete" title="Löschen">🗑</button>
				</div>
			</div>
		`).join('');
	}

	private async createNewChat() {
		try {
			const response = await fetch(`${window.kraftAIChatConfig.apiUrl}/member/session`, {
				method: 'POST',
				headers: {
					'X-WP-Nonce': window.kraftAIChatConfig.nonce,
				},
			});

			if (!response.ok) {
				console.error('Failed to create session');
				return;
			}

			const data = await response.json();
			if (data.success && data.data.session_id) {
				this.sessionId = data.data.session_id;
				this.currentSessionId = data.data.session_id;
				this.messages = [];
				await this.loadSessions();
				this.renderMessages();
				
				// Focus input
				const input = this.container?.querySelector('.kk-input') as HTMLTextAreaElement;
				input?.focus();
			}
		} catch (error) {
			console.error('Error creating new chat:', error);
		}
	}

	private async loadSession(sessionId: string) {
		if (!window.kraftAIChatConfig.apiUrl) return;

		try {
			const response = await fetch(`${window.kraftAIChatConfig.apiUrl}/member/session/${sessionId}/messages`, {
				method: 'GET',
				headers: {
					'X-WP-Nonce': window.kraftAIChatConfig.nonce,
				},
			});

			if (!response.ok) {
				console.error('Failed to load session messages');
				return;
			}

			const data = await response.json();
			if (data.success) {
				this.currentSessionId = sessionId;
				this.sessionId = sessionId;
				this.messages = data.data || [];
				this.renderSessions(); // Re-render to update active state
				this.renderMessages();
			}
		} catch (error) {
			console.error('Error loading session:', error);
		}
	}

	private async renameSession(sessionId: string) {
		const currentSession = this.sessions.find(s => s.session_id === sessionId);
		const newTitle = prompt('Neuer Titel:', currentSession?.title || '');
		
		if (!newTitle || newTitle === currentSession?.title) return;

		try {
			const response = await fetch(`${window.kraftAIChatConfig.apiUrl}/member/session/${sessionId}`, {
				method: 'PATCH',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.kraftAIChatConfig.nonce,
				},
				body: JSON.stringify({ title: newTitle }),
			});

			if (response.ok) {
				await this.loadSessions();
			}
		} catch (error) {
			console.error('Error renaming session:', error);
		}
	}

	private async deleteSession(sessionId: string) {
		if (!confirm('Diese Unterhaltung wirklich löschen?')) return;

		try {
			const response = await fetch(`${window.kraftAIChatConfig.apiUrl}/member/session/${sessionId}`, {
				method: 'DELETE',
				headers: {
					'X-WP-Nonce': window.kraftAIChatConfig.nonce,
				},
			});

			if (response.ok) {
				if (this.currentSessionId === sessionId) {
					this.currentSessionId = null;
					this.sessionId = null;
					this.messages = [];
					this.renderMessages();
				}
				await this.loadSessions();
			}
		} catch (error) {
			console.error('Error deleting session:', error);
		}
	}

	private escapeHtml(text: string): string {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	private async createSession() {
		// Safety check: ensure apiUrl and nonce are available
		if (!window.kraftAIChatConfig.apiUrl || !window.kraftAIChatConfig.nonce) {
			console.error('Failed to create session: Missing API configuration');
			return;
		}

		try {
			const response = await fetch(`${window.kraftAIChatConfig.apiUrl}/member/session`, {
				method: 'POST',
				headers: {
					'X-WP-Nonce': window.kraftAIChatConfig.nonce,
				},
			});
			const data = await response.json();
			this.sessionId = data.session_id;
		} catch (error) {
			console.error('Failed to create session:', error);
		}
	}

	private async sendMessage() {
		if (!this.container) return;

		const input = this.container.querySelector('.kk-input') as HTMLTextAreaElement;
		const message = input.value.trim();

		if (!message) return;

		// Safety check: ensure API configuration is available
		if (!window.kraftAIChatConfig.apiUrl || !window.kraftAIChatConfig.nonce) {
			console.error('Failed to send message: Missing API configuration');
			this.addMessage({
				role: 'assistant',
				content: 'Configuration error: Unable to connect to chat service.',
			});
			return;
		}

		// Clear input
		input.value = '';

		// Add user message
		this.addMessage({ role: 'user', content: message });

		// Show typing indicator
		this.isTyping = true;
		this.renderTypingIndicator();

		try {
			const type = this.container.getAttribute('data-type') || 'faq';
			let endpoint = '';
			let body: any = {};

			if (type === 'member' && this.sessionId) {
				endpoint = '/member/message';
				body = { session_id: this.sessionId, message };
			} else {
				endpoint = '/faq/query';
				body = { query: message };
			}

			const response = await fetch(`${window.kraftAIChatConfig.apiUrl}${endpoint}`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.kraftAIChatConfig.nonce,
				},
				body: JSON.stringify(body),
			});

			const data = await response.json();

			this.isTyping = false;
			this.removeTypingIndicator();

			if (data.answer || data.response) {
				this.addMessage({
					role: 'assistant',
					content: data.answer || data.response,
					sources: data.sources,
				});
			}
		} catch (error) {
			console.error('Failed to send message:', error);
			this.isTyping = false;
			this.removeTypingIndicator();
			this.addMessage({
				role: 'assistant',
				content: 'Sorry, there was an error processing your request.',
			});
		}
	}

	private addMessage(message: Message) {
		this.messages.push(message);
		this.renderMessages();
	}

	private renderMessages() {
		if (!this.container) return;

		const messagesContainer = this.container.querySelector('.kk-messages');
		if (!messagesContainer) return;

		const isFullscreen = !!this.container.querySelector('.kk-fullscreen');

		messagesContainer.innerHTML = this.messages
			.map((msg, index) => {
				const sourcesHtml =
					msg.sources && msg.sources.length > 0
						? `<div class="kk-sources">
							${msg.sources
								.map(
									(s) =>
										`<span class="kk-badge" title="Score: ${s.score}">${s.title}</span>`
								)
								.join('')}
						</div>`
						: '';

				// Add message actions for bot messages in fullscreen mode
				const actionsHtml = isFullscreen && msg.role === 'assistant' && index === this.messages.length - 1
					? `<div class="kk-message-actions">
						<button class="kk-msg-copy" title="Kopieren" data-msg-index="${index}">📋</button>
						<button class="kk-msg-retry" title="Neu generieren" data-msg-index="${index}">🔄</button>
						<button class="kk-msg-like" title="Gefällt mir" data-msg-index="${index}">👍</button>
						<button class="kk-msg-dislike" title="Gefällt mir nicht" data-msg-index="${index}">👎</button>
						<button class="kk-msg-delete" title="Löschen" data-msg-index="${index}">🗑</button>
					</div>`
					: '';

				if (isFullscreen) {
					return `
						<div class="kk-message ${msg.role}">
							<div class="kk-message-content">
								${msg.content}
								${sourcesHtml}
							</div>
							${actionsHtml}
						</div>
					`;
				} else {
					return `
						<div class="kk-message ${msg.role}">
							<div class="kk-bubble ${msg.role}">
								${msg.content}
								${sourcesHtml}
							</div>
						</div>
					`;
				}
			})
			.join('');

		// Attach action listeners
		if (isFullscreen) {
			messagesContainer.querySelectorAll('.kk-msg-copy').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const index = parseInt((e.target as HTMLElement).dataset.msgIndex || '0');
					this.copyMessage(index);
				});
			});

			messagesContainer.querySelectorAll('.kk-msg-retry').forEach(btn => {
				btn.addEventListener('click', () => this.retryLastMessage());
			});

			messagesContainer.querySelectorAll('.kk-msg-like, .kk-msg-dislike').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const index = parseInt((e.target as HTMLElement).dataset.msgIndex || '0');
					const isLike = (e.target as HTMLElement).classList.contains('kk-msg-like');
					this.rateMessage(index, isLike);
				});
			});

			messagesContainer.querySelectorAll('.kk-msg-delete').forEach(btn => {
				btn.addEventListener('click', (e) => {
					const index = parseInt((e.target as HTMLElement).dataset.msgIndex || '0');
					this.deleteMessage(index);
				});
			});
		}

		// Scroll to bottom
		const chatContainer = this.container.querySelector('.kk-chat');
		if (chatContainer) {
			chatContainer.scrollTop = chatContainer.scrollHeight;
		}
	}

	private copyMessage(index: number) {
		const msg = this.messages[index];
		if (msg) {
			navigator.clipboard.writeText(msg.content).then(() => {
				console.log('Message copied to clipboard');
			}).catch(err => {
				console.error('Failed to copy:', err);
			});
		}
	}

	private retryLastMessage() {
		// Find the last user message and resend it
		for (let i = this.messages.length - 1; i >= 0; i--) {
			if (this.messages[i].role === 'user') {
				const input = this.container?.querySelector('.kk-input') as HTMLTextAreaElement;
				if (input) {
					input.value = this.messages[i].content;
					this.sendMessage();
				}
				break;
			}
		}
	}

	private rateMessage(index: number, isLike: boolean) {
		// Placeholder for feedback functionality
		console.log(`Message ${index} rated:`, isLike ? 'like' : 'dislike');
		// In a real implementation, this would send feedback to the server
	}

	private deleteMessage(index: number) {
		if (confirm('Diese Nachricht wirklich löschen?')) {
			this.messages.splice(index, 1);
			this.renderMessages();
		}
	}

	private renderTypingIndicator() {
		if (!this.container) return;

		const messagesContainer = this.container.querySelector('.kk-messages');
		if (!messagesContainer) return;

		const typingDiv = document.createElement('div');
		typingDiv.className = 'kk-typing-indicator';
		typingDiv.innerHTML = `
			<div class="kk-bubble bot">
				<span class="kk-typing-dots">
					<span></span><span></span><span></span>
				</span>
			</div>
		`;
		messagesContainer.appendChild(typingDiv);

		const chatContainer = this.container.querySelector('.kk-chat');
		if (chatContainer) {
			chatContainer.scrollTop = chatContainer.scrollHeight;
		}
	}

	private removeTypingIndicator() {
		if (!this.container) return;

		const indicator = this.container.querySelector('.kk-typing-indicator');
		indicator?.remove();
	}
}

/**
 * Initialize the chat widget.
 */
function initKIKraftWidget() {
	new KIKraftWidget();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initKIKraftWidget);
} else {
	initKIKraftWidget();
}

export { initKIKraftWidget, KIKraftWidget };
