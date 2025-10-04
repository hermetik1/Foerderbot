import React, { useState, useEffect } from 'react';
import { settingsAPI, APIError } from '../../lib/api';

interface PrivacySettings {
	retention_enabled: boolean;
	retention_days: number;
	external_ai_enabled: boolean;
	consent_required: boolean;
	data_export_enabled: boolean;
	data_erase_enabled: boolean;
	collect_local_analytics: boolean;
}

/**
 * Privacy settings tab component.
 */
const PrivacyTab: React.FC = () => {
	const [settings, setSettings] = useState<PrivacySettings>({
		retention_enabled: true,
		retention_days: 365,
		external_ai_enabled: false,
		consent_required: true,
		data_export_enabled: true,
		data_erase_enabled: true,
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
			const data = await settingsAPI.get('privacy');
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
			const response = await settingsAPI.update('privacy', settings);
			if (response.success) {
				setMessage('Privacy settings saved successfully!');
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
			<div className="settings-tab privacy-tab">
				<p>Loading settings...</p>
			</div>
		);
	}

	return (
		<div className="settings-tab privacy-tab">
			<h2>Privacy & GDPR Settings</h2>
			<p>Configure data protection and GDPR compliance settings.</p>

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
					<h3>Data Retention</h3>
					
					<div className="form-group">
						<label htmlFor="retention_enabled">
							<input
								type="checkbox"
								id="retention_enabled"
								checked={settings.retention_enabled}
								onChange={(e) => setSettings({ ...settings, retention_enabled: e.target.checked })}
							/>
							{' '}Enable Automatic Data Retention
						</label>
						<p className="description">Automatically delete old data after specified period</p>
					</div>

					{settings.retention_enabled && (
						<div className="form-group">
							<label htmlFor="retention_days">Retention Period (days)</label>
							<input
								type="number"
								id="retention_days"
								value={settings.retention_days}
								onChange={(e) => setSettings({ ...settings, retention_days: Number(e.target.value) })}
								min="30"
								max="3650"
								aria-describedby="retention_days_desc"
							/>
							<p className="description" id="retention_days_desc">
								Data older than this will be automatically deleted (default: 365 days)
							</p>
							{errors.retention_days && <p className="error-text">{errors.retention_days}</p>}
						</div>
					)}
				</div>

				<div className="form-section">
					<h3>External AI Services</h3>
					
					<div className="form-group">
						<label htmlFor="external_ai_enabled">
							<input
								type="checkbox"
								id="external_ai_enabled"
								checked={settings.external_ai_enabled}
								onChange={(e) => setSettings({ ...settings, external_ai_enabled: e.target.checked })}
							/>
							{' '}Enable External AI Services
						</label>
						<p className="description">
							Use external AI services (e.g., OpenAI) for embeddings and completions
						</p>
					</div>

					{settings.external_ai_enabled && (
						<div className="notice notice-warning" role="alert">
							<p>
								<strong>Privacy Notice:</strong> When enabled, user queries may be sent to external AI services. 
								Ensure users are informed and consent is obtained as required by GDPR.
							</p>
						</div>
					)}

					<div className="form-group">
						<label htmlFor="consent_required">
							<input
								type="checkbox"
								id="consent_required"
								checked={settings.consent_required}
								onChange={(e) => setSettings({ ...settings, consent_required: e.target.checked })}
							/>
							{' '}Require User Consent
						</label>
						<p className="description">
							Show consent notice before using external AI services
						</p>
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
						<p className="description">
							Track queries and interactions locally (no external tracking)
						</p>
					</div>
				</div>

				<div className="form-section">
					<h3>GDPR Rights</h3>
					
					<div className="form-group">
						<label htmlFor="data_export_enabled">
							<input
								type="checkbox"
								id="data_export_enabled"
								checked={settings.data_export_enabled}
								onChange={(e) => setSettings({ ...settings, data_export_enabled: e.target.checked })}
							/>
							{' '}Enable Data Export (Right to Access)
						</label>
						<p className="description">
							Allow users to export their data via WordPress privacy tools
						</p>
					</div>

					<div className="form-group">
						<label htmlFor="data_erase_enabled">
							<input
								type="checkbox"
								id="data_erase_enabled"
								checked={settings.data_erase_enabled}
								onChange={(e) => setSettings({ ...settings, data_erase_enabled: e.target.checked })}
							/>
							{' '}Enable Data Erasure (Right to be Forgotten)
						</label>
						<p className="description">
							Allow users to request deletion of their data via WordPress privacy tools
						</p>
					</div>

					<div className="notice notice-info" role="status">
						<p>
							<strong>Info:</strong> Users can request data export/erasure via 
							WordPress → Settings → Privacy → Export/Erase Personal Data
						</p>
					</div>
				</div>

				<div className="form-actions">
					<button type="submit" className="button button-primary" disabled={saving || loading}>
						{saving ? 'Saving...' : 'Save Privacy Settings'}
					</button>
				</div>
			</form>
		</div>
	);
};

export default PrivacyTab;
