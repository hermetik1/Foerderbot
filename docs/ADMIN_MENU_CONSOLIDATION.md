# Admin Menu Consolidation

## Overview

This document describes the consolidated admin menu structure for Kraft AI Chat plugin, as implemented per issue requirements.

## Menu Structure

### WordPress Admin Sidebar

```
Kraft AI Chat (Top-level menu, position 65)
├── Dashboard (Mirrors parent, shows SPA)
└── Settings (Separate page)
```

### What Was Removed

- **Analytics** submenu (removed - now accessible via Dashboard tab)
- **Old "Settings" tab** inside Dashboard SPA (removed)

## Pages

### 1. Dashboard (SPA) - `page=kraft-ai-chat`

**URL:** `admin.php?page=kraft-ai-chat`

**Navigation Tabs:**
- Overview
- Knowledge Base
- Analytics
- Privacy
- Branding

**Features:**
- Modern React SPA with tab-based navigation
- Overview tab does NOT auto-load analytics data
- Analytics tab loads data lazily only when activated
- No "Settings" tab (moved to separate page)
- Quick action buttons for common tasks

**Help Tabs:**
- Getting Started
- Disclaimer
- FAQ

### 2. Settings Page - `page=kraft-ai-chat-settings`

**URL:** `admin.php?page=kraft-ai-chat-settings`

**Sections:**
- General Settings (⚙️)
  - Site-wide enable/disable
  - Default language
  - Cache configuration
  - Rate limiting
  
- Privacy (🔒)
  - Data retention
  - External AI services
  - User consent
  - GDPR compliance
  
- Branding (🎨)
  - Product name
  - Logo and favicon
  - Color customization
  - Footer and legal links
  
- Knowledge Defaults (📚)
  - Chunk settings
  - Similarity threshold
  - Search configuration
  
- Analytics (📊)
  - Enable/disable analytics
  - Retention period
  - IP anonymization
  - Feedback tracking

**Features:**
- Sidebar navigation for sections
- Search functionality for settings
- Comprehensive disclaimer footer
- Links to Privacy Policy and Imprint
- All settings persist via REST API

**Help Tabs:**
- Configuration
- Privacy Notes
- Support

## Technical Implementation

### PHP Backend

**File:** `includes/class-ki-kraft-core.php`

**Key Changes:**
1. Menu registration uses position 65 (not 30)
2. Only 2 submenus: Dashboard + Settings
3. Help tabs registered for both pages
4. Legacy redirects for old URLs
5. Conditional asset loading based on page
6. File existence checks before enqueuing
7. Version strings use `filemtime()` for cache busting

**Render Methods:**
- `render_dashboard_page()` - Dashboard SPA
- `render_settings_page()` - Settings page

**Help Tab Methods:**
- `add_dashboard_help_tabs()` - Dashboard help
- `add_settings_help_tabs()` - Settings help

**Redirect Method:**
- `handle_legacy_redirects()` - Handles old page slugs

### Frontend (React)

**Files:**
- `admin/index.tsx` - Entry point with conditional rendering
- `admin/app/routes/Dashboard.tsx` - Dashboard SPA
- `admin/app/routes/SettingsPage.tsx` - Settings page
- `admin/app/styles/index.css` - Styles for both pages

**Key Changes:**

#### Dashboard.tsx
- Removed `GeneralTab` import (no longer used)
- Removed "Settings" tab from navigation
- Changed Overview to static welcome/getting started content
- Analytics now loads lazily (only when tab active)
- Uses `kraftAIChatAdmin.page` for page context

#### SettingsPage.tsx
- New component with sidebar navigation
- Search functionality for sections
- Reuses existing settings tab components
- Comprehensive disclaimer footer
- Responsive layout (mobile-friendly)

#### admin/index.tsx
- Conditional rendering based on `kraftAIChatAdmin.page`
- Renders `Dashboard` for `kraft-ai-chat`
- Renders `SettingsPage` for `kraft-ai-chat-settings`

### CSS Styles

**Settings Page Specific Styles:**
- `.ki-kraft-settings-page` - Main container
- `.settings-header` - Page header
- `.settings-search` - Search input area
- `.settings-layout` - Flexbox layout (sidebar + content)
- `.settings-sidebar` - Section navigation
- `.settings-content` - Main content area
- `.settings-footer` - Disclaimer section
- Responsive breakpoint at 768px

## Legacy Redirects

The following old URLs are automatically redirected:

| Old URL | New URL | Purpose |
|---------|---------|---------|
| `page=kraft-ai-chat-analytics` | `page=kraft-ai-chat` | Analytics moved to Dashboard tab |
| `page=kac-analytics` | `page=kraft-ai-chat` | Old analytics slug |
| `page=kac-settings` | `page=kraft-ai-chat-settings` | Old settings slug |

## Security & Capabilities

**Capability Required:** `manage_options` (for both Dashboard and Settings)

**Nonce Verification:**
- REST API calls use `wp_rest` nonce
- Nonce passed via `kraftAIChatAdmin.nonce`
- Header: `X-WP-Nonce`

**Asset Loading:**
- Only loads on Kraft AI Chat pages
- Checks file existence before enqueuing
- Uses `filemtime()` for cache-busting versioning

## i18n Support

All strings are wrapped in translation functions:

**PHP:**
- `__()` for simple translations
- Text domain: `KRAFT_AI_CHAT_TEXTDOMAIN`

**JavaScript:**
- Uses `@wordpress/i18n`
- Translations set via `wp_set_script_translations()`

## Testing

**PHP Unit Tests:** `tests/test-admin-menu.php`
- ✅ Main menu registration
- ✅ Dashboard submenu registration
- ✅ Settings submenu registration
- ✅ Analytics submenu NOT registered
- ✅ Exactly 2 submenus exist
- ✅ Legacy redirects work

**Manual Testing Checklist:**
- [ ] Menu appears in sidebar at position 65
- [ ] Dashboard and Settings submenus visible
- [ ] No Analytics submenu
- [ ] Dashboard shows 5 tabs (no Settings)
- [ ] Overview tab loads without analytics
- [ ] Analytics tab loads data when clicked
- [ ] Settings page shows all sections
- [ ] Settings search works
- [ ] Settings save successfully
- [ ] Help tabs appear in both pages
- [ ] Old URLs redirect correctly
- [ ] i18n strings translate properly

## Build Process

**Build Command:** `npm run build`

**Output:**
- `assets/admin.js` - Bundled React app (Dashboard + Settings)
- `assets/admin.css` - Compiled styles

**Size:**
- admin.js: ~51KB (minified)
- admin.css: ~9KB

## Migration Notes

For users upgrading from previous versions:

1. **Bookmarks:** Old bookmarks to Analytics page will redirect to Dashboard
2. **Settings:** Old settings URLs redirect to new Settings page
3. **Data:** All existing settings are preserved
4. **Permissions:** No capability changes
5. **Layout:** Design and colors remain unchanged

## Future Enhancements

Potential improvements (out of scope for this implementation):

- [ ] Settings export/import functionality
- [ ] "Reset to defaults" button with confirmation
- [ ] Settings change history/audit log
- [ ] Real-time settings validation
- [ ] Settings preview before save
- [ ] Keyboard shortcuts for navigation
- [ ] Settings groups/presets

## References

- WordPress Admin Menu API: https://developer.wordpress.org/plugins/administration-menus/
- React Router (not used - simple tab switching)
- WordPress i18n: https://developer.wordpress.org/plugins/internationalization/
