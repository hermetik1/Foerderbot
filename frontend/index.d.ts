/**
 * Main entry point for the KI Kraft chat widget.
 */
import './styles/base.css';
import './styles/layout.css';
import './styles/components.css';
import './styles/themes.css';
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
        KIKraftConfig: KraftAIChatConfig;
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