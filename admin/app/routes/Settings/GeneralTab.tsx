import React, { useState } from 'react';

declare const kiKraftAdmin: {
	apiUrl: string;
	nonce: string;
};

/**
 * General settings tab component.
 */
const GeneralTab: React.FC = () => {
	const [settings, setSettings] = useState({
		cache_enabled: true,
		cache_ttl: 86400,
		rate_limit_enabled: true,
		rate_limit_max: 60,
		rate_limit_window: 3600,
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
				setMessage('Settings saved successfully!');
				setSaving(false);
			}, 500);
		} catch (error) {
			setMessage('Error saving settings.');
			setSaving(false);
		}
	};

	return (
		<div className="settings-tab general-tab">
			<h2>General Settings</h2>
			<p>Configure general plugin behavior and performance settings.</p>

			{message && (
				<div className={`notice ${message.includes('successfully') ? 'notice-success' : 'notice-error'}`}>
					<p>{message}</p>
				</div>
			)}

			<form onSubmit={handleSave}>
				<div className="form-section">
					<h3>Caching</h3>
					
					<div className="form-group">
						<label>
							<input
								type="checkbox"
								checked={settings.cache_enabled}
								onChange={(e) => setSettings({ ...settings, cache_enabled: e.target.checked })}
							/>
							{' '}Enable Query Caching
						</label>
						<p className="description">Cache frequent queries to improve performance</p>
					</div>

					{settings.cache_enabled && (
						<div className="form-group">
							<label htmlFor="cache_ttl">Cache TTL (seconds)</label>
							<input
								type="number"
								id="cache_ttl"
								value={settings.cache_ttl}
								onChange={(e) => setSettings({ ...settings, cache_ttl: Number(e.target.value) })}
								min="60"
								max="604800"
							/>
							<p className="description">How long to cache results (default: 86400 = 24 hours)</p>
						</div>
					)}
				</div>

				<div className="form-section">
					<h3>Rate Limiting</h3>
					
					<div className="form-group">
						<label>
							<input
								type="checkbox"
								checked={settings.rate_limit_enabled}
								onChange={(e) => setSettings({ ...settings, rate_limit_enabled: e.target.checked })}
							/>
							{' '}Enable Rate Limiting
						</label>
						<p className="description">Prevent abuse by limiting query frequency</p>
					</div>

					{settings.rate_limit_enabled && (
						<>
							<div className="form-group">
								<label htmlFor="rate_limit_max">Maximum Requests</label>
								<input
									type="number"
									id="rate_limit_max"
									value={settings.rate_limit_max}
									onChange={(e) => setSettings({ ...settings, rate_limit_max: Number(e.target.value) })}
									min="10"
									max="1000"
								/>
								<p className="description">Maximum number of requests per time window</p>
							</div>

							<div className="form-group">
								<label htmlFor="rate_limit_window">Time Window (seconds)</label>
								<input
									type="number"
									id="rate_limit_window"
									value={settings.rate_limit_window}
									onChange={(e) => setSettings({ ...settings, rate_limit_window: Number(e.target.value) })}
									min="60"
									max="86400"
								/>
								<p className="description">Time window for rate limiting (default: 3600 = 1 hour)</p>
							</div>
						</>
					)}
				</div>

				<div className="form-actions">
					<button type="submit" className="button button-primary" disabled={saving}>
						{saving ? 'Saving...' : 'Save Settings'}
					</button>
				</div>
			</form>
		</div>
	);
};

export default GeneralTab;
