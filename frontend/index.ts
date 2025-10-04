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

declare global {
	interface Window {
		KIKraftConfig: {
			apiUrl: string;
			nonce: string;
			user: {
				loggedIn: boolean;
				userId?: number;
				displayName?: string;
				avatarUrl?: string;
				roles?: string[];
			};
			branding: any;
			i18n: {
				send: string;
				typing: string;
				placeholder: string;
				close: string;
			};
		};
	}
}

class KIKraftWidget {
	private container: HTMLElement | null = null;
	private railOpen = false;
	private sidebarOpen = false;
	private messages: Message[] = [];
	private sessionId: string | null = null;
	private isTyping = false;

	constructor() {
		this.init();
	}

	private init() {
		// Find widget containers
		const containers = document.querySelectorAll('.kk-widget');
		if (containers.length === 0) return;

		containers.forEach((container) => {
			this.container = container as HTMLElement;
			this.render();
		});
	}

	private render() {
		if (!this.container) return;

		const type = this.container.getAttribute('data-type') || 'faq';
		const config = window.KIKraftConfig;

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
						<strong>${config.branding.product_name || 'KI Kraft'}</strong>
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

		this.attachEventListeners();
	}

	private attachEventListeners() {
		if (!this.container) return;

		// Rail buttons
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
	}

	private toggleSidebar() {
		if (!this.container) return;

		const sidebar = this.container.querySelector('.kk-sidebar');
		if (!sidebar) return;

		this.sidebarOpen = !this.sidebarOpen;
		if (this.sidebarOpen) {
			sidebar.classList.add('open');
			if (!this.sessionId && window.KIKraftConfig.user.loggedIn) {
				this.createSession();
			}
		} else {
			sidebar.classList.remove('open');
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
			const response = await fetch(`${window.KIKraftConfig.apiUrl}/member/session`, {
				method: 'POST',
				headers: {
					'X-WP-Nonce': window.KIKraftConfig.nonce,
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

			const response = await fetch(`${window.KIKraftConfig.apiUrl}${endpoint}`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.KIKraftConfig.nonce,
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
