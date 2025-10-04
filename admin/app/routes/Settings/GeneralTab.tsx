import React, { useState, useEffect } from 'react';
import { settingsAPI, APIError } from '../../lib/api';

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
};

interface GeneralSettings {
	site_enabled: boolean;
	default_lang: string;
	cache_enabled: boolean;
	cache_ttl: number;
	rate_limit_enabled: boolean;
	rate_limit_max: number;
	rate_limit_window: number;
}

/**
 * General settings tab component.
 */
const GeneralTab: React.FC = () => {
	const [settings, setSettings] = useState<GeneralSettings>({
		site_enabled: true,
		default_lang: 'de',
		cache_enabled: true,
		cache_ttl: 86400,
		rate_limit_enabled: true,
		rate_limit_max: 60,
		rate_limit_window: 3600,
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
			const data = await settingsAPI.get('general');
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
			const response = await settingsAPI.update('general', settings);
			if (response.success) {
				setMessage('Settings saved successfully!');
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
			<div className="settings-tab general-tab">
				<p>Loading settings...</p>
			</div>
		);
	}

	return (
		<div className="settings-tab general-tab">
			<h2>General Settings</h2>
			<p>Configure general plugin behavior and performance settings.</p>

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
					<h3>Site Settings</h3>
					
					<div className="form-group">
						<label htmlFor="site_enabled">
							<input
								type="checkbox"
								id="site_enabled"
								checked={settings.site_enabled}
								onChange={(e) => setSettings({ ...settings, site_enabled: e.target.checked })}
							/>
							{' '}Enable Plugin Site-Wide
						</label>
						<p className="description">Activate the chatbot functionality across the entire site</p>
					</div>

					<div className="form-group">
						<label htmlFor="default_lang">Default Language</label>
						<select
							id="default_lang"
							value={settings.default_lang}
							onChange={(e) => setSettings({ ...settings, default_lang: e.target.value })}
						>
							<option value="de">Deutsch (German)</option>
							<option value="en">English</option>
							<option value="fr">Français (French)</option>
							<option value="es">Español (Spanish)</option>
							<option value="it">Italiano (Italian)</option>
						</select>
						<p className="description">Primary language for chatbot responses</p>
						{errors.default_lang && <p className="error-text">{errors.default_lang}</p>}
					</div>
				</div>

				<div className="form-section">
					<h3>Caching</h3>
					
					<div className="form-group">
						<label htmlFor="cache_enabled">
							<input
								type="checkbox"
								id="cache_enabled"
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
							{errors.cache_ttl && <p className="error-text">{errors.cache_ttl}</p>}
						</div>
					)}
				</div>

				<div className="form-section">
					<h3>Rate Limiting</h3>
					
					<div className="form-group">
						<label htmlFor="rate_limit_enabled">
							<input
								type="checkbox"
								id="rate_limit_enabled"
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
								{errors.rate_limit_max && <p className="error-text">{errors.rate_limit_max}</p>}
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
								{errors.rate_limit_window && <p className="error-text">{errors.rate_limit_window}</p>}
							</div>
						</>
					)}
				</div>

				<div className="form-actions">
					<button type="submit" className="button button-primary" disabled={saving || loading}>
						{saving ? 'Saving...' : 'Save Settings'}
					</button>
				</div>
			</form>
		</div>
	);
};

export default GeneralTab;
