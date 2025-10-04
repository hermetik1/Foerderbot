import React, { useState, useEffect } from 'react';
import { settingsAPI, APIError } from '../../lib/api';

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
	branding: any;
};

interface BrandingSettings {
	logo_url: string;
	product_name: string;
	primary_color: string;
	secondary_color: string;
	theme: string;
	icon_color: string;
	header_text_color: string;
	faq_header_title: string;
	advisor_header_title: string;
	favicon_url: string;
	footer_text: string;
	privacy_url: string;
	imprint_url: string;
	powered_by: boolean;
}

/**
 * White-label settings tab component.
 */
const WhiteLabelTab: React.FC = () => {
	const [config, setConfig] = useState<BrandingSettings>({
		logo_url: '',
		product_name: 'KI Kraft',
		primary_color: '#3b82f6',
		secondary_color: '#60a5fa',
		theme: 'auto',
		icon_color: '#3b82f6',
		header_text_color: '#111827',
		faq_header_title: 'Häufige Fragen',
		advisor_header_title: 'Mitglieder-Chat',
		favicon_url: '',
		footer_text: '',
		privacy_url: '',
		imprint_url: '',
		powered_by: true,
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
			const data = await settingsAPI.get('branding');
			setConfig(data);
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
			const response = await settingsAPI.update('branding', config);
			if (response.success) {
				setMessage('Branding settings saved successfully!');
				// Update CSS variables
				document.documentElement.style.setProperty('--kraft-primary-color', config.primary_color);
				document.documentElement.style.setProperty('--kraft-secondary-color', config.secondary_color);
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
			<div className="settings-tab whitelabel-tab">
				<p>Loading settings...</p>
			</div>
		);
	}

	return (
		<div className="settings-tab whitelabel-tab">
			<h2>White-Label Branding</h2>
			<p>Customize the appearance and branding of the KI Kraft plugin.</p>

			{message && (
				<div 
					className={`notice ${message.includes('successfully') ? 'notice-success' : 'notice-error'}`}
					role="status"
					aria-live="polite"
				>
					<p>{message}</p>
				</div>
			)}

			<form onSubmit={handleSave} className="branding-form">
				<div className="form-section">
					<h3>Visual Identity</h3>
					
					<div className="form-group">
						<label htmlFor="product_name">Product Name</label>
						<input
							type="text"
							id="product_name"
							value={config.product_name}
							onChange={(e) => setConfig({ ...config, product_name: e.target.value })}
							aria-describedby="product_name_desc"
						/>
						<p className="description" id="product_name_desc">The name displayed in the plugin interface</p>
						{errors.product_name && <p className="error-text">{errors.product_name}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="logo_url">Logo URL</label>
						<input
							type="url"
							id="logo_url"
							value={config.logo_url}
							onChange={(e) => setConfig({ ...config, logo_url: e.target.value })}
							placeholder="https://example.com/logo.png"
							aria-describedby="logo_url_desc"
						/>
						<p className="description" id="logo_url_desc">URL to your custom logo image</p>
						{errors.logo_url && <p className="error-text">{errors.logo_url}</p>}
						{config.logo_url && (
							<div className="preview">
								<img src={config.logo_url} alt="Logo preview" style={{ maxHeight: '60px' }} />
							</div>
						)}
					</div>

					<div className="form-group">
						<label htmlFor="primary_color">Primary Color</label>
						<div className="color-input">
							<input
								type="color"
								id="primary_color"
								value={config.primary_color}
								onChange={(e) => setConfig({ ...config, primary_color: e.target.value })}
								aria-describedby="primary_color_desc"
							/>
							<input
								type="text"
								value={config.primary_color}
								onChange={(e) => setConfig({ ...config, primary_color: e.target.value })}
								placeholder="#3b82f6"
								pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
							/>
						</div>
						<p className="description" id="primary_color_desc">Main brand color (hex format)</p>
						{errors.primary_color && <p className="error-text">{errors.primary_color}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="secondary_color">Secondary Color</label>
						<div className="color-input">
							<input
								type="color"
								id="secondary_color"
								value={config.secondary_color}
								onChange={(e) => setConfig({ ...config, secondary_color: e.target.value })}
								aria-describedby="secondary_color_desc"
							/>
							<input
								type="text"
								value={config.secondary_color}
								onChange={(e) => setConfig({ ...config, secondary_color: e.target.value })}
								placeholder="#60a5fa"
								pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
							/>
						</div>
						<p className="description" id="secondary_color_desc">Secondary brand color (hex format)</p>
						{errors.secondary_color && <p className="error-text">{errors.secondary_color}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="theme">Theme</label>
						<select
							id="theme"
							value={config.theme}
							onChange={(e) => setConfig({ ...config, theme: e.target.value })}
						>
							<option value="light">Light</option>
							<option value="dark">Dark</option>
							<option value="auto">Auto (system preference)</option>
						</select>
						<p className="description">Color theme for the chatbot interface</p>
						{errors.theme && <p className="error-text">{errors.theme}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="icon_color">Icon Color</label>
						<div className="color-input">
							<input
								type="color"
								id="icon_color"
								value={config.icon_color}
								onChange={(e) => setConfig({ ...config, icon_color: e.target.value })}
							/>
							<input
								type="text"
								value={config.icon_color}
								onChange={(e) => setConfig({ ...config, icon_color: e.target.value })}
								placeholder="#3b82f6"
								pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
							/>
						</div>
						<p className="description">Color for icons in the chatbot</p>
						{errors.icon_color && <p className="error-text">{errors.icon_color}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="header_text_color">Header Text Color</label>
						<div className="color-input">
							<input
								type="color"
								id="header_text_color"
								value={config.header_text_color}
								onChange={(e) => setConfig({ ...config, header_text_color: e.target.value })}
							/>
							<input
								type="text"
								value={config.header_text_color}
								onChange={(e) => setConfig({ ...config, header_text_color: e.target.value })}
								placeholder="#111827"
								pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
							/>
						</div>
						<p className="description">Color for header text in the chatbot</p>
						{errors.header_text_color && <p className="error-text">{errors.header_text_color}</p>}
					</div>
				</div>

				<div className="form-section">
					<h3>Chatbot Titles</h3>

					<div className="form-group">
						<label htmlFor="faq_header_title">FAQ Bot Header Title</label>
						<input
							type="text"
							id="faq_header_title"
							value={config.faq_header_title}
							onChange={(e) => setConfig({ ...config, faq_header_title: e.target.value })}
						/>
						<p className="description">Title shown in FAQ chatbot header</p>
						{errors.faq_header_title && <p className="error-text">{errors.faq_header_title}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="advisor_header_title">Advisor Bot Header Title</label>
						<input
							type="text"
							id="advisor_header_title"
							value={config.advisor_header_title}
							onChange={(e) => setConfig({ ...config, advisor_header_title: e.target.value })}
						/>
						<p className="description">Title shown in advisor chatbot header</p>
						{errors.advisor_header_title && <p className="error-text">{errors.advisor_header_title}</p>}
					</div>
				</div>

				<div className="form-section">
					<h3>Footer & Legal</h3>

					<div className="form-group">
						<label htmlFor="footer_text">Footer Text</label>
						<textarea
							id="footer_text"
							value={config.footer_text}
							onChange={(e) => setConfig({ ...config, footer_text: e.target.value })}
							rows={3}
							aria-describedby="footer_text_desc"
						/>
						<p className="description" id="footer_text_desc">Custom footer text displayed in the chatbot</p>
						{errors.footer_text && <p className="error-text">{errors.footer_text}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="privacy_url">Privacy Policy URL</label>
						<input
							type="url"
							id="privacy_url"
							value={config.privacy_url}
							onChange={(e) => setConfig({ ...config, privacy_url: e.target.value })}
							placeholder="https://example.com/privacy"
							aria-describedby="privacy_url_desc"
						/>
						<p className="description" id="privacy_url_desc">Link to your privacy policy</p>
						{errors.privacy_url && <p className="error-text">{errors.privacy_url}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="imprint_url">Imprint URL</label>
						<input
							type="url"
							id="imprint_url"
							value={config.imprint_url}
							onChange={(e) => setConfig({ ...config, imprint_url: e.target.value })}
							placeholder="https://example.com/imprint"
							aria-describedby="imprint_url_desc"
						/>
						<p className="description" id="imprint_url_desc">Link to your imprint page (required in Germany)</p>
						{errors.imprint_url && <p className="error-text">{errors.imprint_url}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="favicon_url">Favicon URL</label>
						<input
							type="url"
							id="favicon_url"
							value={config.favicon_url}
							onChange={(e) => setConfig({ ...config, favicon_url: e.target.value })}
							placeholder="https://example.com/favicon.ico"
							aria-describedby="favicon_url_desc"
						/>
						<p className="description" id="favicon_url_desc">URL to your custom favicon</p>
						{errors.favicon_url && <p className="error-text">{errors.favicon_url}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="powered_by">
							<input
								type="checkbox"
								id="powered_by"
								checked={config.powered_by}
								onChange={(e) => setConfig({ ...config, powered_by: e.target.checked })}
							/>
							{' '}Show "Powered by KI Kraft" attribution
						</label>
					</div>
				</div>

				<div className="form-actions">
					<button type="submit" className="button button-primary" disabled={saving || loading}>
						{saving ? 'Saving...' : 'Save Branding Settings'}
					</button>
				</div>
			</form>
</div>
);
};

export default WhiteLabelTab;
