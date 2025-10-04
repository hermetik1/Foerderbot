import React, { useState, useEffect } from 'react';

declare const kiKraftAdmin: {
	apiUrl: string;
	nonce: string;
	branding: any;
};

/**
 * White-label settings tab component.
 */
const WhiteLabelTab: React.FC = () => {
	const [config, setConfig] = useState({
		logo_url: '',
		product_name: 'KI Kraft',
		primary_color: '#3b82f6',
		secondary_color: '#60a5fa',
		favicon_url: '',
		footer_text: '',
		privacy_url: '',
		imprint_url: '',
		powered_by: true,
	});
	const [saving, setSaving] = useState(false);
	const [message, setMessage] = useState('');

	useEffect(() => {
		if (kiKraftAdmin.branding) {
			setConfig(kiKraftAdmin.branding);
		}
	}, []);

	const handleSave = async (e: React.FormEvent) => {
		e.preventDefault();
		setSaving(true);
		setMessage('');

		try {
			const response = await fetch(`${kiKraftAdmin.apiUrl}/branding`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': kiKraftAdmin.nonce,
				},
				body: JSON.stringify(config),
			});

			if (response.ok) {
				setMessage('Branding settings saved successfully!');
			} else {
				setMessage('Failed to save settings.');
			}
		} catch (error) {
			console.error('Failed to save branding:', error);
			setMessage('Error saving settings.');
		} finally {
			setSaving(false);
		}
	};

	return (
		<div className="settings-tab whitelabel-tab">
			<h2>White-Label Branding</h2>
			<p>Customize the appearance and branding of the KI Kraft plugin.</p>

			{message && (
				<div className={`notice ${message.includes('successfully') ? 'notice-success' : 'notice-error'}`}>
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
						/>
						<p className="description">The name displayed in the plugin interface</p>
					</div>

					<div className="form-group">
						<label htmlFor="logo_url">Logo URL</label>
						<input
							type="url"
							id="logo_url"
							value={config.logo_url}
							onChange={(e) => setConfig({ ...config, logo_url: e.target.value })}
							placeholder="https://example.com/logo.png"
						/>
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
							/>
							<input
								type="text"
								value={config.primary_color}
								onChange={(e) => setConfig({ ...config, primary_color: e.target.value })}
								placeholder="#3b82f6"
							/>
						</div>
					</div>

					<div className="form-group">
						<label htmlFor="secondary_color">Secondary Color</label>
						<div className="color-input">
							<input
								type="color"
								id="secondary_color"
								value={config.secondary_color}
								onChange={(e) => setConfig({ ...config, secondary_color: e.target.value })}
							/>
							<input
								type="text"
								value={config.secondary_color}
								onChange={(e) => setConfig({ ...config, secondary_color: e.target.value })}
								placeholder="#60a5fa"
							/>
						</div>
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
						/>
					</div>

					<div className="form-group">
						<label htmlFor="privacy_url">Privacy Policy URL</label>
						<input
							type="url"
							id="privacy_url"
							value={config.privacy_url}
							onChange={(e) => setConfig({ ...config, privacy_url: e.target.value })}
							placeholder="https://example.com/privacy"
						/>
					</div>

					<div className="form-group">
						<label htmlFor="imprint_url">Imprint URL</label>
						<input
							type="url"
							id="imprint_url"
							value={config.imprint_url}
							onChange={(e) => setConfig({ ...config, imprint_url: e.target.value })}
							placeholder="https://example.com/imprint"
						/>
					</div>

					<div className="form-group">
						<label>
							<input
								type="checkbox"
								checked={config.powered_by}
								onChange={(e) => setConfig({ ...config, powered_by: e.target.checked })}
							/>
							{' '}Show "Powered by KI Kraft" attribution
						</label>
					</div>
				</div>

				<div className="form-actions">
					<button type="submit" className="button button-primary" disabled={saving}>
						{saving ? 'Saving...' : 'Save Branding Settings'}
					</button>
				</div>
			</form>
		</div>
	);
};

export default WhiteLabelTab;
