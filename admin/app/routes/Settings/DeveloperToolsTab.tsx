import React, { useState } from 'react';

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
};

/**
 * Developer tools tab component with seed functionality.
 */
const DeveloperToolsTab: React.FC = () => {
	const [seeding, setSeeding] = useState(false);
	const [message, setMessage] = useState('');
	const [result, setResult] = useState('');
	const [showConfirm, setShowConfirm] = useState(false);

	const handleSeed = async () => {
		setSeeding(true);
		setMessage('');
		setResult('');
		setShowConfirm(false);

		try {
			// Call the seeder function via a custom REST endpoint
			// For now, we'll just simulate it since we need to add a REST endpoint
			const response = await fetch(`${kraftAIChatAdmin.apiUrl}/seed`, {
				method: 'POST',
				headers: {
					'X-WP-Nonce': kraftAIChatAdmin.nonce,
					'Content-Type': 'application/json',
				},
			});

			if (!response.ok) {
				throw new Error('Failed to seed data');
			}

			const data = await response.json();
			setMessage('✅ Success! Sample data has been created.');
			setResult(`Added ${data.count || 'multiple'} sample FAQ entries to the knowledge base.`);
		} catch (error: any) {
			setMessage('❌ Error: Failed to seed sample data.');
			setResult(error.message || 'Unknown error occurred');
		} finally {
			setSeeding(false);
		}
	};

	return (
		<div className="settings-tab developer-tools-tab">
			<h2>Developer / Tools</h2>
			<p>Advanced tools for development and testing. <strong>Use only in staging/development environments!</strong></p>

			<div className="form-section">
				<h3>📦 Seed Sample Data</h3>
				<p>
					This tool will populate the knowledge base with sample FAQ entries in German. 
					This is useful for testing and development purposes.
				</p>

				<div className="warning-box" style={{ 
					padding: '15px', 
					background: '#fff3cd', 
					border: '1px solid #ffc107', 
					borderRadius: '4px', 
					marginBottom: '20px' 
				}}>
					<strong>⚠️ Warning:</strong>
					<ul style={{ marginTop: '10px', marginBottom: 0 }}>
						<li>Only use this in staging or development environments</li>
						<li>This will add sample data to your knowledge base</li>
						<li>Sample data may need to be manually removed afterwards</li>
					</ul>
				</div>

				{message && (
					<div 
						className={`notice ${message.includes('Success') ? 'notice-success' : 'notice-error'}`}
						style={{ marginBottom: '20px' }}
					>
						<p>{message}</p>
					</div>
				)}

				{result && (
					<div className="result-box" style={{ 
						padding: '15px', 
						background: '#f5f5f5', 
						border: '1px solid #ddd', 
						borderRadius: '4px', 
						marginBottom: '20px',
						fontFamily: 'monospace',
						whiteSpace: 'pre-wrap'
					}}>
						{result}
					</div>
				)}

				{!showConfirm ? (
					<button 
						type="button" 
						className="button button-secondary" 
						onClick={() => setShowConfirm(true)}
						disabled={seeding}
					>
						🌱 Seed Sample Data
					</button>
				) : (
					<div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
						<p style={{ margin: 0, fontWeight: 'bold' }}>Are you sure?</p>
						<button 
							type="button" 
							className="button button-primary" 
							onClick={handleSeed}
							disabled={seeding}
						>
							{seeding ? '⏳ Seeding...' : '✅ Yes, Seed Data'}
						</button>
						<button 
							type="button" 
							className="button" 
							onClick={() => setShowConfirm(false)}
							disabled={seeding}
						>
							❌ Cancel
						</button>
					</div>
				)}
			</div>

			<div className="form-section">
				<h3>🔧 Other Tools</h3>
				<p>Additional development tools will be added here in future updates.</p>
				<p style={{ color: '#666', fontStyle: 'italic' }}>
					Tools for clearing caches, resetting analytics, debugging, etc.
				</p>
			</div>
		</div>
	);
};

export default DeveloperToolsTab;
