import React, { useState } from 'react';

/**
 * Privacy settings tab component.
 */
const PrivacyTab: React.FC = () => {
	const [settings, setSettings] = useState({
		retention_enabled: true,
		retention_days: 365,
		external_ai_enabled: false,
		consent_required: true,
		data_export_enabled: true,
		data_erase_enabled: true,
	});
	const [saving, setSaving] = useState(false);
	const [message, setMessage] = useState('');

	const handleSave = async (e: React.FormEvent) => {
		e.preventDefault();
		setSaving(true);
		setMessage('');

		try {
			// In a real implementation, this would save to WordPress options
			setTimeout(() => {
				setMessage('Privacy settings saved successfully!');
				setSaving(false);
			}, 500);
		} catch (error) {
			setMessage('Error saving settings.');
			setSaving(false);
		}
	};

	return (
		<div className="settings-tab privacy-tab">
			<h2>Privacy & GDPR Settings</h2>
			<p>Configure data protection and GDPR compliance settings.</p>

			{message && (
				<div className={`notice ${message.includes('successfully') ? 'notice-success' : 'notice-error'}`}>
					<p>{message}</p>
				</div>
			)}

			<form onSubmit={handleSave}>
				<div className="form-section">
					<h3>Data Retention</h3>
					
					<div className="form-group">
						<label>
							<input
								type="checkbox"
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
							/>
							<p className="description">
								Data older than this will be automatically deleted (default: 365 days)
							</p>
						</div>
					)}
				</div>

				<div className="form-section">
					<h3>External AI Services</h3>
					
					<div className="form-group">
						<label>
							<input
								type="checkbox"
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
						<div className="notice notice-warning">
							<p>
								<strong>Privacy Notice:</strong> When enabled, user queries may be sent to external AI services. 
								Ensure users are informed and consent is obtained as required by GDPR.
							</p>
						</div>
					)}

					<div className="form-group">
						<label>
							<input
								type="checkbox"
								checked={settings.consent_required}
								onChange={(e) => setSettings({ ...settings, consent_required: e.target.checked })}
							/>
							{' '}Require User Consent
						</label>
						<p className="description">
							Show consent notice before using external AI services
						</p>
					</div>
				</div>

				<div className="form-section">
					<h3>GDPR Rights</h3>
					
					<div className="form-group">
						<label>
							<input
								type="checkbox"
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
						<label>
							<input
								type="checkbox"
								checked={settings.data_erase_enabled}
								onChange={(e) => setSettings({ ...settings, data_erase_enabled: e.target.checked })}
							/>
							{' '}Enable Data Erasure (Right to be Forgotten)
						</label>
						<p className="description">
							Allow users to request deletion of their data via WordPress privacy tools
						</p>
					</div>

					<div className="notice notice-info">
						<p>
							<strong>Info:</strong> Users can request data export/erasure via 
							WordPress → Settings → Privacy → Export/Erase Personal Data
						</p>
					</div>
				</div>

				<div className="form-actions">
					<button type="submit" className="button button-primary" disabled={saving}>
						{saving ? 'Saving...' : 'Save Privacy Settings'}
					</button>
				</div>
			</form>
		</div>
	);
};

export default PrivacyTab;
