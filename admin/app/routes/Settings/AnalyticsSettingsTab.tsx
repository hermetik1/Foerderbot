import React, { useState, useEffect } from 'react';
import { settingsAPI, APIError } from '../../lib/api';

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
};

interface AnalyticsSettingsData {
	enabled: boolean;
	retention_days: number;
	anonymize_ip: boolean;
	track_feedback: boolean;
	collect_local_analytics: boolean;
}

/**
 * Analytics settings tab component.
 */
const AnalyticsSettingsTab: React.FC = () => {
	const [settings, setSettings] = useState<AnalyticsSettingsData>({
		enabled: true,
		retention_days: 90,
		anonymize_ip: true,
		track_feedback: true,
		collect_local_analytics: false,
	});
	const [loading, setLoading] = useState(true);
	const [saving, setSaving] = useState(false);
	const [message, setMessage] = useState('');
	const [errors, setErrors] = useState<Record<string, string>>({});

	useEffect(() => {
		loadSettings();
	}, []);

	const loadSettings = async () => {
		try {
			const data = await settingsAPI.get('analytics');
			setSettings(data);
		} catch (error) {
			console.error('Failed to load settings:', error);
			setMessage('Failed to load settings.');
		} finally {
			setLoading(false);
		}
	};

	const handleSave = async (e: React.FormEvent) => {
		e.preventDefault();
		setSaving(true);
		setMessage('');
		setErrors({});

		try {
			const response = await settingsAPI.update('analytics', settings);
			if (response.success) {
				setMessage('Analytics settings saved successfully!');
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

	if (loading) {
		return (
			<div className="settings-tab analytics-settings-tab">
				<p>Loading settings...</p>
			</div>
		);
	}

	return (
		<div className="settings-tab analytics-settings-tab">
			<h2>Analytics Settings</h2>
			<p>Configure analytics and tracking behavior for the chatbot.</p>

			{message && (
				<div 
					className={`notice ${message.includes('successfully') ? 'notice-success' : 'notice-error'}`}
					role="status"
					aria-live="polite"
				>
					<p>{message}</p>
				</div>
			)}

			<form onSubmit={handleSave}>
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
							{' '}Enable Analytics
						</label>
						<p className="description">Track chatbot usage and queries</p>
					</div>

					<div className="form-group">
						<label htmlFor="collect_local_analytics">
							<input
								type="checkbox"
								id="collect_local_analytics"
								checked={settings.collect_local_analytics}
								onChange={(e) => setSettings({ ...settings, collect_local_analytics: e.target.checked })}
							/>
							{' '}Collect Local Analytics
						</label>
						<p className="description">Store analytics data locally in the database</p>
					</div>

					{settings.enabled && (
						<>
							<div className="form-group">
								<label htmlFor="retention_days">Retention Days</label>
								<input
									type="number"
									id="retention_days"
									value={settings.retention_days}
									onChange={(e) => setSettings({ ...settings, retention_days: Number(e.target.value) })}
									min="7"
									max="365"
								/>
								<p className="description">How long to keep analytics data (7-365 days)</p>
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
								<p className="description">Remove the last octet of IP addresses for privacy</p>
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
								<p className="description">Record user feedback (thumbs up/down) on responses</p>
							</div>
						</>
					)}
				</div>

				<div className="form-actions">
					<button type="submit" className="button button-primary" disabled={saving || loading}>
						{saving ? 'Saving...' : 'Save Analytics Settings'}
					</button>
				</div>
			</form>
		</div>
	);
};

export default AnalyticsSettingsTab;
