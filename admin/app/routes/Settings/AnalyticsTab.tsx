import React, { useState, useEffect } from 'react';
import { settingsAPI, analyticsAPI, APIError } from '../../lib/api';

declare const kraftAIChatAdmin: {
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

interface AnalyticsSettings {
	enabled: boolean;
	retention_days: number;
	anonymize_ip: boolean;
	track_feedback: boolean;
}

/**
 * Analytics tab component.
 */
const AnalyticsTab: React.FC = () => {
	const [analytics, setAnalytics] = useState<AnalyticsData | null>(null);
	const [settings, setSettings] = useState<AnalyticsSettings>({
		enabled: true,
		retention_days: 90,
		anonymize_ip: true,
		track_feedback: true,
	});
	const [loading, setLoading] = useState(true);
	const [loadingSettings, setLoadingSettings] = useState(true);
	const [saving, setSaving] = useState(false);
	const [showSettings, setShowSettings] = useState(false);
	const [period, setPeriod] = useState(7);
	const [message, setMessage] = useState('');
	const [errors, setErrors] = useState<Record<string, string>>({});

	useEffect(() => {
		fetchAnalytics();
		loadSettings();
	}, [period]);

	const loadSettings = async () => {
		try {
			const data = await settingsAPI.get('analytics');
			setSettings(data);
		} catch (error) {
			console.error('Failed to load analytics settings:', error);
		} finally {
			setLoadingSettings(false);
		}
	};

	const handleSaveSettings = async (e: React.FormEvent) => {
		e.preventDefault();
		setSaving(true);
		setMessage('');
		setErrors({});

		try {
			const response = await settingsAPI.update('analytics', settings);
			if (response.success) {
				setMessage('Analytics settings saved successfully!');
				setShowSettings(false);
			}
		} catch (error: any) {
			const apiError = error as APIError;
			if (apiError.data?.errors) {
				setErrors(apiError.data.errors);
				setMessage('Validation failed. Please check the fields below.');
			} else {
				setMessage(apiError.message || 'Error saving settings.');
			}
		} finally {
			setSaving(false);
		}
	};

	const fetchAnalytics = async () => {
		setLoading(true);
		try {
			const data = await analyticsAPI.getSummary(period);
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
			
			{message && (
				<div 
					className={`notice ${message.includes('successfully') ? 'notice-success' : 'notice-error'}`}
					role="status"
					aria-live="polite"
				>
					<p>{message}</p>
				</div>
			)}
			
			<div style={{ marginBottom: '20px', display: 'flex', gap: '10px', alignItems: 'center' }}>
				<label>Period: </label>
				<select value={period} onChange={(e) => setPeriod(Number(e.target.value))}>
					<option value={7}>Last 7 Days</option>
					<option value={30}>Last 30 Days</option>
					<option value={90}>Last 90 Days</option>
				</select>
				<button onClick={() => setShowSettings(!showSettings)} className="button">
					{showSettings ? 'Hide Settings' : '⚙️ Analytics Settings'}
				</button>
			</div>

			{showSettings && !loadingSettings && (
				<form onSubmit={handleSaveSettings} style={{ marginBottom: '20px', padding: '20px', background: '#f5f5f5', borderRadius: '4px' }}>
					<div className="form-section">
						<h3>Analytics Configuration</h3>
						
						<div className="form-group">
							<label htmlFor="enabled">
								<input
									type="checkbox"
									id="enabled"
									checked={settings.enabled}
									onChange={(e) => setSettings({ ...settings, enabled: e.target.checked })}
								/>
								{' '}Enable Analytics Collection
							</label>
							<p className="description">Track chatbot usage and queries</p>
						</div>

						{settings.enabled && (
							<>
								<div className="form-group">
									<label htmlFor="retention_days">Analytics Retention (days)</label>
									<input
										type="number"
										id="retention_days"
										value={settings.retention_days}
										onChange={(e) => setSettings({ ...settings, retention_days: Number(e.target.value) })}
										min="7"
										max="365"
										aria-describedby="retention_days_desc"
									/>
									<p className="description" id="retention_days_desc">
										How long to keep analytics data (default: 90 days)
									</p>
									{errors.retention_days && <p className="error-text">{errors.retention_days}</p>}
								</div>

								<div className="form-group">
									<label htmlFor="anonymize_ip">
										<input
											type="checkbox"
											id="anonymize_ip"
											checked={settings.anonymize_ip}
											onChange={(e) => setSettings({ ...settings, anonymize_ip: e.target.checked })}
										/>
										{' '}Anonymize IP Addresses
									</label>
									<p className="description">Mask IP addresses for GDPR compliance</p>
								</div>

								<div className="form-group">
									<label htmlFor="track_feedback">
										<input
											type="checkbox"
											id="track_feedback"
											checked={settings.track_feedback}
											onChange={(e) => setSettings({ ...settings, track_feedback: e.target.checked })}
										/>
										{' '}Track User Feedback
									</label>
									<p className="description">Record thumbs up/down feedback on answers</p>
								</div>
							</>
						)}

						<div className="form-actions">
							<button type="submit" className="button button-primary" disabled={saving}>
								{saving ? 'Saving...' : 'Save Settings'}
							</button>
						</div>
					</div>
				</form>
			)}

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
