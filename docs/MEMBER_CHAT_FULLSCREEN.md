# Fullscreen Member Chat Implementation

## Overview
This document describes the implementation of the fullscreen member chat interface for the Foerderbot plugin.

## Features Implemented

### 1. Fullscreen Layout
- **Rail Navigation** (left sidebar, 56px wide)
  - User avatar/profile icon
  - New conversation button (＋)
  - Sidebar collapse/expand toggle (❮)
  - Search button (🔎)
  - Language indicator (EN)
  - Settings/Theme toggle (⚙️)

- **Main Sidebar** (collapsible)
  - Header with branding logo and title
  - Close button (✕)
  - Two-column body layout:
    - Left: Conversations history (280px)
    - Right: Chat messages area

### 2. Conversations History
- Search input for filtering conversations
- "+ New Chat" button (green)
- List of conversations with:
  - Session title (or "Untitled" if no title)
  - Hover actions: Rename (✎) and Delete (🗑)
  - Active state highlighting
  - Click to load conversation

### 3. Chat Interface
- Message display:
  - User messages: right-aligned, blue bubble
  - Bot/Assistant messages: left-aligned, gray bubble
  - Proper spacing and typography

- **Message Actions** (on last bot message):
  - Copy to clipboard (📋)
  - Regenerate response (🔄)
  - Like (👍)
  - Dislike (👎)
  - Delete message (🗑)

### 4. Composer
- Microphone button (🎤) for voice input (placeholder)
- Textarea with auto-resize
- Send button (✈️)
- Placeholder text: "Nachricht schreiben..."

### 5. State Persistence
- Sidebar open/closed state stored in `localStorage` as `kk_member_sidebar_open`
- Theme preference stored in `localStorage` as `kk_theme`
- Restored on page load

### 6. Theme Support
- Light and dark themes
- Toggle via settings button
- CSS variables for consistent theming:
  - `--kk-bg`, `--kk-fg`, `--kk-text`, `--kk-border`
  - `--kk-primary`, `--kk-primary-contrast`
  - `--kk-surface`, `--kk-surface-2`

### 7. Login Flow
- Detects if user is logged in via `window.kraftAIChatConfig.user.loggedIn`
- Shows login prompt with CTA button if not logged in
- Login URL resolved from settings with fallback chain:
  1. `settings.accounts.profile_url_override`
  2. `settings.accounts.profile_url`
  3. `settings.accounts.account_page_id` (permalink)
  4. `/wp-login.php` (default)

### 8. REST API Endpoints

#### GET `/member/sessions`
Returns list of user's chat sessions with metadata:
```json
{
  "success": true,
  "data": [
    {
      "session_id": "sess_xxx",
      "title": "Conversation Title",
      "created_at": "2024-01-15 10:00:00",
      "updated_at": "2024-01-15 11:30:00",
      "last_message": "Last message preview"
    }
  ]
}
```

#### POST `/member/session`
Creates a new chat session:
```json
{
  "success": true,
  "data": {
    "session_id": "sess_xxx",
    "created_at": "2024-01-15 10:00:00"
  }
}
```

#### GET `/member/session/{id}/messages`
Returns messages for a specific session:
```json
{
  "success": true,
  "data": [
    {
      "role": "user",
      "content": "Message text",
      "created_at": "2024-01-15 10:00:00"
    },
    {
      "role": "assistant",
      "content": "Response text",
      "created_at": "2024-01-15 10:00:05"
    }
  ]
}
```

#### PATCH `/member/session/{id}`
Updates session title:
```json
{
  "title": "New Title"
}
```

#### DELETE `/member/session/{id}`
Deletes a session and all its messages.

### 9. Database Schema Updates
Added to `kraft_ai_chat_sessions` table:
- `title` VARCHAR(255) - Session title (nullable)
- `updated_at` DATETIME - Last update timestamp

Migration runs automatically via `kraft_ai_chat_maybe_upgrade_schema()`.

### 10. Accessibility
- ARIA roles and labels:
  - `role="application"` on fullscreen container
  - `role="navigation"` on conversations list
  - `role="log"` on chat messages
  - `aria-live="polite"` for new messages
  - `aria-label` on all buttons
- Keyboard navigation:
  - ESC key closes fullscreen
  - Enter sends messages (with Ctrl/Cmd modifier)
- Focus management

### 11. Responsive Design
- Desktop: Full two-column layout
- Mobile (≤768px):
  - Rail becomes horizontal top bar
  - Single column layout
  - History hidden by default
- Mobile (≤640px):
  - Full-width sidebar
  - Larger touch targets (44px)

## CSS Classes Reference

### Layout
- `.kk-fullscreen` - Main container (position: fixed, full viewport)
- `.kk-rail` - Left navigation rail (56px wide)
- `.kk-sidebar` - Main content area (collapsible)
- `.kk-sidebar.open` - Sidebar expanded state

### History
- `.kk-history` - Conversations list container
- `.kk-history-search` - Search input wrapper
- `.kk-history-search-input` - Search input field
- `.kk-new-chat-btn` - New chat button
- `.kk-history-list` - Conversations list
- `.kk-history-item` - Individual conversation item
- `.kk-history-item.active` - Active conversation
- `.kk-history-title` - Conversation title
- `.kk-history-actions` - Rename/delete buttons container
- `.kk-history-rename` - Rename button
- `.kk-history-delete` - Delete button

### Chat
- `.kk-chat` - Chat messages container
- `.kk-messages` - Messages wrapper
- `.kk-message` - Individual message container
- `.kk-message.user` - User message
- `.kk-message.bot` - Bot/assistant message
- `.kk-message-content` - Message text content
- `.kk-message-actions` - Action buttons container

### Actions
- `.kk-msg-copy` - Copy button
- `.kk-msg-retry` - Regenerate button
- `.kk-msg-like` - Like button
- `.kk-msg-dislike` - Dislike button
- `.kk-msg-delete` - Delete message button

### Composer
- `.kk-composer` - Message input container
- `.kk-mic-btn` - Microphone button
- `.kk-input` - Text input/textarea
- `.kk-send-btn` - Send button

### Rail Buttons
- `.kk-rail-btn` - Generic rail button
- `.kk-rail-avatar` - User avatar
- `.kk-rail-new` - New chat button
- `.kk-rail-collapse` - Sidebar toggle
- `.kk-rail-search` - Search button
- `.kk-rail-lang` - Language indicator
- `.kk-rail-settings` - Settings button

## Usage

### Shortcode
```php
[kraft_ai_chat_chatbot type="member"]
```

### Direct HTML
```html
<div class="kk-widget" data-type="member"></div>
```

The widget automatically detects:
- User login status
- Member type → renders fullscreen UI
- Loads configuration from `window.kraftAIChatConfig`

## Configuration

### JavaScript Config
```javascript
window.kraftAIChatConfig = {
  apiUrl: '/wp-json/kraft-ai-chat/v1',
  nonce: 'wp_rest_nonce',
  user: {
    loggedIn: true,
    userId: 1,
    displayName: 'User Name',
    avatarUrl: 'https://...',
    roles: ['subscriber']
  },
  branding: {
    product_name: 'Chat Assistant',
    logo_url: 'https://...',
    advisor_header_title: 'Berater-Chat',
    footer_text: 'Powered by...',
    privacy_url: '/privacy'
  },
  settings: {
    accounts: {
      profile_url_override: '/login',
      profile_url: '/account',
      account_page_id: 123
    },
    general: {}
  },
  i18n: {
    send: 'Send',
    typing: 'Typing...',
    placeholder: 'Type your message...',
    close: 'Close'
  }
};
```

## Testing
See `/tmp/test-member-chat.html` for a standalone test page with mock API responses.

## Screenshots

### Full Interface (Light Theme)
![Fullscreen Interface](https://github.com/user-attachments/assets/d8502b35-65b7-45b5-9be5-ec7fc14dffa0)

### With Loaded Conversation
![With Messages](https://github.com/user-attachments/assets/e070ab73-23ec-4d2a-9598-62f5a9047d2f)

### Sidebar Collapsed (Rail Only)
![Collapsed Sidebar](https://github.com/user-attachments/assets/223a782a-deb5-4707-baa9-5d3f0c6e592f)

### Dark Theme
![Dark Theme](https://github.com/user-attachments/assets/131cd3d5-c6c6-4360-8369-5cd3afd6a2bc)

## Future Enhancements
- Voice input implementation (mic button currently placeholder)
- Server-side feedback storage (like/dislike)
- Search functionality for conversations
- Conversation export/archive
- Typing indicators for real-time responses
- Rich text formatting in messages
- File attachments
- Message threading
