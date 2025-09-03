# Custom Auth & Lockdown WordPress Plugin

A comprehensive WordPress plugin by [Surefire Studios](https://www.surefirestudios.io) that allows you to create custom login, register, and forgot password pages using page builders like Elementor, while providing powerful site lockdown functionality to control access to your content.

**Repository:** [https://github.com/SurefireStudios/lockdown](https://github.com/SurefireStudios/lockdown)

## Features

### 🔐 Custom Authentication Pages
- **Custom Login Page**: Replace wp-login.php with your own designed page
- **Custom Register Page**: Create beautiful registration forms using page builders
- **Custom Forgot Password Page**: Design password reset pages that match your brand
- **Login Redirects**: Redirect users to specific pages after login (global or role-based)
- **Logout Redirects**: Redirect users to specific pages after logout (global or role-based)
- **Seamless Integration**: Works with any page builder (Elementor, Beaver Builder, etc.)

### 🛡️ Site Lockdown Functionality
- **Content Protection**: Control which pages are accessible to non-logged-in users
- **Flexible Access Control**: Select specific pages that should remain public
- **Custom Redirect**: Choose where to redirect unauthorized users
- **Lockdown Messages**: Show custom messages to restricted users

### 🎨 Page Builder Integration
- **Elementor Widgets**: Pre-built widgets for all authentication forms
- **Shortcodes**: Universal shortcodes that work with any page builder
- **Responsive Design**: Mobile-friendly forms and layouts
- **Customizable Styling**: Full control over form appearance

### ⚡ Advanced Features
- **AJAX Forms**: Smooth user experience without page reloads
- **Real-time Validation**: Instant feedback on form inputs
- **Admin Bar Integration**: Quick lockdown status visibility
- **Security Features**: Nonce protection and sanitized inputs

## Installation

1. Upload the plugin files to the `/wp-content/plugins/custom-auth-lockdown` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Auth & Lockdown to configure the plugin

## Configuration

### Setting Up Custom Authentication Pages

1. **Create Your Pages**:
   - Create new pages for Login, Register, and Forgot Password
   - Design them using your preferred page builder

2. **Add Forms to Pages**:
   - **With Elementor**: Use the Custom Auth & Lockdown widgets
   - **With Shortcodes**: Add the appropriate shortcodes to your pages
   - **Other Page Builders**: Use shortcodes in text/HTML elements

3. **Configure Plugin Settings**:
   - Go to Settings > Auth & Lockdown
   - Select your custom pages in the "Custom Pages" tab
   - Optionally disable wp-login.php access

### Setting Up Site Lockdown

1. **Enable Lockdown**:
   - Go to the "Site Lockdown" tab
   - Check "Enable Site Lockdown"

2. **Select Allowed Pages**:
   - Choose which pages non-logged-in users can access
   - Your custom auth pages are automatically allowed

3. **Configure Messages**:
   - Set a custom lockdown message
   - Choose redirect behavior

### Setting Up Login Redirects

1. **Global Redirects**:
   - Go to the **Custom Pages** tab
   - Select a "Login Redirect Page" or enter a "Login Redirect URL"
   - This applies to all users unless overridden by role-based settings

2. **Role-Based Redirects**:
   - In the same tab, configure different redirects for each user role
   - Administrators might go to the dashboard, subscribers to a members area
   - Custom URLs take priority over page selections

3. **Redirect Priority**:
   - URL parameters (redirect_to) have highest priority
   - Role-based custom URLs
   - Role-based page selections
   - Global custom URL
   - Global page selection
   - Default behavior (admin dashboard for admins, home for others)

### Setting Up Logout Redirects

1. **Global Logout Redirects**:
   - In the **Custom Pages** tab, find the "Logout Redirect" settings
   - Select a "Logout Redirect Page" or enter a "Logout Redirect URL"
   - This applies to all users unless overridden by role-based settings

2. **Role-Based Logout Redirects**:
   - Configure different logout destinations for each user role
   - Premium users might go to a "Thanks for visiting" page
   - Administrators might stay on the admin area
   - Custom URLs take priority over page selections

3. **Logout Priority**:
   - URL parameters (redirect_to) have highest priority
   - Role-based custom URLs
   - Role-based page selections
   - Global custom URL
   - Global page selection
   - Default behavior (home page)

## Available Shortcodes

### Authentication Forms
```
[cal_login_form]
[cal_register_form]
[cal_forgot_password_form]
```

### User Information
```
[cal_user_info field="display_name"]
[cal_logout_link text="Sign Out"]
[cal_login_status]
```

### Shortcode Parameters

#### Login Form
```
[cal_login_form redirect="https://example.com" show_register_link="true" show_forgot_password_link="true"]
```

#### Register Form
```
[cal_register_form show_login_link="true"]
```

#### User Info
```
[cal_user_info field="display_name" show_avatar="true" avatar_size="50"]
```

**Available fields**: `display_name`, `username`, `email`, `first_name`, `last_name`, `full_name`

#### Logout Link
```
[cal_logout_link text="Sign Out" redirect="https://example.com"]
```

## Elementor Widgets

When Elementor is active, you'll find these widgets in the "Custom Auth & Lockdown" category:

- **Login Form Widget**: Complete login form with styling options
- **Register Form Widget**: User registration form
- **Forgot Password Widget**: Password reset request form
- **User Info Widget**: Display logged-in user information
- **Logout Link Widget**: Customizable logout link

Each widget includes extensive styling options and content controls.

## Hooks and Filters

### Filters

**cal_is_page_allowed**: Control page access programmatically
```php
add_filter('cal_is_page_allowed', function($is_allowed, $page_id, $post) {
    // Custom logic here
    return $is_allowed;
}, 10, 3);
```

**cal_login_redirect_url**: Control login redirect destination
```php
add_filter('cal_login_redirect_url', function($redirect_url, $user, $default_redirect) {
    // Redirect based on custom logic
    if ($user->has_cap('manage_options')) {
        return admin_url('dashboard.php');
    }
    return home_url('/members-area/');
}, 10, 3);
```

**cal_logout_redirect_url**: Control logout redirect destination
```php
add_filter('cal_logout_redirect_url', function($redirect_url, $user, $default_redirect) {
    // Redirect based on custom logic
    if ($user->has_cap('manage_options')) {
        return admin_url(); // Keep admins in admin area
    }
    return home_url('/goodbye/'); // Send others to goodbye page
}, 10, 3);
```

### Actions

**cal_after_login**: Triggered after successful login
```php
add_action('cal_after_login', function($user) {
    // Custom logic after login
});
```

**cal_after_register**: Triggered after successful registration
```php
add_action('cal_after_register', function($user_id) {
    // Custom logic after registration
});
```

## Styling and Customization

### CSS Classes

The plugin uses semantic CSS classes that you can target in your theme:

- `.cal-form`: All forms
- `.cal-login-form`: Login form specifically
- `.cal-register-form`: Register form specifically
- `.cal-form-group`: Form field groups
- `.cal-submit-btn`: Submit buttons
- `.cal-message`: Status messages
- `.cal-lockdown-container`: Lockdown message container

### Custom CSS Example

```css
/* Customize form appearance */
.cal-form {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 30px;
}

.cal-submit-btn {
    background: linear-gradient(45deg, #007cba, #005a87);
    border-radius: 25px;
}

/* Style lockdown page */
.cal-lockdown-container {
    background: url('your-bg-image.jpg') center/cover;
}
```

## Security Considerations

- All forms use WordPress nonces for CSRF protection
- Input data is sanitized and validated
- Password fields are properly handled
- Admin access is required for plugin settings

## Troubleshooting

### Common Issues

**Q: Login form doesn't work**
A: Make sure you've added the `[cal_login_form]` shortcode to your custom login page

**Q: Users can still access wp-login.php**
A: Enable "Disable WP Login Access" in plugin settings

**Q: Lockdown isn't working**
A: Check that "Enable Site Lockdown" is checked and you've selected allowed pages

**Q: Elementor widgets don't appear**
A: Make sure Elementor is active and check the "Custom Auth & Lockdown" widget category

### Debug Mode

To enable debug mode, add this to your wp-config.php:
```php
define('CAL_DEBUG', true);
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

### Optional
- Elementor (for Elementor widgets)
- Any page builder that supports shortcodes

## Changelog

### Version 1.0.0
- Initial release
- Custom authentication pages
- Site lockdown functionality
- Elementor integration
- Comprehensive shortcode system

## Support

For support, feature requests, or bug reports, please create an issue in the [plugin repository](https://github.com/SurefireStudios/lockdown) or contact [Surefire Studios](https://www.surefirestudios.io).

## License

This plugin is licensed under the GPL v2 or later.

---

**Note**: This plugin provides powerful site restriction capabilities. Always test thoroughly on a staging site before deploying to production, especially when enabling site lockdown features.
