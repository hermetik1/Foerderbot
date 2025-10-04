/**
 * Main entry point for the KI Kraft chat widget.
 */
import './styles/base.css';
import './styles/layout.css';
import './styles/components.css';
import './styles/themes.css';
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
declare class KIKraftWidget {
    private container;
    private railOpen;
    private sidebarOpen;
    private messages;
    private sessionId;
    private isTyping;
    private isFloating;
    constructor();
    private init;
    private render;
    private renderFloating;
    private renderRegular;
    private renderLoginPrompt;
    private restoreState;
    private saveState;
    private attachEventListeners;
    private toggleSidebar;
    private openSidebar;
    private closeSidebar;
    private toggleTheme;
    private createSession;
    private sendMessage;
    private addMessage;
    private renderMessages;
    private renderTypingIndicator;
    private removeTypingIndicator;
}
/**
 * Initialize the chat widget.
 */
declare function initKIKraftWidget(): void;
export { initKIKraftWidget, KIKraftWidget };
//# sourceMappingURL=index.d.ts.map