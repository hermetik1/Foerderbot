import React, { useState, useEffect } from 'react';
import { settingsAPI } from '../lib/api';

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
	branding: any;
	page?: string;
};

interface StatusData {
	site_enabled: boolean;
	faq_enabled: boolean;
	advisor_enabled: boolean;
	max_message_length: number;
}

interface IntegrationsData {
	openai_api_key: string;
	whisper_api_key: string;
	rag_service: string;
	rag_endpoint: string;
}

/**
 * Main dashboard component for Kraft AI Chat admin.
 */
const Dashboard: React.FC = () => {
	const [status, setStatus] = useState<StatusData | null>(null);
	const [integrations, setIntegrations] = useState<IntegrationsData | null>(null);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		loadDashboardData();
	}, []);

	const loadDashboardData = async () => {
		try {
			const [generalData, integrationsData] = await Promise.all([
				settingsAPI.get('general'),
				settingsAPI.get('integrations'),
			]);
			setStatus(generalData);
			setIntegrations(integrationsData);
		} catch (error) {
			console.error('Failed to load dashboard data:', error);
		} finally {
			setLoading(false);
		}
	};

	const renderStatusBadge = (enabled: boolean) => {
		return enabled ? (
			<span className="status-badge status-active">✅ Enabled</span>
		) : (
			<span className="status-badge status-inactive">❌ Disabled</span>
		);
	};

	const renderKeyStatus = (key: string) => {
		return key && key.length > 0 ? (
			<span className="status-badge status-active">✅ Configured</span>
		) : (
			<span className="status-badge status-inactive">⚠️ Not Set</span>
		);
	};

	if (loading) {
		return (
			<div className="ki-kraft-dashboard">
				<h1>Kraft AI Chat Dashboard</h1>
				<p>Loading dashboard...</p>
			</div>
		);
	}

	return (
		<div className="ki-kraft-dashboard">
			<div className="dashboard-header">
				<h1>Kraft AI Chat Dashboard</h1>
				<p>Übersicht über Status, Verbindungen und Statistiken</p>
			</div>

			<div className="dashboard-grid">
				{/* Status Section */}
				<div className="dashboard-card status-card">
					<h2>📊 Status</h2>
					<div className="card-content">
						<div className="status-item">
							<span className="label">Plugin aktiviert:</span>
							{renderStatusBadge(status?.site_enabled ?? false)}
						</div>
						<div className="status-item">
							<span className="label">FAQ-Bot:</span>
							{renderStatusBadge(status?.faq_enabled ?? false)}
						</div>
						<div className="status-item">
							<span className="label">Advisor-Bot:</span>
							{renderStatusBadge(status?.advisor_enabled ?? false)}
						</div>
						<div className="status-item">
							<span className="label">Max. Nachrichtenlänge:</span>
							<span className="value">{status?.max_message_length ?? 1000} Zeichen</span>
						</div>
					</div>
				</div>

				{/* Connections Section */}
				<div className="dashboard-card connections-card">
					<h2>🔌 Verbindungen</h2>
					<div className="card-content">
						<div className="status-item">
							<span className="label">OpenAI Key:</span>
							{renderKeyStatus(integrations?.openai_api_key ?? '')}
						</div>
						<div className="status-item">
							<span className="label">Whisper Key:</span>
							{renderKeyStatus(integrations?.whisper_api_key ?? '')}
						</div>
						<div className="status-item">
							<span className="label">RAG Service:</span>
							<span className="value">{integrations?.rag_service || '—'}</span>
						</div>
						<div className="status-item">
							<span className="label">RAG Endpoint:</span>
							<span className="value">{integrations?.rag_endpoint ? '✅ Konfiguriert' : '⚠️ Nicht gesetzt'}</span>
						</div>
					</div>
				</div>

				{/* Quick Actions Section */}
				<div className="dashboard-card actions-card">
					<h2>⚡ Schnellaktionen</h2>
					<div className="card-content">
						<a href="?page=kraft-ai-chat-settings" className="action-button">
							⚙️ Zu den Settings
						</a>
						<a href="https://github.com/hermetik1/Foerderbot" target="_blank" rel="noopener noreferrer" className="action-button">
							📖 Dokumentation
						</a>
						<button className="action-button" onClick={() => alert('Test-Chat würde hier geöffnet')}>
							💬 Test-Chat öffnen
						</button>
					</div>
				</div>

				{/* Usage/Statistics Section */}
				<div className="dashboard-card stats-card">
					<h2>📈 Nutzung / Statistiken</h2>
					<div className="card-content">
						<p className="empty-state">
							Statistiken werden angezeigt, wenn lokale Analytics aktiv sind.
						</p>
						<p className="hint">
							Aktivieren Sie lokale Analytics in den <a href="?page=kraft-ai-chat-settings&section=analytics-settings">Analytics-Einstellungen</a>.
						</p>
					</div>
				</div>

				{/* Knowledge Base Section */}
				<div className="dashboard-card knowledge-card">
					<h2>📚 Knowledge Base / Inhalte</h2>
					<div className="card-content">
						<div className="status-item">
							<span className="label">Anzahl Dokumente:</span>
							<span className="value">—</span>
						</div>
						<div className="status-item">
							<span className="label">Letztes Update:</span>
							<span className="value">—</span>
						</div>
						<a href="?page=kraft-ai-chat-settings&section=knowledge" className="action-button small">
							📝 Wissensbasis verwalten
						</a>
					</div>
				</div>

				{/* System Health Section */}
				<div className="dashboard-card health-card">
					<h2>🏥 System Health</h2>
					<div className="card-content">
						<div className="status-item">
							<span className="label">WP Version:</span>
							<span className="value">—</span>
						</div>
						<div className="status-item">
							<span className="label">PHP Version:</span>
							<span className="value">—</span>
						</div>
						<div className="status-item">
							<span className="label">REST erreichbar:</span>
							<span className="status-badge status-active">✅ OK</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default Dashboard;
