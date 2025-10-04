# Admin Settings API Documentation

This document describes the settings REST API endpoints and usage.

## Overview

The plugin provides REST API endpoints for managing settings across 5 groups:
- **General**: Site-wide plugin configuration
- **Privacy**: GDPR and data protection settings
- **Branding**: White-label customization
- **Knowledge**: Knowledge base configuration
- **Analytics**: Usage tracking settings

## Authentication

All endpoints require the `manage_options` capability (typically admin users).

## Endpoints

### Base URL
```
/wp-json/kraft_ai_chat/v1/settings/{group}
```

### Get Settings

**GET** `/wp-json/kraft_ai_chat/v1/settings/{group}`

Returns the current settings for the specified group.

**Example:**
```bash
curl -X GET \
  -H "X-WP-Nonce: YOUR_NONCE" \
  https://yoursite.com/wp-json/kraft_ai_chat/v1/settings/general
```

**Response:**
```json
{
  "site_enabled": true,
  "default_lang": "de",
  "cache_enabled": true,
  "cache_ttl": 86400,
  "rate_limit_enabled": true,
  "rate_limit_max": 60,
  "rate_limit_window": 3600
}
```

### Update Settings

**POST** `/wp-json/kraft_ai_chat/v1/settings/{group}`

Updates settings for the specified group.

**Example:**
```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -d '{"cache_enabled": false, "cache_ttl": 3600}' \
  https://yoursite.com/wp-json/kraft_ai_chat/v1/settings/general
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "site_enabled": true,
    "default_lang": "de",
    "cache_enabled": false,
    "cache_ttl": 3600,
    "rate_limit_enabled": true,
    "rate_limit_max": 60,
    "rate_limit_window": 3600
  }
}
```

**Validation Error (400):**
```json
{
  "code": "validation_failed",
  "message": "Settings validation failed.",
  "data": {
    "status": 400,
    "errors": {
      "cache_ttl": "Field cache_ttl must be at least 60."
    }
  }
}
```

**Permission Error (403):**
```json
{
  "code": "rest_forbidden",
  "message": "Sorry, you are not allowed to do that.",
  "data": {
    "status": 403
  }
}
```

## Settings Groups

### General Settings

**Option Name:** `kraft_ai_chat_general`

**Fields:**
- `site_enabled` (boolean): Enable plugin site-wide
- `default_lang` (string): Default language (de, en, fr, es, it)
- `cache_enabled` (boolean): Enable query caching
- `cache_ttl` (integer): Cache TTL in seconds (60-604800)
- `rate_limit_enabled` (boolean): Enable rate limiting
- `rate_limit_max` (integer): Max requests per window (10-1000)
- `rate_limit_window` (integer): Rate limit window in seconds (60-86400)

### Privacy Settings

**Option Name:** `kraft_ai_chat_privacy`

**Fields:**
- `retention_enabled` (boolean): Enable automatic data retention
- `retention_days` (integer): Retention period in days (0-3650)
- `external_ai_enabled` (boolean): Use external AI services
- `consent_required` (boolean): Require user consent
- `data_export_enabled` (boolean): Enable data export
- `data_erase_enabled` (boolean): Enable data erasure
- `collect_local_analytics` (boolean): Collect local analytics

### Branding Settings

**Option Name:** `kraft_ai_chat_branding`

**Fields:**
- `logo_url` (string): URL to custom logo
- `product_name` (string): Product display name
- `primary_color` (string): Primary brand color (hex format)
- `secondary_color` (string): Secondary brand color (hex format)
- `favicon_url` (string): URL to custom favicon
- `footer_text` (string): Custom footer text
- `privacy_url` (string): Privacy policy URL
- `imprint_url` (string): Imprint page URL
- `powered_by` (boolean): Show "Powered by KI Kraft"

### Knowledge Settings

**Option Name:** `kraft_ai_chat_knowledge`

**Fields:**
- `chunk_max_tokens` (integer): Max chunk size (100-2000)
- `chunk_overlap` (integer): Chunk overlap (0-500)
- `similarity_threshold` (number): Similarity threshold (0-1)
- `max_results` (integer): Max results per query (1-20)

### Analytics Settings

**Option Name:** `kraft_ai_chat_analytics`

**Fields:**
- `enabled` (boolean): Enable analytics collection
- `retention_days` (integer): Analytics retention in days (7-365)
- `anonymize_ip` (boolean): Anonymize IP addresses
- `track_feedback` (boolean): Track user feedback

## Validation

All settings are validated server-side with:
- Type checking (boolean, integer, string)
- Range validation (min/max values)
- Pattern matching (hex colors, URLs)
- Enum validation (allowed values)

Invalid values will return a 400 error with field-specific error messages.

## Security

- All endpoints require valid WordPress nonce
- Only users with `manage_options` capability can access
- All data is sanitized before saving
- URLs are validated with `esc_url_raw`
- Colors are validated with `sanitize_hex_color`

## Usage in JavaScript

```typescript
import { settingsAPI } from '@/lib/api';

// Get settings
const settings = await settingsAPI.get('general');

// Update settings
try {
  const response = await settingsAPI.update('general', {
    cache_enabled: false,
    cache_ttl: 3600
  });
  console.log('Settings saved!', response.data);
} catch (error) {
  if (error.data?.errors) {
    // Handle validation errors
    console.error('Validation errors:', error.data.errors);
  }
}
```

## Default Values

All settings have sensible defaults set on plugin activation. If an option doesn't exist, the GET endpoint will return the defaults merged with any saved values.

## Testing

PHP unit tests are available in `tests/test-settings-rest-api.php` covering:
- Permission checks
- GET requests for all groups
- POST requests with valid data
- Validation error scenarios
- Edge cases

Run tests with:
```bash
phpunit tests/test-settings-rest-api.php
```
