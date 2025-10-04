import React, { useState, useEffect } from 'react';
import PrivacyTab from './Settings/PrivacyTab';
import WhiteLabelTab from './Settings/WhiteLabelTab';
import KnowledgeTab from './Settings/KnowledgeTab';
import AnalyticsTab from './Settings/AnalyticsTab';

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
	branding: any;
	page?: string;
};

interface AnalyticsSummary {
	total: number;
	answered: number;
	unanswered: number;
	trends: Array<{ date: string; count: number }>;
}

/**
 * Main dashboard component for KI Kraft admin.
 */
const Dashboard: React.FC = () => {
	const [activeTab, setActiveTab] = useState<string>('overview');
	const [analytics, setAnalytics] = useState<AnalyticsSummary | null>(null);
	const [loading, setLoading] = useState(false);

	// Don't auto-load analytics on mount
	useEffect(() => {
		// Only fetch analytics when Analytics tab is active
		if (activeTab === 'analytics') {
			fetchAnalytics();
		}
	}, [activeTab]);

	const fetchAnalytics = async () => {
		if (analytics !== null) {
			return; // Already loaded
		}
		setLoading(true);
		try {
			const response = await fetch(`${kraftAIChatAdmin.apiUrl}/analytics/summary?days=7`, {
				headers: {
					'X-WP-Nonce': kraftAIChatAdmin.nonce,
				},
			});
			const data = await response.json();
			setAnalytics(data);
		} catch (error) {
			console.error('Failed to fetch analytics:', error);
		} finally {
			setLoading(false);
		}
	};

	const renderContent = () => {
		switch (activeTab) {
			case 'overview':
				return (
					<div className="dashboard-overview">
						<h1>KI Kraft Dashboard</h1>
						<div className="dashboard-grid">
							<div className="dashboard-card">
								<h2>Welcome to Kraft AI Chat</h2>
								<p>Use the tabs above to manage your chatbot, knowledge base, analytics, privacy settings, and branding.</p>
								<div className="action-buttons" style={{ marginTop: '20px' }}>
									<button onClick={() => setActiveTab('knowledge')} className="action-btn">
										📚 Manage Knowledge Base
									</button>
									<button onClick={() => setActiveTab('analytics')} className="action-btn">
										📊 View Analytics
									</button>
									<button onClick={() => setActiveTab('privacy')} className="action-btn">
										🔒 Privacy Settings
									</button>
									<button onClick={() => setActiveTab('branding')} className="action-btn">
										🎨 Customize Branding
									</button>
								</div>
							</div>
							<div className="dashboard-card">
								<h2>Getting Started</h2>
								<ol>
									<li>Add knowledge entries in the Knowledge Base tab</li>
									<li>Configure privacy and data retention settings</li>
									<li>Customize branding to match your site</li>
									<li>Monitor usage in the Analytics tab</li>
								</ol>
							</div>
						</div>
					</div>
				);
			case 'privacy':
				return <PrivacyTab />;
			case 'branding':
				return <WhiteLabelTab />;
			case 'knowledge':
				return <KnowledgeTab />;
			case 'analytics':
				return <AnalyticsTab />;
			default:
				return <div>Unknown tab</div>;
		}
	};

	return (
		<div className="ki-kraft-dashboard">
			<nav className="dashboard-nav">
				<button
					className={activeTab === 'overview' ? 'active' : ''}
					onClick={() => setActiveTab('overview')}
				>
					Overview
				</button>
				<button
					className={activeTab === 'knowledge' ? 'active' : ''}
					onClick={() => setActiveTab('knowledge')}
				>
					Knowledge Base
				</button>
				<button
					className={activeTab === 'analytics' ? 'active' : ''}
					onClick={() => setActiveTab('analytics')}
				>
					Analytics
				</button>
				<button
					className={activeTab === 'privacy' ? 'active' : ''}
					onClick={() => setActiveTab('privacy')}
				>
					Privacy
				</button>
				<button
					className={activeTab === 'branding' ? 'active' : ''}
					onClick={() => setActiveTab('branding')}
				>
					Branding
				</button>
			</nav>
			<div className="dashboard-content">{renderContent()}</div>
		</div>
	);
};

export default Dashboard;
