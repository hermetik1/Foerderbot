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

	private renderLoginPrompt() {
		if (!this.container) return;

		// Get account page URL from config or use default
		const accountUrl = '/account/'; // This should come from settings
		
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
						<p>Bitte melde dich an, um den Mitglieder-Chat zu nutzen.</p>
						<a href="${accountUrl}" class="kk-btn">Jetzt einloggen</a>
					</div>
				</div>
			`;
		} else {
			this.container.innerHTML = `
				<div class="kk-login-prompt">
					<p>Bitte melde dich an, um den Mitglieder-Chat zu nutzen.</p>
					<a href="${accountUrl}" class="kk-btn">Jetzt einloggen</a>
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
			floatingBubble.addEventListener('click', () => this.toggleSidebar());
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

		// ESC key to close sidebar
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && this.sidebarOpen) {
				this.toggleSidebar();
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
	}

	private async createSession() {
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

		messagesContainer.innerHTML = this.messages
			.map((msg) => {
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

				return `
					<div class="kk-message ${msg.role}">
						<div class="kk-bubble ${msg.role}">
							${msg.content}
							${sourcesHtml}
						</div>
					</div>
				`;
			})
			.join('');

		// Scroll to bottom
		const chatContainer = this.container.querySelector('.kk-chat');
		if (chatContainer) {
			chatContainer.scrollTop = chatContainer.scrollHeight;
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
