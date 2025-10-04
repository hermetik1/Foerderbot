import React, { useState, useEffect } from 'react';
import { settingsAPI, knowledgeAPI, APIError } from '../../lib/api';

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
};

interface KnowledgeEntry {
	id: number;
	title: string;
	content: string;
	scope: string;
	created_at: string;
}

interface KnowledgeSettings {
	chunk_max_tokens: number;
	chunk_overlap: number;
	similarity_threshold: number;
	max_results: number;
}

/**
 * Knowledge base settings tab component.
 */
const KnowledgeTab: React.FC = () => {
	const [entries, setEntries] = useState<KnowledgeEntry[]>([]);
	const [settings, setSettings] = useState<KnowledgeSettings>({
		chunk_max_tokens: 500,
		chunk_overlap: 50,
		similarity_threshold: 0.7,
		max_results: 5,
	});
	const [loading, setLoading] = useState(true);
	const [loadingSettings, setLoadingSettings] = useState(true);
	const [showAdd, setShowAdd] = useState(false);
	const [showSettings, setShowSettings] = useState(false);
	const [newEntry, setNewEntry] = useState({ title: '', content: '', scope: 'public' });
	const [saving, setSaving] = useState(false);
	const [message, setMessage] = useState('');
	const [errors, setErrors] = useState<Record<string, string>>({});

	useEffect(() => {
		fetchEntries();
		loadSettings();
	}, []);

	const loadSettings = async () => {
		try {
			const data = await settingsAPI.get('knowledge');
			setSettings(data);
		} catch (error) {
			console.error('Failed to load knowledge settings:', error);
		} finally {
			setLoadingSettings(false);
		}
	};

	const handleSaveSettings = async (e: React.FormEvent) => {
		e.preventDefault();
		setSaving(true);
		setMessage('');
		setErrors({});

		try {
			const response = await settingsAPI.update('knowledge', settings);
			if (response.success) {
				setMessage('Knowledge base settings saved successfully!');
				setShowSettings(false);
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

	const fetchEntries = async () => {
		try {
			const data = await knowledgeAPI.getEntries(50);
			setEntries(data.entries || []);
		} catch (error) {
			console.error('Failed to fetch knowledge entries:', error);
		} finally {
			setLoading(false);
		}
	};

	const handleAdd = async (e: React.FormEvent) => {
		e.preventDefault();
		try {
			await knowledgeAPI.addEntry(newEntry);
			setNewEntry({ title: '', content: '', scope: 'public' });
			setShowAdd(false);
			fetchEntries();
		} catch (error) {
			console.error('Failed to add entry:', error);
		}
	};

	const handleDelete = async (id: number) => {
		if (!confirm('Are you sure you want to delete this entry?')) return;
		try {
			await knowledgeAPI.deleteEntry(id);
			fetchEntries();
		} catch (error) {
			console.error('Failed to delete entry:', error);
		}
	};

	return (
		<div className="settings-tab knowledge-tab">
			<h2>Knowledge Base Management</h2>
			
			{message && (
				<div 
					className={`notice ${message.includes('successfully') ? 'notice-success' : 'notice-error'}`}
					role="status"
					aria-live="polite"
				>
					<p>{message}</p>
				</div>
			)}

			<div className="knowledge-actions">
				<button onClick={() => setShowSettings(!showSettings)} className="button">
					{showSettings ? 'Hide Settings' : '⚙️ Knowledge Base Settings'}
				</button>
				<button onClick={() => setShowAdd(!showAdd)} className="button button-primary">
					{showAdd ? 'Cancel' : '+ Add New Entry'}
				</button>
			</div>

			{showSettings && !loadingSettings && (
				<form onSubmit={handleSaveSettings} className="knowledge-settings-form" style={{ marginBottom: '20px', padding: '20px', background: '#f5f5f5', borderRadius: '4px' }}>
					<div className="form-section">
						<h3>Chunking & Retrieval Settings</h3>
						
						<div className="form-group">
							<label htmlFor="chunk_max_tokens">Maximum Chunk Size (tokens)</label>
							<input
								type="number"
								id="chunk_max_tokens"
								value={settings.chunk_max_tokens}
								onChange={(e) => setSettings({ ...settings, chunk_max_tokens: Number(e.target.value) })}
								min="100"
								max="2000"
								aria-describedby="chunk_max_tokens_desc"
							/>
							<p className="description" id="chunk_max_tokens_desc">
								Maximum number of tokens per knowledge chunk (default: 500)
							</p>
							{errors.chunk_max_tokens && <p className="error-text">{errors.chunk_max_tokens}</p>}
						</div>

						<div className="form-group">
							<label htmlFor="chunk_overlap">Chunk Overlap (tokens)</label>
							<input
								type="number"
								id="chunk_overlap"
								value={settings.chunk_overlap}
								onChange={(e) => setSettings({ ...settings, chunk_overlap: Number(e.target.value) })}
								min="0"
								max="500"
								aria-describedby="chunk_overlap_desc"
							/>
							<p className="description" id="chunk_overlap_desc">
								Number of overlapping tokens between chunks (default: 50)
							</p>
							{errors.chunk_overlap && <p className="error-text">{errors.chunk_overlap}</p>}
						</div>

						<div className="form-group">
							<label htmlFor="similarity_threshold">Similarity Threshold</label>
							<input
								type="number"
								id="similarity_threshold"
								value={settings.similarity_threshold}
								onChange={(e) => setSettings({ ...settings, similarity_threshold: Number(e.target.value) })}
								min="0"
								max="1"
								step="0.1"
								aria-describedby="similarity_threshold_desc"
							/>
							<p className="description" id="similarity_threshold_desc">
								Minimum similarity score to include results (0-1, default: 0.7)
							</p>
							{errors.similarity_threshold && <p className="error-text">{errors.similarity_threshold}</p>}
						</div>

						<div className="form-group">
							<label htmlFor="max_results">Maximum Results</label>
							<input
								type="number"
								id="max_results"
								value={settings.max_results}
								onChange={(e) => setSettings({ ...settings, max_results: Number(e.target.value) })}
								min="1"
								max="20"
								aria-describedby="max_results_desc"
							/>
							<p className="description" id="max_results_desc">
								Maximum number of knowledge entries to return per query (default: 5)
							</p>
							{errors.max_results && <p className="error-text">{errors.max_results}</p>}
						</div>

						<div className="form-actions">
							<button type="submit" className="button button-primary" disabled={saving}>
								{saving ? 'Saving...' : 'Save Settings'}
							</button>
						</div>
					</div>
				</form>
			)}

			{showAdd && (
				<form onSubmit={handleAdd} className="knowledge-form">
					<div className="form-group">
						<label>Title</label>
						<input
							type="text"
							value={newEntry.title}
							onChange={(e) => setNewEntry({ ...newEntry, title: e.target.value })}
							required
						/>
					</div>
					<div className="form-group">
						<label>Content</label>
						<textarea
							value={newEntry.content}
							onChange={(e) => setNewEntry({ ...newEntry, content: e.target.value })}
							rows={10}
							required
						/>
					</div>
					<div className="form-group">
						<label>Scope</label>
						<select
							value={newEntry.scope}
							onChange={(e) => setNewEntry({ ...newEntry, scope: e.target.value })}
						>
							<option value="public">Public (FAQ Bot)</option>
							<option value="members">Members Only</option>
							<option value="role:administrator">Administrators</option>
						</select>
					</div>
					<button type="submit" className="button button-primary">
						Add Entry
					</button>
				</form>
			)}

			{loading ? (
				<p>Loading knowledge base...</p>
			) : (
				<table className="knowledge-table">
					<thead>
						<tr>
							<th>Title</th>
							<th>Scope</th>
							<th>Created</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						{entries.map((entry) => (
							<tr key={entry.id}>
								<td>
									<strong>{entry.title}</strong>
									<br />
									<small>{entry.content.substring(0, 100)}...</small>
								</td>
								<td>
									<span className={`scope-badge scope-${entry.scope}`}>{entry.scope}</span>
								</td>
								<td>{new Date(entry.created_at).toLocaleDateString()}</td>
								<td>
									<button
										onClick={() => handleDelete(entry.id)}
										className="button button-small"
									>
										Delete
									</button>
								</td>
							</tr>
						))}
					</tbody>
				</table>
			)}
		</div>
	);
};

export default KnowledgeTab;
