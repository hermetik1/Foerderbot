import React, { useState } from 'react';
import GeneralTab from './Settings/GeneralTab';
import PrivacyTab from './Settings/PrivacyTab';
import WhiteLabelTab from './Settings/WhiteLabelTab';
import KnowledgeTab from './Settings/KnowledgeTab';
import AnalyticsTab from './Settings/AnalyticsTab';
import IntegrationsTab from './Settings/IntegrationsTab';
import AccountsTab from './Settings/AccountsTab';
import AnalyticsSettingsTab from './Settings/AnalyticsSettingsTab';
import DeveloperToolsTab from './Settings/DeveloperToolsTab';

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
	branding: any;
	page?: string;
};

/**
 * Settings page component - separate screen for comprehensive settings.
 */
const SettingsPage: React.FC = () => {
	const [activeSection, setActiveSection] = useState<string>('general');
	const [searchTerm, setSearchTerm] = useState<string>('');

	const sections = [
		{ id: 'general', label: 'General Settings', icon: '⚙️' },
		{ id: 'branding', label: 'Branding', icon: '🎨' },
		{ id: 'integrations', label: 'Integrations', icon: '🔌' },
		{ id: 'accounts', label: 'Accounts', icon: '👤' },
		{ id: 'privacy', label: 'Privacy', icon: '🔒' },
		{ id: 'knowledge', label: 'Knowledge Defaults', icon: '📚' },
		{ id: 'analytics-settings', label: 'Analytics Settings', icon: '📊' },
		{ id: 'analytics', label: 'Analytics Dashboard', icon: '📈' },
		{ id: 'developer', label: 'Developer / Tools', icon: '🛠️' },
	];

	const renderContent = () => {
		switch (activeSection) {
			case 'general':
				return <GeneralTab />;
			case 'branding':
				return <WhiteLabelTab />;
			case 'integrations':
				return <IntegrationsTab />;
			case 'accounts':
				return <AccountsTab />;
			case 'privacy':
				return <PrivacyTab />;
			case 'knowledge':
				return <KnowledgeTab />;
			case 'analytics-settings':
				return <AnalyticsSettingsTab />;
			case 'analytics':
				return <AnalyticsTab />;
			case 'developer':
				return <DeveloperToolsTab />;
			default:
				return <div>Unknown section</div>;
		}
	};

	const filteredSections = sections.filter(section =>
		section.label.toLowerCase().includes(searchTerm.toLowerCase())
	);

	return (
		<div className="ki-kraft-settings-page">
			<div className="settings-header">
				<h1>Kraft AI Chat Settings</h1>
				<p>Configure all aspects of your chatbot plugin.</p>
			</div>

			<div className="settings-search">
				<input
					type="text"
					placeholder="Search settings..."
					value={searchTerm}
					onChange={(e) => setSearchTerm(e.target.value)}
					className="settings-search-input"
					aria-label="Search settings"
				/>
			</div>

			<div className="settings-layout">
				<nav className="settings-sidebar" role="navigation" aria-label="Settings sections">
					{filteredSections.map((section) => (
						<button
							key={section.id}
							className={activeSection === section.id ? 'active' : ''}
							onClick={() => setActiveSection(section.id)}
							aria-current={activeSection === section.id ? 'page' : undefined}
						>
							<span className="icon">{section.icon}</span>
							<span className="label">{section.label}</span>
						</button>
					))}
				</nav>

				<main className="settings-content" role="main">
					{renderContent()}
				</main>
			</div>

			<footer className="settings-footer">
				<div className="disclaimer">
					<h3>⚠️ Disclaimer</h3>
					<p>
						This plugin uses AI technology to generate responses. While we strive for accuracy, 
						AI-generated content should be reviewed and verified. The plugin does not guarantee 
						the correctness of responses. Use at your own discretion.
					</p>
					<p>
						<strong>Data Privacy:</strong> No telemetry data is collected without explicit opt-in. 
						Please review the Privacy settings to ensure compliance with applicable data protection laws.
					</p>
					<p>
						<a href={kraftAIChatAdmin.branding?.privacy_url || '#'} target="_blank" rel="noopener noreferrer">
							Privacy Policy
						</a>
						{' | '}
						<a href={kraftAIChatAdmin.branding?.imprint_url || '#'} target="_blank" rel="noopener noreferrer">
							Imprint
						</a>
					</p>
				</div>
			</footer>
		</div>
	);
};

export default SettingsPage;
