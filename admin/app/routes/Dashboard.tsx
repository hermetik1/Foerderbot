import React, { useState, useEffect } from 'react';
import GeneralTab from './Settings/GeneralTab';
import PrivacyTab from './Settings/PrivacyTab';
import WhiteLabelTab from './Settings/WhiteLabelTab';
import KnowledgeTab from './Settings/KnowledgeTab';
import AnalyticsTab from './Settings/AnalyticsTab';

declare const kiKraftAdmin: {
	apiUrl: string;
	nonce: string;
	branding: any;
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
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		fetchAnalytics();
	}, []);

	const fetchAnalytics = async () => {
		try {
			const response = await fetch(`${kiKraftAdmin.apiUrl}/analytics/summary?days=7`, {
				headers: {
					'X-WP-Nonce': kiKraftAdmin.nonce,
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
						{loading ? (
							<p>Loading analytics...</p>
						) : analytics ? (
							<div className="dashboard-grid">
								<div className="dashboard-card">
									<h2>Query Statistics (Last 7 Days)</h2>
									<div className="stats-grid">
										<div className="stat">
											<span className="stat-label">Total Queries</span>
											<span className="stat-value">{analytics.total}</span>
										</div>
										<div className="stat">
											<span className="stat-label">Answered</span>
											<span className="stat-value success">{analytics.answered}</span>
										</div>
										<div className="stat">
											<span className="stat-label">Unanswered</span>
											<span className="stat-value warning">{analytics.unanswered}</span>
										</div>
										<div className="stat">
											<span className="stat-label">Success Rate</span>
											<span className="stat-value">
												{analytics.total > 0
													? Math.round((analytics.answered / analytics.total) * 100)
													: 0}
												%
											</span>
										</div>
									</div>
								</div>
								<div className="dashboard-card">
									<h2>Quick Actions</h2>
									<div className="action-buttons">
										<button onClick={() => setActiveTab('knowledge')} className="action-btn">
											📚 Manage Knowledge Base
										</button>
										<button onClick={() => setActiveTab('analytics')} className="action-btn">
											📊 View Detailed Analytics
										</button>
										<button onClick={() => setActiveTab('settings')} className="action-btn">
											⚙️ Configure Settings
										</button>
										<button onClick={() => setActiveTab('branding')} className="action-btn">
											🎨 Customize Branding
										</button>
									</div>
								</div>
							</div>
						) : (
							<p>Failed to load analytics</p>
						)}
					</div>
				);
			case 'settings':
				return <GeneralTab />;
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
					className={activeTab === 'settings' ? 'active' : ''}
					onClick={() => setActiveTab('settings')}
				>
					Settings
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
