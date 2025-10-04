import React, { useState, useEffect } from 'react';
import { settingsAPI, APIError } from '../../lib/api';

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
};

interface AccountsSettings {
	account_page_id: number;
	profile_url: string;
	profile_url_override: string;
}

/**
 * Accounts settings tab component.
 */
const AccountsTab: React.FC = () => {
	const [settings, setSettings] = useState<AccountsSettings>({
		account_page_id: 0,
		profile_url: '',
		profile_url_override: '',
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
			const data = await settingsAPI.get('accounts');
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
			const response = await settingsAPI.update('accounts', settings);
			if (response.success) {
				setMessage('Account settings saved successfully!');
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
			<div className="settings-tab accounts-tab">
				<p>Loading settings...</p>
			</div>
		);
	}

	return (
		<div className="settings-tab accounts-tab">
			<h2>Account Settings</h2>
			<p>Configure account-related pages and URLs for user profiles.</p>

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
					<h3>Account Pages</h3>
					
					<div className="form-group">
						<label htmlFor="account_page_id">Account Page ID</label>
						<input
							type="number"
							id="account_page_id"
							value={settings.account_page_id}
							onChange={(e) => setSettings({ ...settings, account_page_id: Number(e.target.value) })}
							min="0"
						/>
						<p className="description">WordPress Page ID for the main account/profile page</p>
						{errors.account_page_id && <p className="error-text">{errors.account_page_id}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="profile_url">Profile URL</label>
						<input
							type="url"
							id="profile_url"
							value={settings.profile_url}
							onChange={(e) => setSettings({ ...settings, profile_url: e.target.value })}
							placeholder="https://example.com/profile"
						/>
						<p className="description">Default URL pattern for user profiles</p>
						{errors.profile_url && <p className="error-text">{errors.profile_url}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="profile_url_override">Profile URL Override</label>
						<input
							type="url"
							id="profile_url_override"
							value={settings.profile_url_override}
							onChange={(e) => setSettings({ ...settings, profile_url_override: e.target.value })}
							placeholder="https://example.com/custom-profile"
						/>
						<p className="description">Override URL to use instead of the default profile URL</p>
						{errors.profile_url_override && <p className="error-text">{errors.profile_url_override}</p>}
					</div>
				</div>

				<div className="form-actions">
					<button type="submit" className="button button-primary" disabled={saving || loading}>
						{saving ? 'Saving...' : 'Save Account Settings'}
					</button>
				</div>
			</form>
		</div>
	);
};

export default AccountsTab;
