import React, { useState, useEffect } from 'react';

declare const kiKraftAdmin: {
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

/**
 * Knowledge base settings tab component.
 */
const KnowledgeTab: React.FC = () => {
	const [entries, setEntries] = useState<KnowledgeEntry[]>([]);
	const [loading, setLoading] = useState(true);
	const [showAdd, setShowAdd] = useState(false);
	const [newEntry, setNewEntry] = useState({ title: '', content: '', scope: 'public' });

	useEffect(() => {
		fetchEntries();
	}, []);

	const fetchEntries = async () => {
		try {
			const response = await fetch(`${kiKraftAdmin.apiUrl}/knowledge?limit=50`, {
				headers: {
					'X-WP-Nonce': kiKraftAdmin.nonce,
				},
			});
			const data = await response.json();
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
			const response = await fetch(`${kiKraftAdmin.apiUrl}/knowledge`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': kiKraftAdmin.nonce,
				},
				body: JSON.stringify(newEntry),
			});
			if (response.ok) {
				setNewEntry({ title: '', content: '', scope: 'public' });
				setShowAdd(false);
				fetchEntries();
			}
		} catch (error) {
			console.error('Failed to add entry:', error);
		}
	};

	const handleDelete = async (id: number) => {
		if (!confirm('Are you sure you want to delete this entry?')) return;
		try {
			await fetch(`${kiKraftAdmin.apiUrl}/knowledge/${id}`, {
				method: 'DELETE',
				headers: {
					'X-WP-Nonce': kiKraftAdmin.nonce,
				},
			});
			fetchEntries();
		} catch (error) {
			console.error('Failed to delete entry:', error);
		}
	};

	return (
		<div className="settings-tab knowledge-tab">
			<h2>Knowledge Base Management</h2>
			<div className="knowledge-actions">
				<button onClick={() => setShowAdd(!showAdd)} className="button button-primary">
					{showAdd ? 'Cancel' : '+ Add New Entry'}
				</button>
			</div>

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
