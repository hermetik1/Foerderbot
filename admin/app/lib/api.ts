/**
 * API utilities for admin interface.
 */

declare const kraftAIChatAdmin: {
	apiUrl: string;
	nonce: string;
	branding: any;
};

export interface APIError {
	code: string;
	message: string;
	data?: {
		status: number;
		errors?: Record<string, string>;
	};
}

export interface APIResponse<T = any> {
	success: boolean;
	data?: T;
	error?: APIError;
}

/**
 * Fetch wrapper with nonce and error handling.
 */
async function apiFetch<T = any>(
	endpoint: string,
	options: RequestInit = {}
): Promise<T> {
	const url = `${kraftAIChatAdmin.apiUrl}${endpoint}`;
	
	const headers = {
		'Content-Type': 'application/json',
		'X-WP-Nonce': kraftAIChatAdmin.nonce,
		...options.headers,
	};

	try {
		const response = await fetch(url, {
			...options,
			headers,
		});

		const data = await response.json();

		if (!response.ok) {
			throw {
				code: data.code || 'unknown_error',
				message: data.message || 'An error occurred',
				data: data.data || { status: response.status },
			} as APIError;
		}

		return data;
	} catch (error: any) {
		if (error.code) {
			throw error;
		}
		throw {
			code: 'network_error',
			message: 'Network error occurred',
			data: { status: 0 },
		} as APIError;
	}
}

/**
 * Settings API.
 */
export const settingsAPI = {
	/**
	 * Get settings for a group.
	 */
	async get(group: string): Promise<any> {
		return apiFetch(`/settings/${group}`);
	},

	/**
	 * Update settings for a group.
	 */
	async update(group: string, settings: any): Promise<APIResponse> {
		return apiFetch(`/settings/${group}`, {
			method: 'POST',
			body: JSON.stringify(settings),
		});
	},
};

/**
 * Analytics API.
 */
export const analyticsAPI = {
	/**
	 * Get analytics summary.
	 */
	async getSummary(days: number = 7): Promise<any> {
		return apiFetch(`/analytics/summary?days=${days}`);
	},
};

/**
 * Knowledge API.
 */
export const knowledgeAPI = {
	/**
	 * Get knowledge entries.
	 */
	async getEntries(limit: number = 50): Promise<any> {
		return apiFetch(`/knowledge?limit=${limit}`);
	},

	/**
	 * Add knowledge entry.
	 */
	async addEntry(entry: any): Promise<any> {
		return apiFetch('/knowledge', {
			method: 'POST',
			body: JSON.stringify(entry),
		});
	},

	/**
	 * Delete knowledge entry.
	 */
	async deleteEntry(id: number): Promise<any> {
		return apiFetch(`/knowledge/${id}`, {
			method: 'DELETE',
		});
	},
};

/**
 * Branding API (legacy support).
 */
export const brandingAPI = {
	/**
	 * Update branding config.
	 */
	async update(config: any): Promise<any> {
		return apiFetch('/branding', {
			method: 'POST',
			body: JSON.stringify(config),
		});
	},
};

export default {
	settings: settingsAPI,
	analytics: analyticsAPI,
	knowledge: knowledgeAPI,
	branding: brandingAPI,
};
