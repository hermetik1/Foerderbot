/**
 * API utilities for admin interface.
 */
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
 * Settings API.
 */
export declare const settingsAPI: {
    /**
     * Get settings for a group.
     */
    get(group: string): Promise<any>;
    /**
     * Update settings for a group.
     */
    update(group: string, settings: any): Promise<APIResponse>;
};
/**
 * Analytics API.
 */
export declare const analyticsAPI: {
    /**
     * Get analytics summary.
     */
    getSummary(days?: number): Promise<any>;
};
/**
 * Knowledge API.
 */
export declare const knowledgeAPI: {
    /**
     * Get knowledge entries.
     */
    getEntries(limit?: number): Promise<any>;
    /**
     * Add knowledge entry.
     */
    addEntry(entry: any): Promise<any>;
    /**
     * Delete knowledge entry.
     */
    deleteEntry(id: number): Promise<any>;
};
/**
 * Branding API (legacy support).
 */
export declare const brandingAPI: {
    /**
     * Update branding config.
     */
    update(config: any): Promise<any>;
};
declare const _default: {
    settings: {
        /**
         * Get settings for a group.
         */
        get(group: string): Promise<any>;
        /**
         * Update settings for a group.
         */
        update(group: string, settings: any): Promise<APIResponse>;
    };
    analytics: {
        /**
         * Get analytics summary.
         */
        getSummary(days?: number): Promise<any>;
    };
    knowledge: {
        /**
         * Get knowledge entries.
         */
        getEntries(limit?: number): Promise<any>;
        /**
         * Add knowledge entry.
         */
        addEntry(entry: any): Promise<any>;
        /**
         * Delete knowledge entry.
         */
        deleteEntry(id: number): Promise<any>;
    };
    branding: {
        /**
         * Update branding config.
         */
        update(config: any): Promise<any>;
    };
};
export default _default;
//# sourceMappingURL=api.d.ts.map