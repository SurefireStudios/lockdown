# User Approval Feature - Custom Auth & Lockdown Plugin

## Overview

The User Approval feature has been successfully added to the Custom Auth & Lockdown plugin. This feature allows administrators to manually approve new user registrations before users can access the site.

## Features Added

### 1. Admin Settings
- **New "User Approval" tab** in the plugin settings
- **Enable/Disable approval requirement** - Toggle whether new users need approval
- **Email notifications** - Control whether approval emails are sent
- **Customizable messages** for different approval states:
  - Pending approval message
  - Approval success message
  - Rejection message

### 2. Registration Process
- **Automatic status assignment** - New users are marked as "pending" when approval is required
- **Email notifications** - Both admin and user receive notification emails
- **No auto-login** - Users with pending status cannot log in until approved

### 3. Admin Management Interface
- **Pending users list** - Shows users awaiting approval in the settings page
- **Individual actions** - Approve/Reject buttons for each user
- **Bulk actions** - Select and approve/reject multiple users at once
- **Users list integration** - Added approval status column and filter to WordPress Users page

### 4. Login Protection
- **AJAX login blocking** - Custom login forms block unapproved users
- **WordPress login blocking** - Standard wp-login.php also blocks unapproved users
- **Custom error messages** - Users see appropriate messages based on their status

### 5. Email System
- **Admin notifications** - Alerts when new users register
- **User notifications** - Confirmation emails for registration, approval, and rejection
- **Customizable content** - Admin can modify all email messages

## How to Use

### Enabling User Approval
1. Go to **Settings > Auth & Lockdown**
2. Click the **"User Approval"** tab
3. Check **"Require Admin Approval"**
4. Customize messages and email settings as needed
5. Click **"Save Changes"**

### Managing Pending Users
#### From Plugin Settings:
1. Go to **Settings > Auth & Lockdown > User Approval**
2. View pending users in the table
3. Use individual **Approve/Reject** buttons
4. Or use **Bulk Actions** for multiple users

#### From Users Page:
1. Go to **Users > All Users**
2. Click **"Pending Approval"** filter to see only pending users
3. Use the **Approval Status** column to see user states
4. Use row actions to approve/reject individual users

### User Experience
#### When Approval is Required:
1. User registers normally through your custom registration form
2. User receives confirmation email that account is pending approval
3. User cannot log in until approved
4. Admin receives notification of new registration
5. Once approved, user receives approval email and can log in

#### When Approval is Disabled:
- Registration works as before (auto-login after registration)
- All new users are automatically marked as "approved"

## Technical Implementation

### Database Changes
- **User meta field**: `cal_approval_status` with values:
  - `pending` - Awaiting approval
  - `approved` - Can log in
  - `rejected` - Cannot log in

### Plugin Options Added
```php
'require_admin_approval' => false,
'approval_pending_message' => 'Your account is pending administrator approval...',
'approval_success_message' => 'Your account has been approved! You can now log in.',
'approval_rejection_message' => 'Your registration has been rejected.',
'send_approval_emails' => true
```

### Hooks and Filters
- **`wp_authenticate_user`** - Blocks unapproved users from WordPress login
- **User list filters** - Adds approval status column and filtering
- **AJAX endpoints** - For approval/rejection actions

### Security Features
- **Nonce verification** for all AJAX actions
- **Capability checks** - Only administrators can approve/reject users
- **Input sanitization** - All user inputs are properly sanitized
- **SQL injection protection** - Uses WordPress functions for database operations

## Files Modified

### Core Plugin Files
- `custom-auth-lockdown.php` - Added default options
- `includes/class-admin.php` - Added approval management interface
- `includes/class-custom-auth.php` - Added registration and login blocking
- `assets/css/admin.css` - Added styling for approval interface
- `assets/js/admin.js` - Added JavaScript for approval actions

### New Functionality
- Complete admin interface for managing user approvals
- Email notification system
- Bulk approval/rejection actions
- Integration with WordPress Users page
- Comprehensive error handling and user feedback

## Compatibility

- **WordPress Version**: 5.0+
- **PHP Version**: 7.4+
- **Existing Features**: Fully compatible with all existing plugin functionality
- **Themes**: Works with any WordPress theme
- **Page Builders**: Compatible with Elementor and other page builders

## Notes

- Existing users are automatically marked as "approved" when the feature is first enabled
- The feature can be safely disabled - users will revert to normal registration behavior
- All approval actions are logged for audit purposes
- Email notifications use WordPress's built-in `wp_mail()` function
- The feature integrates seamlessly with the existing lockdown functionality

## Support

This feature maintains full backward compatibility with existing installations and can be enabled/disabled without affecting existing users or functionality.
