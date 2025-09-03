# Installation Guide - Custom Auth & Lockdown Plugin

**By:** [Surefire Studios](https://www.surefirestudios.io)  
**Repository:** [https://github.com/SurefireStudios/lockdown](https://github.com/SurefireStudios/lockdown)

## Quick Start Guide

### Step 1: Install the Plugin

1. Upload the entire `custom-auth-lockdown` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the WordPress admin Plugins menu
3. You'll see a new menu item "Auth & Lockdown" under Settings

### Step 2: Create Your Custom Pages

1. **Create Login Page**:
   - Go to Pages > Add New
   - Title: "Login" (or whatever you prefer)
   - Add the shortcode: `[cal_login_form]`
   - Publish the page

2. **Create Register Page** (optional):
   - Go to Pages > Add New
   - Title: "Register"
   - Add the shortcode: `[cal_register_form]`
   - Publish the page

3. **Create Forgot Password Page** (optional):
   - Go to Pages > Add New
   - Title: "Forgot Password"
   - Add the shortcode: `[cal_forgot_password_form]`
   - Publish the page

### Step 3: Configure the Plugin

1. Go to **Settings > Auth & Lockdown**
2. In the **Custom Pages** tab:
   - Select your Login page from the "Custom Login Page" dropdown
   - Select your Register page (if created)
   - Select your Forgot Password page (if created)
   - **Optional**: Set up login redirects:
     - Choose a page or URL where users go after logging in
     - Set different redirects for different user roles (Admin → Dashboard, Subscriber → Members Area)
   - **Optional**: Set up logout redirects:
     - Choose a page or URL where users go after logging out
     - Set different logout destinations for different user roles
   - Check "Disable WP Login Access" if you want to redirect wp-login.php to your custom page
3. Click **Save Changes**

### Step 4: Test Your Setup

1. Open an incognito/private browser window
2. Visit your website
3. Try to access a restricted page - you should be redirected to your custom login page
4. Test the login form to make sure it works

## Advanced Setup with Elementor

If you're using Elementor:

1. **Edit your Login page with Elementor**
2. **Add Custom Auth & Lockdown widgets**:
   - Search for "Login Form" in the widget panel
   - Drag it to your page
   - Customize styling as needed
3. **Repeat for Register and Forgot Password pages**

## Setting Up Site Lockdown

1. Go to **Settings > Auth & Lockdown**
2. Click the **Site Lockdown** tab
3. Check **"Enable Site Lockdown"**
4. Select which pages should be accessible to non-logged-in users:
   - Your homepage (if you want it public)
   - About page, Contact page, etc.
   - Your custom login/register pages are automatically allowed
5. Set a custom lockdown message
6. Click **Save Changes**

## Recommended Page Structure

```
📄 Home (public)
📄 About (public)
📄 Contact (public)
📄 Login (public - contains [cal_login_form])
📄 Register (public - contains [cal_register_form])
📄 Forgot Password (public - contains [cal_forgot_password_form])
📄 Dashboard (private - only for logged-in users)
📄 Profile (private - only for logged-in users)
📄 Members Area (private - only for logged-in users)
```

## Common Customizations

### Adding a Logout Link

Add this shortcode anywhere you want a logout link:
```
[cal_logout_link text="Sign Out"]
```

### Showing User Information

Display the current user's name:
```
[cal_user_info field="display_name"]
```

With avatar:
```
[cal_user_info field="display_name" show_avatar="true" avatar_size="50"]
```

### Conditional Content

Show different content for logged-in vs logged-out users:
```
[cal_login_status logged_in_content="Welcome back!" logged_out_content="Please log in to continue."]
```

## Troubleshooting Installation

### Plugin Not Appearing
- Make sure the plugin folder is named `custom-auth-lockdown`
- Check that all files are uploaded correctly
- Verify your WordPress meets the minimum requirements (WP 5.0+, PHP 7.4+)

### Forms Not Working
- Ensure you've added the correct shortcodes to your pages
- Check that JavaScript is enabled in your browser
- Verify there are no JavaScript errors in the browser console

### Lockdown Not Working
- Make sure "Enable Site Lockdown" is checked
- Verify you've selected allowed pages
- Check that you're testing with a non-logged-in user

### Styling Issues
- The plugin includes basic styling that should work with most themes
- You may need to add custom CSS to match your theme perfectly
- Check the browser developer tools for CSS conflicts

## Common Redirect Examples

### Example 1: Simple Member Dashboard
- Create a page called "Member Dashboard"
- Set it as the "Login Redirect Page"
- All users will go there after login

### Example 2: Role-Based Login Redirects
- **Administrators**: Redirect to `/wp-admin/` (WordPress dashboard)
- **Editors**: Redirect to `/wp-admin/edit.php` (Posts list)
- **Subscribers**: Redirect to `/members-area/` (Custom members page)

### Example 3: Role-Based Logout Redirects
- **Administrators**: Redirect to `/wp-admin/` (Stay in admin area)
- **Premium Members**: Redirect to `/thank-you-premium/` (Thank you page)
- **Regular Members**: Redirect to `/` (Home page)
- **Subscribers**: Redirect to `/goodbye/` (Goodbye message)

### Example 4: Membership Site Structure
```
📄 Home (public)
📄 About (public)
📄 Pricing (public)
📄 Login (public - [cal_login_form])
📄 Register (public - [cal_register_form])
📄 Member Dashboard (private - redirect destination)
📄 Premium Content (private)
📄 User Profile (private)
```

## Next Steps

Once installed, you can:
- Customize the form styling with CSS
- Create a members-only area
- Set up user roles and permissions
- Configure advanced login redirects
- Integrate with membership plugins

For detailed customization options, see the main README.md file.
