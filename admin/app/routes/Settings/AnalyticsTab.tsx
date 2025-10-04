import React, { useState, useEffect } from 'react';

declare const kiKraftAdmin: {
	apiUrl: string;
	nonce: string;
};

interface AnalyticsData {
	total: number;
	answered: number;
	unanswered: number;
	top_queries: Array<{ query: string; count: number }>;
	unanswered_queries: Array<{ query: string; count: number }>;
	trends: Array<{ date: string; count: number }>;
	period: number;
}

/**
 * Analytics tab component.
 */
const AnalyticsTab: React.FC = () => {
	const [analytics, setAnalytics] = useState<AnalyticsData | null>(null);
	const [loading, setLoading] = useState(true);
	const [period, setPeriod] = useState(7);

	useEffect(() => {
		fetchAnalytics();
	}, [period]);

	const fetchAnalytics = async () => {
		setLoading(true);
		try {
			const response = await fetch(`${kiKraftAdmin.apiUrl}/analytics/summary?days=${period}`, {
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

	if (loading) {
		return <div className="settings-tab analytics-tab"><p>Loading analytics...</p></div>;
	}

	if (!analytics) {
		return <div className="settings-tab analytics-tab"><p>Failed to load analytics</p></div>;
	}

	const successRate = analytics.total > 0 
		? Math.round((analytics.answered / analytics.total) * 100) 
		: 0;

	return (
		<div className="settings-tab analytics-tab">
			<h2>Analytics Dashboard</h2>
			
			<div className="period-selector">
				<label>Period: </label>
				<select value={period} onChange={(e) => setPeriod(Number(e.target.value))}>
					<option value={7}>Last 7 Days</option>
					<option value={30}>Last 30 Days</option>
					<option value={90}>Last 90 Days</option>
				</select>
			</div>

			<div className="analytics-grid">
				<div className="analytics-card">
					<h3>Overview</h3>
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
							<span className="stat-value">{successRate}%</span>
						</div>
					</div>
				</div>

				<div className="analytics-card">
					<h3>Top Queries</h3>
					<table className="analytics-table">
						<thead>
							<tr>
								<th>Query</th>
								<th>Count</th>
							</tr>
						</thead>
						<tbody>
							{analytics.top_queries.slice(0, 10).map((item, idx) => (
								<tr key={idx}>
									<td>{item.query}</td>
									<td><strong>{item.count}</strong></td>
								</tr>
							))}
						</tbody>
					</table>
				</div>

				<div className="analytics-card">
					<h3>Unanswered Queries</h3>
					<table className="analytics-table">
						<thead>
							<tr>
								<th>Query</th>
								<th>Count</th>
							</tr>
						</thead>
						<tbody>
							{analytics.unanswered_queries.slice(0, 10).map((item, idx) => (
								<tr key={idx}>
									<td>{item.query}</td>
									<td><strong>{item.count}</strong></td>
								</tr>
							))}
						</tbody>
					</table>
					{analytics.unanswered_queries.length === 0 && (
						<p className="no-data">No unanswered queries in this period</p>
					)}
				</div>

				<div className="analytics-card full-width">
					<h3>Query Trends</h3>
					<div className="trend-chart">
						{analytics.trends.map((trend, idx) => {
							const maxCount = Math.max(...analytics.trends.map(t => t.count));
							const height = maxCount > 0 ? (trend.count / maxCount) * 100 : 0;
							return (
								<div key={idx} className="trend-bar-container">
									<div 
										className="trend-bar" 
										style={{ height: `${height}%` }}
										title={`${trend.date}: ${trend.count} queries`}
									/>
									<span className="trend-label">{trend.date.substring(5)}</span>
								</div>
							);
						})}
					</div>
				</div>
			</div>
		</div>
	);
};

export default AnalyticsTab;
