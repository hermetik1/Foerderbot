# Kraft AI Chat - Dual Chatbot Plugin

This repository contains the complete implementation of the Kraft AI Chat WordPress plugin as specified in the readme.md documentation.

## 🚀 Quick Start

### Installation

1. **Install dependencies:**
   ```bash
   npm install
   ```

2. **Build the plugin:**
   ```bash
   npm run build
   ```

3. **Activate in WordPress:**
   - Copy the entire folder to `wp-content/plugins/kraft-ai-chat/`
   - Activate via WordPress Admin

4. **Initial Setup:**
   - Go to **Kraft AI Chat → Seed Data** and click "Seed Sample Data"
   - Go to **Kraft AI Chat → Dashboard** to configure settings
   - Add the widget to a page with shortcode: `[kraft_ai_chat_chatbot type="faq"]`

### Usage

**FAQ Bot (Public):**
```
[kraft_ai_chat_chatbot type="faq"]
```

**Member Bot (Logged-in users):**
```
[kraft_ai_chat_chatbot type="member"]
```

## ✨ Features Implemented

### Backend (PHP)
- ✅ Complete database schema with tables for conversations, messages, knowledge base, and analytics
- ✅ REST API with 15+ endpoints for all features
- ✅ FAQ bot with fulltext search and analytics logging
- ✅ Member bot with role-based knowledge access and session management
- ✅ GDPR compliance (data export/erase, retention policies)
- ✅ White-label branding system
- ✅ Admin menu integration with WordPress
- ✅ Shortcode and Gutenberg block support

### Admin Interface (React)
- ✅ Dashboard with real-time analytics
- ✅ Knowledge Base management (CRUD operations)
- ✅ Analytics dashboard with charts and trends
- ✅ White-Label customization (logo, colors, branding)
- ✅ Professional UI with responsive design

### Frontend Widget
- ✅ Functional chat interface with Rail and Sidebar
- ✅ Real-time message sending/receiving
- ✅ Theme toggle (light/dark mode)
- ✅ Typing indicators
- ✅ Source attribution badges
- ✅ Session management for members
- ✅ Mobile-responsive design

## 📁 Structure

```
ki-kraft/
├── ki-kraft.php                          # Main plugin file
├── includes/                             # PHP Backend
│   ├── class-ki-kraft-core.php          # Core orchestration
│   ├── class-ki-kraft-rest.php          # REST API endpoints
│   ├── class-ki-kraft-faq.php           # FAQ bot logic
│   ├── class-ki-kraft-member.php        # Member bot logic
│   ├── class-ki-kraft-privacy.php       # GDPR compliance
│   ├── class-ki-kraft-branding.php      # White-label system
│   ├── class-ki-kraft-indexer.php       # Document indexing
│   └── class-ki-kraft-seeder.php        # Sample data seeder
├── admin/                                # React Admin Interface
│   ├── index.tsx                         # Entry point
│   └── app/
│       ├── routes/                       # Dashboard & Settings tabs
│       └── styles/                       # Admin CSS
├── frontend/                             # Chat Widget
│   ├── index.ts                          # Widget entry point
│   └── styles/                           # Widget CSS
├── assets/                               # Build Output
├── languages/                            # Translations (DE/EN)
├── tests/                                # PHPUnit tests
└── scripts/                              # Build scripts
```

## 🛠️ Development

### Build Commands

```bash
# Build everything
npm run build

# Build and package plugin
npm run build:plugin

# Watch mode for development
npm run watch

# Run tests
npm test

# Lint code
npm run lint
```

### Build Output

After running `npm run build`, the following files are generated in `assets/`:
- `admin.js` - React admin interface bundle
- `admin.css` - Admin styles
- `widget.js` - Frontend chat widget bundle
- `widget.css` - Widget styles

## 📊 Database Tables

The plugin creates 4 custom tables:

1. **wp_ki_kraft_conversations** - Chat sessions
2. **wp_ki_kraft_messages** - Chat messages
3. **wp_ki_kraft_knowledge** - FAQ knowledge base with fulltext search
4. **wp_ki_kraft_analytics** - Query statistics and trends

## 🔌 REST API Endpoints

All endpoints under `/wp-json/ki_kraft/v1/`:

**Public:**
- `POST /faq/query` - Query FAQ bot

**Members:**
- `POST /member/session` - Create session
- `GET /member/sessions` - List sessions
- `POST /member/message` - Send message
- `POST /member/handoff` - Escalate to support

**Admin:**
- `GET /analytics/summary` - Get analytics
- `GET|POST /knowledge` - Manage knowledge entries
- `GET|POST /branding` - Manage branding

## 🎨 Customization

### CSS Variables

Customize the widget appearance:

```css
:root {
	--kk-primary: #3b82f6;
	--kk-surface: #ffffff;
	--kk-border: #e5e7eb;
	--kk-text: #111827;
}
```

### White-Label

Configure via **KI Kraft → Branding**:
- Logo and product name
- Primary/secondary colors
- Footer text and legal links
- "Powered by" attribution toggle

## 📖 Documentation

- **[readme.md](readme.md)** - Full specification and features
- **[INSTALL.md](INSTALL.md)** - Detailed installation and usage guide
- **[STRUCTURE.md](STRUCTURE.md)** - Folder structure documentation
- **[CHANGELOG.md](CHANGELOG.md)** - Version history

## 🧪 Testing

Basic PHPUnit tests are included in `tests/`:

```bash
# Run PHP tests (requires WordPress test environment)
phpunit

# Run JavaScript tests
npm test
```

## 🔒 Security

- Nonce verification on all REST endpoints
- Capability checks for privileged operations
- Prepared SQL statements
- Output escaping
- Rate limiting

## 🌍 Internationalization

Supported languages:
- German (de_DE)
- English (en_US)

Add more translations in `/languages/` directory.

## 📝 License

GPL v3 - See LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 🐛 Known Issues

- Embeddings/vector search not yet implemented (uses fulltext search)
- Document upload/extraction requires additional libraries
- Advanced analytics charts use simple HTML (could use Chart.js/Recharts)

## 🗺️ Roadmap

- [ ] Implement OpenAI embeddings for semantic search
- [ ] Add document extraction (PDF/DOCX)
- [ ] Enhanced analytics with Chart.js
- [ ] Email notifications for handoffs
- [ ] Multi-language UI support
- [ ] Export/import knowledge base

## 📞 Support

For questions or issues:
- Create an issue on GitHub
- Check the [INSTALL.md](INSTALL.md) troubleshooting section

---

**Version**: 1.0.0  
**Status**: ✅ Fully Functional  
**Last Updated**: 2025-01-04

