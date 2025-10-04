/**
 * Main entry point for the KI Kraft chat widget.
 */

import './styles/base.css';
import './styles/layout.css';
import './styles/components.css';
import './styles/themes.css';

/**
 * Initialize the chat widget.
 */
function initKIKraftWidget() {
	const config = (window as any).KIKraftConfig || {};
	
	console.log('KI Kraft Widget initialized', config);
	
	// TODO: Render Rail component
	// TODO: Render Sidebar component
	// TODO: Initialize theme toggle
	// TODO: Initialize language toggle
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initKIKraftWidget);
} else {
	initKIKraftWidget();
}

export { initKIKraftWidget };
