import React, { useState, useEffect } from 'react';
import { settingsAPI, APIError } from '../../lib/api';

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
};

interface IntegrationsSettings {
	openai_api_key: string;
	whisper_api_key: string;
	rag_service: string;
	rag_endpoint: string;
}

/**
 * Integrations settings tab component.
 */
const IntegrationsTab: React.FC = () => {
	const [settings, setSettings] = useState<IntegrationsSettings>({
		openai_api_key: '',
		whisper_api_key: '',
		rag_service: '',
		rag_endpoint: '',
	});
	const [loading, setLoading] = useState(true);
	const [saving, setSaving] = useState(false);
	const [message, setMessage] = useState('');
	const [errors, setErrors] = useState<Record<string, string>>({});
	const [showOpenAIKey, setShowOpenAIKey] = useState(false);
	const [showWhisperKey, setShowWhisperKey] = useState(false);

	useEffect(() => {
		loadSettings();
	}, []);

	const loadSettings = async () => {
		try {
			const data = await settingsAPI.get('integrations');
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
			const response = await settingsAPI.update('integrations', settings);
			if (response.success) {
				setMessage('Integration settings saved successfully!');
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

	const maskKey = (key: string) => {
		if (!key || key.length < 8) return '●●●●●●●●';
		return key.substring(0, 4) + '●'.repeat(Math.max(8, key.length - 8)) + key.substring(key.length - 4);
	};

	if (loading) {
		return (
			<div className="settings-tab integrations-tab">
				<p>Loading settings...</p>
			</div>
		);
	}

	return (
		<div className="settings-tab integrations-tab">
			<h2>Integrations</h2>
			<p>Configure external AI services and RAG (Retrieval Augmented Generation) endpoints.</p>

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
					<h3>OpenAI Integration</h3>
					
					<div className="form-group">
						<label htmlFor="openai_api_key">OpenAI API Key</label>
						<div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
							<input
								type={showOpenAIKey ? 'text' : 'password'}
								id="openai_api_key"
								value={showOpenAIKey ? settings.openai_api_key : maskKey(settings.openai_api_key)}
								onChange={(e) => showOpenAIKey && setSettings({ ...settings, openai_api_key: e.target.value })}
								placeholder="sk-..."
								style={{ flex: 1 }}
								onFocus={() => setShowOpenAIKey(true)}
								onBlur={() => setShowOpenAIKey(false)}
							/>
							<button
								type="button"
								className="button"
								onClick={() => setShowOpenAIKey(!showOpenAIKey)}
							>
								{showOpenAIKey ? '👁️ Hide' : '👁️ Show'}
							</button>
						</div>
						<p className="description">Your OpenAI API key for GPT models</p>
						{errors.openai_api_key && <p className="error-text">{errors.openai_api_key}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="whisper_api_key">Whisper API Key</label>
						<div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
							<input
								type={showWhisperKey ? 'text' : 'password'}
								id="whisper_api_key"
								value={showWhisperKey ? settings.whisper_api_key : maskKey(settings.whisper_api_key)}
								onChange={(e) => showWhisperKey && setSettings({ ...settings, whisper_api_key: e.target.value })}
								placeholder="sk-..."
								style={{ flex: 1 }}
								onFocus={() => setShowWhisperKey(true)}
								onBlur={() => setShowWhisperKey(false)}
							/>
							<button
								type="button"
								className="button"
								onClick={() => setShowWhisperKey(!showWhisperKey)}
							>
								{showWhisperKey ? '👁️ Hide' : '👁️ Show'}
							</button>
						</div>
						<p className="description">Your Whisper API key for speech-to-text</p>
						{errors.whisper_api_key && <p className="error-text">{errors.whisper_api_key}</p>}
					</div>
				</div>

				<div className="form-section">
					<h3>RAG (Retrieval Augmented Generation)</h3>
					
					<div className="form-group">
						<label htmlFor="rag_service">RAG Service Name</label>
						<input
							type="text"
							id="rag_service"
							value={settings.rag_service}
							onChange={(e) => setSettings({ ...settings, rag_service: e.target.value })}
							placeholder="e.g., Pinecone, Weaviate, Qdrant"
						/>
						<p className="description">Name of the RAG service being used</p>
						{errors.rag_service && <p className="error-text">{errors.rag_service}</p>}
					</div>

					<div className="form-group">
						<label htmlFor="rag_endpoint">RAG Endpoint URL</label>
						<input
							type="url"
							id="rag_endpoint"
							value={settings.rag_endpoint}
							onChange={(e) => setSettings({ ...settings, rag_endpoint: e.target.value })}
							placeholder="https://api.example.com/rag"
						/>
						<p className="description">Full URL to your RAG service endpoint</p>
						{errors.rag_endpoint && <p className="error-text">{errors.rag_endpoint}</p>}
					</div>
				</div>

				<div className="form-actions">
					<button type="submit" className="button button-primary" disabled={saving || loading}>
						{saving ? 'Saving...' : 'Save Integration Settings'}
					</button>
				</div>
			</form>
		</div>
	);
};

export default IntegrationsTab;
