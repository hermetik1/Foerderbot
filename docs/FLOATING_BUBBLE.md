# Floating Chat Bubble Feature

## Overview

The Floating Chat Bubble is a global, automatically-injected chat interface that appears on all pages when enabled. It provides a consistent user experience across the entire website without requiring manual shortcode placement.

## Admin Configuration

### Location
Navigate to **Kraft AI Chat → Settings → General** in the WordPress admin.

### Settings

#### 1. Enable Floating Bubble
- **Type:** Toggle (checkbox)
- **Default:** Disabled
- **Description:** Activates the floating chat bubble site-wide
- **Note:** Requires "Enable Plugin Site-Wide" to be enabled first

#### 2. Default Chat Type
- **Type:** Select
- **Options:**
  - FAQ (Public) - Available to all visitors
  - Member (Logged-in only) - Requires user authentication
- **Default:** FAQ
- **Description:** Determines which chatbot opens when the bubble is clicked

#### 3. Bubble Position
- **Type:** Select
- **Options:**
  - Bottom Right (br)
  - Bottom Left (bl)
- **Default:** Bottom Right
- **Description:** Controls where the bubble appears on the page

## Frontend Behavior

### Display Conditions
The floating bubble only appears when:
1. `site_enabled` is `true` (plugin active site-wide)
2. `floating_enabled` is `true` (floating bubble enabled)

### Automatic Injection
- The bubble is automatically injected via `wp_footer` hook
- No manual shortcode placement required
- Appears consistently across all pages

### User Interaction

#### Opening/Closing
- Click the bubble (💬) to open the chat panel
- Click the close button (✕) to minimize
- Press ESC key to close
- State persists across page loads (localStorage)

#### Member Chat Login Flow
When "Member" chat type is selected and user is not logged in:
- Shows login prompt instead of chat interface
- Displays message: "Bitte melde dich an, um den Mitglieder-Chat zu nutzen."
- Provides "Jetzt einloggen" button
- Button links to account page

### Visual Design

#### Bubble
- 60px × 60px circular button
- Chat emoji (💬) icon
- Primary color background
- Shadow and hover effects
- Smooth entrance animation

#### Chat Panel
- 360px × 500px (desktop)
- Full-screen on mobile
- Positioned relative to bubble
- Slide-up animation
- Rounded corners with shadow

### Double-Mount Prevention
- System checks for existing `.kk-initialized` widgets
- Prevents duplicate bubbles if shortcode is also present
- Priority: Floating bubble takes precedence

## Technical Implementation

### PHP Backend

#### Settings Storage
```php
'kraft_ai_chat_general' => [
    'floating_enabled' => false,
    'floating_default_type' => 'faq',  // 'faq' | 'member'
    'floating_position' => 'br',       // 'br' | 'bl'
]
```

#### Footer Injection
```php
add_action('wp_footer', function() {
    $settings = get_option('kraft_ai_chat_general', []);
    $enabled = $settings['floating_enabled'] ?? false;
    $site_enabled = $settings['site_enabled'] ?? false;

    if ($enabled && $site_enabled) {
        $type = esc_attr($settings['floating_default_type'] ?? 'faq');
        $pos = esc_attr($settings['floating_position'] ?? 'br');
        echo '<div class="kk-widget kk-floating kk-pos-' . $pos . '" data-type="' . $type . '"></div>';
    }
});
```

### Frontend JavaScript

#### Initialization
```javascript
init() {
    // Prevent double-mount
    if (document.querySelector('.kk-widget.kk-initialized')) {
        return;
    }
    
    // Detect floating mode
    this.isFloating = this.container.classList.contains('kk-floating');
    this.container.classList.add('kk-initialized');
    
    this.render();
    this.restoreState();
}
```

#### State Persistence
```javascript
// Save state
localStorage.setItem('kraft_ai_chat_open', this.sidebarOpen.toString());

// Restore state
if (localStorage.getItem('kraft_ai_chat_open') === 'true') {
    this.openSidebar();
}
```

### CSS Classes

#### Base Classes
- `.kk-floating` - Root container for floating mode
- `.kk-floating-bubble` - The circular button
- `.kk-initialized` - Prevents double-mounting

#### Position Classes
- `.kk-pos-br` - Bottom right position
- `.kk-pos-bl` - Bottom left position

#### State Classes
- `.kk-sidebar.open` - Sidebar visible state

## REST API

### Schema Definition
```php
'floating_enabled' => [
    'type' => 'boolean',
    'default' => false,
    'sanitize_callback' => 'rest_sanitize_boolean',
],
'floating_default_type' => [
    'type' => 'string',
    'enum' => ['faq', 'member'],
    'default' => 'faq',
    'sanitize_callback' => 'sanitize_text_field',
],
'floating_position' => [
    'type' => 'string',
    'enum' => ['br', 'bl'],
    'default' => 'br',
    'sanitize_callback' => 'sanitize_text_field',
],
```

### Endpoints
- **GET** `/wp-json/kraft_ai_chat/v1/settings/general` - Retrieve settings
- **POST** `/wp-json/kraft_ai_chat/v1/settings/general` - Update settings

## Mobile Responsiveness

### Breakpoint: 640px

#### Mobile Adjustments
- Bubble: 56px × 56px
- Bubble position: Always bottom-right (overrides bl setting)
- Panel: Full-screen with rounded top corners
- Height: calc(100vh - 80px)

## Browser Support

- Modern browsers with ES6+ support
- CSS Grid and Flexbox required
- localStorage for state persistence
- Animation support (gracefully degrades)

## Accessibility

- ARIA labels on buttons
- Keyboard navigation (ESC to close)
- Focus management
- Screen reader announcements
- Reduced motion support

## Performance

- Lazy initialization (only when needed)
- CSS animations (GPU-accelerated)
- No external dependencies
- Minimal DOM manipulation
- Event delegation where possible

## Security

- All outputs are escaped
- Settings validated server-side
- REST API permission checks
- CSRF protection via nonce
- XSS prevention

## Testing

### Manual Testing Checklist
- [ ] Bubble appears when enabled
- [ ] Bubble doesn't appear when disabled
- [ ] Bubble respects site_enabled setting
- [ ] Position changes work (br/bl)
- [ ] Chat type switches work (faq/member)
- [ ] Login prompt shows for member chat when logged out
- [ ] State persists across page reloads
- [ ] No duplicate bubbles with shortcode present
- [ ] Mobile responsive behavior works
- [ ] ESC key closes sidebar
- [ ] Click outside closes sidebar (optional)

### Unit Tests
Located in `/tests/test-settings-rest-api.php`:
- `test_floating_settings_defaults()`
- `test_update_floating_settings()`
- `test_floating_position_validation()`
- `test_floating_type_validation()`

## Troubleshooting

### Bubble doesn't appear
1. Check "Enable Plugin Site-Wide" is ON
2. Check "Enable Floating Bubble" is ON
3. Verify no JavaScript errors in console
4. Clear browser cache
5. Check REST API is accessible

### Bubble appears twice
- System prevents this automatically via `.kk-initialized` check
- If issue persists, check for conflicting shortcodes

### State not persisting
- Check localStorage is enabled in browser
- Verify domain/path settings
- Check browser privacy mode settings

## Future Enhancements

Potential improvements (not yet implemented):
- Click-outside-to-close behavior
- Drag-to-reposition bubble
- Custom bubble icon upload
- Animation customization
- Schedule-based visibility
- Page-specific overrides
- Custom positioning (x, y coordinates)
