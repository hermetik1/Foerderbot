import React from 'react';
import { createRoot } from 'react-dom/client';
import Dashboard from './app/routes/Dashboard';
import SettingsPage from './app/routes/SettingsPage';
import './app/styles/index.css';

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
	branding: any;
	page?: string;
};

/**
 * Main entry point for the KI Kraft admin interface.
 */
const container = document.getElementById('ki-kraft-admin-root');
if (container) {
	const root = createRoot(container);
	const isSettingsPage = kraftAIChatAdmin.page === 'kraft-ai-chat-settings';
	
	root.render(
		<React.StrictMode>
			{isSettingsPage ? <SettingsPage /> : <Dashboard />}
		</React.StrictMode>
	);
}
