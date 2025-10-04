# Kraft AI Chat Plugin - Installation & Usage Guide

## 📋 Prerequisites

Before installing Kraft AI Chat, ensure your environment meets these requirements:

- WordPress 6.7 or higher
- PHP 8.1 or higher
- MySQL/MariaDB with InnoDB support
- Node.js 18 or 20 (for development/building)
- Modern web browser

## 🚀 Installation

### Method 1: WordPress Admin Upload (Recommended for End Users)

1. Build the plugin package:
   ```bash
   npm install
   npm run build:plugin
   ```

2. Upload `dist/kraft-ai-chat.zip` via WordPress Admin:
   - Navigate to **Plugins → Add New → Upload Plugin**
   - Select the ZIP file and click **Install Now**
   - Click **Activate Plugin**

### Method 2: Development Installation

1. Clone the repository into your WordPress plugins directory:
   ```bash
   cd /path/to/wordpress/wp-content/plugins/
   git clone https://github.com/hermetik1/Foerderbot.git kraft-ai-chat
   cd kraft-ai-chat
   ```

2. Install dependencies and build:
   ```bash
   npm install
   npm run build
   ```

3. Activate the plugin in WordPress Admin

## ⚙️ Initial Setup

### 1. Activate the Plugin

After activation, Kraft AI Chat will:
- Create necessary database tables
- Set up default capabilities for administrators
- Add the "KI Kraft" menu to your WordPress admin

### 2. Seed Sample Data (Optional)

To get started quickly with sample FAQ entries:

1. Go to **KI Kraft → Seed Data**
2. Click **"Seed Sample Data"**
3. This adds 8 sample FAQ entries in German about associations and fundraising

### 3. Configure Settings

Navigate to **KI Kraft → Dashboard** to access:

#### General Settings
- API configuration
- Cache settings
- Rate limiting

#### Knowledge Base
- Add/edit FAQ entries
- Set scope (public, members, role-based)
- Manage your knowledge database

#### White-Label Branding
- Customize logo and product name
- Set primary and secondary colors
- Configure footer text and legal links
- Toggle "Powered by" attribution

#### Privacy & GDPR
- Configure data retention policies
- Set up opt-in flows
- Manage export/erase requests

## 💬 Using the Chat Widget

### FAQ Bot (for all visitors)

Add the FAQ bot to any page or post using:

**Shortcode:**
```
[ki_kraft_chatbot type="faq"]
```

**Gutenberg Block:**
1. Add a new block
2. Search for "Kraft AI Chat Chatbot"
3. Select the block and configure the type to "FAQ"

### Member Bot (for logged-in users)

For authenticated users with enhanced features:

**Shortcode:**
```
[ki_kraft_chatbot type="member"]
```

**Gutenberg Block:**
1. Add "Kraft AI Chat Chatbot" block
2. Set type to "Member"

## 🎨 Customizing the Widget

### CSS Variables

You can customize the widget appearance using CSS variables:

```css
:root {
	--kk-primary: #3b82f6;        /* Primary color */
	--kk-primary-contrast: #ffffff; /* Text on primary */
	--kk-surface: #ffffff;         /* Background color */
	--kk-surface-2: #f8fafc;       /* Secondary background */
	--kk-border: #e5e7eb;          /* Border color */
	--kk-text: #111827;            /* Text color */
	--kk-text-muted: #6b7280;      /* Muted text */
}
```

### Theme Toggle

The widget includes a built-in light/dark theme toggle. Users can switch themes by clicking the 🌗 button in the rail.

## 📊 Analytics & Monitoring

Access analytics via **KI Kraft → Analytics**:

- **Overview**: Total queries, answered/unanswered stats, success rate
- **Top Queries**: Most frequently asked questions
- **Unanswered Queries**: Questions that need new FAQ entries
- **Trends**: Daily query volume over time

Use this data to:
- Identify gaps in your knowledge base
- Optimize popular questions
- Monitor bot performance

## 🔐 Security & Permissions

### Custom Capabilities

KI Kraft adds these capabilities:

- `kk_upload_member_docs`: Upload documents to knowledge base
- `kk_view_analytics`: Access analytics dashboard
- `kk_manage_knowledge`: Manage FAQ entries

Assign these to roles as needed in **Users → Roles**.

### Rate Limiting

The plugin includes built-in rate limiting to prevent abuse. Configure limits in the general settings.

## 🛠️ Development

### Building from Source

```bash
# Install dependencies
npm install

# Build for development (with watch)
npm run watch

# Build for production
npm run build

# Build plugin package
npm run build:plugin
```

### File Structure

```
kraft-ai-chat/
├── ki-kraft.php              # Main plugin file
├── includes/                 # PHP backend
├── admin/                    # React admin interface
├── frontend/                 # Chat widget
├── assets/                   # Build output
├── languages/                # Translations
├── tests/                    # Tests
└── scripts/                  # Build scripts
```

### API Endpoints

All REST endpoints are under `/wp-json/ki_kraft/v1/`:

**Public:**
- `POST /faq/query` - Query FAQ bot

**Members Only:**
- `POST /member/session` - Create chat session
- `GET /member/sessions` - List user sessions
- `POST /member/message` - Send message
- `POST /member/handoff` - Escalate to support

**Admin Only:**
- `GET /analytics/summary` - Analytics data
- `GET /knowledge` - List knowledge entries
- `POST /knowledge` - Add entry
- `DELETE /knowledge/{id}` - Delete entry
- `GET /branding` - Get branding config
- `POST /branding` - Update branding

## 🐛 Troubleshooting

### Widget Not Appearing

1. Check if JavaScript is loaded: View page source and search for `kraft-ai-chat-widget`
2. Check browser console for errors
3. Ensure the shortcode is correctly formatted
4. Clear WordPress and browser caches

### No Search Results

1. Verify knowledge base has entries: **KI Kraft → Knowledge Base**
2. Check if database tables exist: Look for `wp_ki_kraft_*` tables
3. Try re-activating the plugin
4. Seed sample data to test

### Build Errors

```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear build cache
rm -rf assets/*
npm run build
```

## 📝 FAQ

**Q: Can I use this plugin in multiple languages?**
A: Yes, the plugin supports i18n. Currently includes German (de_DE) and English (en_US). Add more languages in the `/languages/` directory.

**Q: Does this plugin require an API key?**
A: For basic functionality (fulltext search), no. For advanced AI features (embeddings, semantic search), you can optionally integrate external AI services.

**Q: Is it GDPR compliant?**
A: Yes, the plugin includes data export/erase functionality, retention policies, and opt-in flows as required by GDPR.

**Q: Can I white-label it completely?**
A: Yes, you can customize logo, colors, product name, and even remove "Powered by" attribution in the White-Label settings.

**Q: Does it work with caching plugins?**
A: Yes, REST API requests bypass most caching. For widget assets, ensure cache rules allow JavaScript and CSS.

## 🤝 Support

For issues, questions, or feature requests:

1. Check this documentation
2. Review the troubleshooting section
3. Create an issue on GitHub
4. Contact the development team

## 📄 License

GPL v3 - See LICENSE file for details.

---

**Version**: 1.0.0  
**Last Updated**: 2025-01-04
