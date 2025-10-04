import React from 'react';
import { createRoot } from 'react-dom/client';
import Dashboard from './app/routes/Dashboard';
import './app/styles/index.css';

/**
 * Main entry point for the KI Kraft admin interface.
 */
const container = document.getElementById('ki-kraft-admin-root');
if (container) {
	const root = createRoot(container);
	root.render(
		<React.StrictMode>
			<Dashboard />
		</React.StrictMode>
	);
}
