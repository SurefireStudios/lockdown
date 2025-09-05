<?php
/**
 * Admin functionality for Custom Auth & Lockdown plugin
 * 
 * @package CustomAuthLockdown
 * @author Surefire Studios
 * @link https://www.surefirestudios.io
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CAL_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_cal_save_settings', array($this, 'save_settings_ajax'));
        add_action('wp_ajax_cal_approve_user', array($this, 'approve_user_ajax'));
        add_action('wp_ajax_cal_reject_user', array($this, 'reject_user_ajax'));
        add_action('wp_ajax_cal_bulk_approve_users', array($this, 'bulk_approve_users_ajax'));
        add_action('wp_ajax_cal_bulk_reject_users', array($this, 'bulk_reject_users_ajax'));
        add_action('wp_ajax_cal_migrate_existing_users', array($this, 'migrate_existing_users_ajax'));
        add_action('wp_ajax_cal_emergency_disable', array($this, 'emergency_disable_ajax'));
        
        // Add users list page filters
        add_filter('pre_get_users', array($this, 'filter_users_list'));
        add_filter('views_users', array($this, 'add_pending_users_view'));
        add_filter('manage_users_columns', array($this, 'add_approval_status_column'));
        add_action('manage_users_custom_column', array($this, 'show_approval_status_column'), 10, 3);
        add_filter('user_row_actions', array($this, 'add_user_row_actions'), 10, 2);
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Custom Auth & Lockdown', 'custom-auth-lockdown'),
            __('Auth & Lockdown', 'custom-auth-lockdown'),
            'manage_options',
            'custom-auth-lockdown',
            array($this, 'admin_page')
        );
    }
    
    public function register_settings() {
        register_setting('cal_options_group', 'cal_options', array($this, 'sanitize_options'));
    }
    
    public function admin_page() {
        $options = get_option('cal_options', array());
        $pages = get_pages();
        ?>
        <div class="wrap">
            <h1><?php _e('Custom Auth & Lockdown Settings', 'custom-auth-lockdown'); ?></h1>
            
            <div class="nav-tab-wrapper">
                <a href="#custom-pages" class="nav-tab nav-tab-active"><?php _e('Custom Pages', 'custom-auth-lockdown'); ?></a>
                <a href="#user-approval" class="nav-tab"><?php _e('User Approval', 'custom-auth-lockdown'); ?></a>
                <a href="#lockdown" class="nav-tab"><?php _e('Site Lockdown', 'custom-auth-lockdown'); ?></a>
                <a href="#advanced" class="nav-tab"><?php _e('Advanced', 'custom-auth-lockdown'); ?></a>
            </div>
            
            <form method="post" action="options.php">
                <?php settings_fields('cal_options_group'); ?>
                
                <!-- Custom Pages Tab -->
                <div id="custom-pages" class="tab-content">
                    <h2><?php _e('Custom Authentication Pages', 'custom-auth-lockdown'); ?></h2>
                    <p><?php _e('Select pages created with page builders to replace default WordPress authentication pages.', 'custom-auth-lockdown'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Custom Login Page', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <select name="cal_options[custom_login_page]" id="custom_login_page">
                                    <option value=""><?php _e('Use default WordPress login', 'custom-auth-lockdown'); ?></option>
                                    <?php foreach ($pages as $page) : ?>
                                        <option value="<?php echo $page->ID; ?>" <?php selected(isset($options['custom_login_page']) ? $options['custom_login_page'] : '', $page->ID); ?>>
                                            <?php echo esc_html($page->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e('Add [cal_login_form] shortcode to your selected page.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Custom Register Page', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <select name="cal_options[custom_register_page]" id="custom_register_page">
                                    <option value=""><?php _e('Use default WordPress registration', 'custom-auth-lockdown'); ?></option>
                                    <?php foreach ($pages as $page) : ?>
                                        <option value="<?php echo $page->ID; ?>" <?php selected(isset($options['custom_register_page']) ? $options['custom_register_page'] : '', $page->ID); ?>>
                                            <?php echo esc_html($page->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e('Add [cal_register_form] shortcode to your selected page.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Custom Forgot Password Page', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <select name="cal_options[custom_forgot_password_page]" id="custom_forgot_password_page">
                                    <option value=""><?php _e('Use default WordPress forgot password', 'custom-auth-lockdown'); ?></option>
                                    <?php foreach ($pages as $page) : ?>
                                        <option value="<?php echo $page->ID; ?>" <?php selected(isset($options['custom_forgot_password_page']) ? $options['custom_forgot_password_page'] : '', $page->ID); ?>>
                                            <?php echo esc_html($page->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e('Add [cal_forgot_password_form] shortcode to your selected page.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Login Redirect Page', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <select name="cal_options[login_redirect_page]" id="login_redirect_page">
                                    <option value=""><?php _e('Use default WordPress behavior', 'custom-auth-lockdown'); ?></option>
                                    <?php foreach ($pages as $page) : ?>
                                        <option value="<?php echo $page->ID; ?>" <?php selected(isset($options['login_redirect_page']) ? $options['login_redirect_page'] : '', $page->ID); ?>>
                                            <?php echo esc_html($page->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e('Where to redirect users after successful login. Leave empty for WordPress default behavior.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Login Redirect URL (Custom)', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <input type="url" name="cal_options[login_redirect_url]" value="<?php echo esc_url(isset($options['login_redirect_url']) ? $options['login_redirect_url'] : ''); ?>" style="width: 400px;" placeholder="https://example.com/dashboard" />
                                <p class="description"><?php _e('Or enter a custom URL to redirect to after login. This takes priority over the page selection above.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Role-Based Redirects', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <div id="role-based-redirects">
                                    <?php
                                    global $wp_roles;
                                    $roles = $wp_roles->get_names();
                                    $role_redirects = isset($options['role_redirects']) ? $options['role_redirects'] : array();
                                    
                                    foreach ($roles as $role_key => $role_name) :
                                    ?>
                                        <div style="margin-bottom: 10px;">
                                            <label style="display: inline-block; width: 120px; font-weight: 600;">
                                                <?php echo esc_html($role_name); ?>:
                                            </label>
                                            <select name="cal_options[role_redirects][<?php echo $role_key; ?>][page]" style="width: 200px;">
                                                <option value=""><?php _e('Use default redirect', 'custom-auth-lockdown'); ?></option>
                                                <?php foreach ($pages as $page) : ?>
                                                    <option value="<?php echo $page->ID; ?>" <?php selected(isset($role_redirects[$role_key]['page']) ? $role_redirects[$role_key]['page'] : '', $page->ID); ?>>
                                                        <?php echo esc_html($page->post_title); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span style="margin: 0 5px;"><?php _e('or', 'custom-auth-lockdown'); ?></span>
                                            <input type="url" name="cal_options[role_redirects][<?php echo $role_key; ?>][url]" value="<?php echo esc_url(isset($role_redirects[$role_key]['url']) ? $role_redirects[$role_key]['url'] : ''); ?>" placeholder="Custom URL" style="width: 200px;" />
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p class="description"><?php _e('Set different redirect destinations based on user roles. Custom URLs take priority over page selections.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Logout Redirect Page', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <select name="cal_options[logout_redirect_page]" id="logout_redirect_page">
                                    <option value=""><?php _e('Use default WordPress behavior', 'custom-auth-lockdown'); ?></option>
                                    <?php foreach ($pages as $page) : ?>
                                        <option value="<?php echo $page->ID; ?>" <?php selected(isset($options['logout_redirect_page']) ? $options['logout_redirect_page'] : '', $page->ID); ?>>
                                            <?php echo esc_html($page->post_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e('Where to redirect users after logout. Leave empty for WordPress default behavior.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Logout Redirect URL (Custom)', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <input type="url" name="cal_options[logout_redirect_url]" value="<?php echo esc_url(isset($options['logout_redirect_url']) ? $options['logout_redirect_url'] : ''); ?>" style="width: 400px;" placeholder="https://example.com/goodbye" />
                                <p class="description"><?php _e('Or enter a custom URL to redirect to after logout. This takes priority over the page selection above.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Role-Based Logout Redirects', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <div id="logout-role-based-redirects">
                                    <?php
                                    $logout_role_redirects = isset($options['logout_role_redirects']) ? $options['logout_role_redirects'] : array();
                                    
                                    foreach ($roles as $role_key => $role_name) :
                                    ?>
                                        <div style="margin-bottom: 10px;">
                                            <label style="display: inline-block; width: 120px; font-weight: 600;">
                                                <?php echo esc_html($role_name); ?>:
                                            </label>
                                            <select name="cal_options[logout_role_redirects][<?php echo $role_key; ?>][page]" style="width: 200px;">
                                                <option value=""><?php _e('Use default logout redirect', 'custom-auth-lockdown'); ?></option>
                                                <?php foreach ($pages as $page) : ?>
                                                    <option value="<?php echo $page->ID; ?>" <?php selected(isset($logout_role_redirects[$role_key]['page']) ? $logout_role_redirects[$role_key]['page'] : '', $page->ID); ?>>
                                                        <?php echo esc_html($page->post_title); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span style="margin: 0 5px;"><?php _e('or', 'custom-auth-lockdown'); ?></span>
                                            <input type="url" name="cal_options[logout_role_redirects][<?php echo $role_key; ?>][url]" value="<?php echo esc_url(isset($logout_role_redirects[$role_key]['url']) ? $logout_role_redirects[$role_key]['url'] : ''); ?>" placeholder="Custom URL" style="width: 200px;" />
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p class="description"><?php _e('Set different logout redirect destinations based on user roles. Custom URLs take priority over page selections.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Disable WP Login Access', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cal_options[disable_wp_login]" value="1" <?php checked(isset($options['disable_wp_login']) ? $options['disable_wp_login'] : false, 1); ?> />
                                    <?php _e('Redirect wp-login.php to custom login page', 'custom-auth-lockdown'); ?>
                                </label>
                                <p class="description"><?php _e('When enabled, users will be redirected from wp-login.php to your custom login page.', 'custom-auth-lockdown'); ?></p>
                                <?php if (isset($options['disable_wp_login']) && $options['disable_wp_login']): ?>
                                    <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">
                                        <strong><?php _e('⚠️ Troubleshooting:', 'custom-auth-lockdown'); ?></strong>
                                        <p><?php _e('If you\'re experiencing login loops, try temporarily disabling this option. Some WordPress admin functions (like the Customizer) require direct access to wp-login.php for re-authentication.', 'custom-auth-lockdown'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- User Approval Tab -->
                <div id="user-approval" class="tab-content" style="display: none;">
                    <h2><?php _e('User Registration Approval', 'custom-auth-lockdown'); ?></h2>
                    <p><?php _e('Control whether new user registrations require administrator approval before users can log in.', 'custom-auth-lockdown'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Require Admin Approval', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cal_options[require_admin_approval]" value="1" <?php checked(isset($options['require_admin_approval']) ? $options['require_admin_approval'] : false, 1); ?> />
                                    <?php _e('New users must be approved by an administrator before they can log in', 'custom-auth-lockdown'); ?>
                                </label>
                                <p class="description"><?php _e('When enabled, newly registered users will have a "pending" status and cannot log in until approved.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Send Email Notifications', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cal_options[send_approval_emails]" value="1" <?php checked(isset($options['send_approval_emails']) ? $options['send_approval_emails'] : true, 1); ?> />
                                    <?php _e('Send email notifications for approval events', 'custom-auth-lockdown'); ?>
                                </label>
                                <p class="description"><?php _e('When enabled, users and administrators will receive email notifications about registration and approval status changes.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Pending Approval Message', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <textarea name="cal_options[approval_pending_message]" rows="3" cols="50" placeholder="<?php _e('Your account is pending administrator approval. Please wait for approval before logging in.', 'custom-auth-lockdown'); ?>"><?php echo esc_textarea(isset($options['approval_pending_message']) ? $options['approval_pending_message'] : ''); ?></textarea>
                                <p class="description"><?php _e('Message shown to users when they try to log in with a pending account.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Approval Success Message', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <textarea name="cal_options[approval_success_message]" rows="3" cols="50" placeholder="<?php _e('Your account has been approved! You can now log in.', 'custom-auth-lockdown'); ?>"><?php echo esc_textarea(isset($options['approval_success_message']) ? $options['approval_success_message'] : ''); ?></textarea>
                                <p class="description"><?php _e('Message sent to users when their account is approved.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Rejection Message', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <textarea name="cal_options[approval_rejection_message]" rows="3" cols="50" placeholder="<?php _e('Your registration has been rejected.', 'custom-auth-lockdown'); ?>"><?php echo esc_textarea(isset($options['approval_rejection_message']) ? $options['approval_rejection_message'] : ''); ?></textarea>
                                <p class="description"><?php _e('Message sent to users when their registration is rejected.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Migration Tool', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <?php
                                $migration_done = get_option('cal_user_approval_migration_done');
                                $users_without_status = get_users(array(
                                    'meta_query' => array(
                                        array(
                                            'key' => 'cal_approval_status',
                                            'compare' => 'NOT EXISTS'
                                        )
                                    ),
                                    'fields' => 'ID',
                                    'number' => 1
                                ));
                                ?>
                                <?php if (!$migration_done || !empty($users_without_status)): ?>
                                    <p><?php _e('Some existing users may not have approval status set. Click the button below to set all existing users as "approved".', 'custom-auth-lockdown'); ?></p>
                                    <button type="button" class="button button-secondary cal-migrate-users">
                                        <?php _e('Migrate Existing Users', 'custom-auth-lockdown'); ?>
                                    </button>
                                    <p class="description"><?php _e('This will set all existing users without approval status to "approved". This is safe to run multiple times.', 'custom-auth-lockdown'); ?></p>
                                <?php else: ?>
                                    <p style="color: #00a32a;"><?php _e('✓ All users have been migrated and have approval status.', 'custom-auth-lockdown'); ?></p>
                                <?php endif; ?>
                                
                                <!-- Debug Information -->
                                <div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border: 1px solid #ccc;">
                                    <h4><?php _e('Debug Information', 'custom-auth-lockdown'); ?></h4>
                                    <?php
                                    $current_user = wp_get_current_user();
                                    $user_approval_status = get_user_meta($current_user->ID, 'cal_approval_status', true);
                                    $is_admin = user_can($current_user->ID, 'manage_options');
                                    ?>
                                    <p><strong><?php _e('Current User:', 'custom-auth-lockdown'); ?></strong> <?php echo esc_html($current_user->user_login); ?></p>
                                    <p><strong><?php _e('User ID:', 'custom-auth-lockdown'); ?></strong> <?php echo $current_user->ID; ?></p>
                                    <p><strong><?php _e('Is Administrator:', 'custom-auth-lockdown'); ?></strong> <?php echo $is_admin ? 'Yes' : 'No'; ?></p>
                                    <p><strong><?php _e('Approval Status:', 'custom-auth-lockdown'); ?></strong> <?php echo $user_approval_status ? $user_approval_status : 'Not Set'; ?></p>
                                    <p><strong><?php _e('Migration Done:', 'custom-auth-lockdown'); ?></strong> <?php echo $migration_done ? 'Yes' : 'No'; ?></p>
                                    <p><strong><?php _e('Disable WP Login:', 'custom-auth-lockdown'); ?></strong> <?php echo (isset($options['disable_wp_login']) && $options['disable_wp_login']) ? 'Enabled' : 'Disabled'; ?></p>
                                    <p><strong><?php _e('Require Admin Approval:', 'custom-auth-lockdown'); ?></strong> <?php echo (isset($options['require_admin_approval']) && $options['require_admin_approval']) ? 'Enabled' : 'Disabled'; ?></p>
                                    
                                    <!-- Emergency Troubleshooting -->
                                    <div style="margin-top: 15px; padding: 10px; background: #ffe6e6; border: 1px solid #ff9999; border-radius: 4px;">
                                        <h5 style="margin-top: 0; color: #cc0000;"><?php _e('🚨 Emergency Troubleshooting', 'custom-auth-lockdown'); ?></h5>
                                        <p><?php _e('If you\'re still experiencing login loops, temporarily disable the approval system:', 'custom-auth-lockdown'); ?></p>
                                        <button type="button" class="button button-secondary cal-emergency-disable">
                                            <?php _e('Temporarily Disable Approval System', 'custom-auth-lockdown'); ?>
                                        </button>
                                        <p class="description"><?php _e('This will turn off both "Require Admin Approval" and "Disable WP Login Access" to resolve login issues.', 'custom-auth-lockdown'); ?></p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Manage Pending Users', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <?php
                                // Get pending users
                                $pending_users = get_users(array(
                                    'meta_key' => 'cal_approval_status',
                                    'meta_value' => 'pending',
                                    'number' => 10
                                ));
                                ?>
                                <?php if (!empty($pending_users)): ?>
                                    <div class="cal-pending-users-list">
                                        <p><strong><?php printf(_n('%d user pending approval:', '%d users pending approval:', count($pending_users), 'custom-auth-lockdown'), count($pending_users)); ?></strong></p>
                                        
                                        <!-- Bulk Actions -->
                                        <div class="cal-bulk-approval-actions">
                                            <label for="cal-bulk-select-all">
                                                <input type="checkbox" id="cal-bulk-select-all"> <?php _e('Select All', 'custom-auth-lockdown'); ?>
                                            </label>
                                            <button type="button" class="button button-primary cal-bulk-approve" disabled>
                                                <?php _e('Bulk Approve', 'custom-auth-lockdown'); ?>
                                            </button>
                                            <button type="button" class="button cal-bulk-reject" disabled>
                                                <?php _e('Bulk Reject', 'custom-auth-lockdown'); ?>
                                            </button>
                                        </div>
                                        
                                        <table class="widefat">
                                            <thead>
                                                <tr>
                                                    <th style="width: 30px;"><?php _e('Select', 'custom-auth-lockdown'); ?></th>
                                                    <th><?php _e('Username', 'custom-auth-lockdown'); ?></th>
                                                    <th><?php _e('Email', 'custom-auth-lockdown'); ?></th>
                                                    <th><?php _e('Registration Date', 'custom-auth-lockdown'); ?></th>
                                                    <th><?php _e('Actions', 'custom-auth-lockdown'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pending_users as $user): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="cal-user-checkbox" value="<?php echo $user->ID; ?>">
                                                        </td>
                                                        <td><?php echo esc_html($user->user_login); ?></td>
                                                        <td><?php echo esc_html($user->user_email); ?></td>
                                                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($user->user_registered))); ?></td>
                                                        <td>
                                                            <button type="button" class="button button-primary cal-approve-user" data-user-id="<?php echo $user->ID; ?>">
                                                                <?php _e('Approve', 'custom-auth-lockdown'); ?>
                                                            </button>
                                                            <button type="button" class="button cal-reject-user" data-user-id="<?php echo $user->ID; ?>">
                                                                <?php _e('Reject', 'custom-auth-lockdown'); ?>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <p><a href="<?php echo admin_url('users.php?cal_filter=pending'); ?>" class="button"><?php _e('View All Pending Users', 'custom-auth-lockdown'); ?></a></p>
                                    </div>
                                <?php else: ?>
                                    <p><?php _e('No users pending approval.', 'custom-auth-lockdown'); ?></p>
                                    <p><a href="<?php echo admin_url('users.php'); ?>" class="button"><?php _e('Manage All Users', 'custom-auth-lockdown'); ?></a></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Site Lockdown Tab -->
                <div id="lockdown" class="tab-content" style="display: none;">
                    <h2><?php _e('Site Lockdown Settings', 'custom-auth-lockdown'); ?></h2>
                    <p><?php _e('Control which pages are accessible to non-logged-in users.', 'custom-auth-lockdown'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Enable Site Lockdown', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cal_options[lockdown_enabled]" value="1" <?php checked(isset($options['lockdown_enabled']) ? $options['lockdown_enabled'] : false, 1); ?> />
                                    <?php _e('Restrict site access to logged-in users only', 'custom-auth-lockdown'); ?>
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Allowed Pages for Non-Logged-In Users', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                                    <?php
                                    $allowed_pages = isset($options['allowed_pages']) ? $options['allowed_pages'] : array();
                                    foreach ($pages as $page) :
                                    ?>
                                        <label style="display: block; margin-bottom: 5px;">
                                            <input type="checkbox" name="cal_options[allowed_pages][]" value="<?php echo $page->ID; ?>" <?php checked(in_array($page->ID, $allowed_pages), true); ?> />
                                            <?php echo esc_html($page->post_title); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <p class="description"><?php _e('Select pages that non-logged-in users can access. Login, register, and forgot password pages are automatically allowed.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Lockdown Message', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <textarea name="cal_options[lockdown_message]" rows="3" cols="50"><?php echo esc_textarea(isset($options['lockdown_message']) ? $options['lockdown_message'] : ''); ?></textarea>
                                <p class="description"><?php _e('Message shown to non-logged-in users when they try to access restricted content.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Redirect URL', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <input type="url" name="cal_options[lockdown_redirect_url]" value="<?php echo esc_url(isset($options['lockdown_redirect_url']) ? $options['lockdown_redirect_url'] : ''); ?>" style="width: 400px;" />
                                <p class="description"><?php _e('Where to redirect non-logged-in users. Leave empty to show message on current page.', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Advanced Tab -->
                <div id="advanced" class="tab-content" style="display: none;">
                    <h2><?php _e('Advanced Settings', 'custom-auth-lockdown'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Available Shortcodes', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <p><strong>[cal_login_form]</strong> - <?php _e('Display login form', 'custom-auth-lockdown'); ?></p>
                                <p><strong>[cal_register_form]</strong> - <?php _e('Display registration form', 'custom-auth-lockdown'); ?></p>
                                <p><strong>[cal_forgot_password_form]</strong> - <?php _e('Display forgot password form', 'custom-auth-lockdown'); ?></p>
                                <p><strong>[cal_logout_link]</strong> - <?php _e('Display logout link for logged-in users', 'custom-auth-lockdown'); ?></p>
                                <p><strong>[cal_user_info]</strong> - <?php _e('Display user information for logged-in users', 'custom-auth-lockdown'); ?></p>
                                <p><strong>[cal_login_status]</strong> - <?php _e('Show different content based on login status', 'custom-auth-lockdown'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Login Redirect Priority', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <p><?php _e('Login redirects work in the following priority order:', 'custom-auth-lockdown'); ?></p>
                                <ol>
                                    <li><strong><?php _e('URL Parameter', 'custom-auth-lockdown'); ?></strong> - <?php _e('redirect_to parameter in login form', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Role-Based Custom URL', 'custom-auth-lockdown'); ?></strong> - <?php _e('Custom URL set for user\'s role', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Role-Based Page', 'custom-auth-lockdown'); ?></strong> - <?php _e('Page selected for user\'s role', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Global Custom URL', 'custom-auth-lockdown'); ?></strong> - <?php _e('Global custom URL setting', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Global Page', 'custom-auth-lockdown'); ?></strong> - <?php _e('Global page selection', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Filter Hook', 'custom-auth-lockdown'); ?></strong> - <?php _e('cal_login_redirect_url filter', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Default Behavior', 'custom-auth-lockdown'); ?></strong> - <?php _e('Admin dashboard for admins, home page for others', 'custom-auth-lockdown'); ?></li>
                                </ol>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Logout Redirect Priority', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <p><?php _e('Logout redirects work in the following priority order:', 'custom-auth-lockdown'); ?></p>
                                <ol>
                                    <li><strong><?php _e('URL Parameter', 'custom-auth-lockdown'); ?></strong> - <?php _e('redirect_to parameter in logout link', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Role-Based Custom URL', 'custom-auth-lockdown'); ?></strong> - <?php _e('Custom URL set for user\'s role', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Role-Based Page', 'custom-auth-lockdown'); ?></strong> - <?php _e('Page selected for user\'s role', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Global Custom URL', 'custom-auth-lockdown'); ?></strong> - <?php _e('Global custom URL setting', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Global Page', 'custom-auth-lockdown'); ?></strong> - <?php _e('Global page selection', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Filter Hook', 'custom-auth-lockdown'); ?></strong> - <?php _e('cal_logout_redirect_url filter', 'custom-auth-lockdown'); ?></li>
                                    <li><strong><?php _e('Default Behavior', 'custom-auth-lockdown'); ?></strong> - <?php _e('Home page', 'custom-auth-lockdown'); ?></li>
                                </ol>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php _e('Available Hooks', 'custom-auth-lockdown'); ?></th>
                            <td>
                                <p><strong>cal_login_redirect_url</strong> - <?php _e('Filter login redirect URL', 'custom-auth-lockdown'); ?></p>
                                <code>add_filter('cal_login_redirect_url', function($url, $user, $default) { return $custom_url; }, 10, 3);</code>
                                
                                <p><strong>cal_logout_redirect_url</strong> - <?php _e('Filter logout redirect URL', 'custom-auth-lockdown'); ?></p>
                                <code>add_filter('cal_logout_redirect_url', function($url, $user, $default) { return $custom_url; }, 10, 3);</code>
                                
                                <p><strong>cal_is_page_allowed</strong> - <?php _e('Filter page access in lockdown mode', 'custom-auth-lockdown'); ?></p>
                                <code>add_filter('cal_is_page_allowed', function($allowed, $page_id, $post) { return $allowed; }, 10, 3);</code>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.nav-tab').click(function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                $('.tab-content').hide();
                $(target).show();
            });
        });
        </script>
        <?php
    }
    
    public function sanitize_options($input) {
        $sanitized = array();
        
        // Sanitize text fields
        $text_fields = array('lockdown_message', 'lockdown_redirect_url', 'login_redirect_url', 'logout_redirect_url');
        foreach ($text_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = sanitize_text_field($input[$field]);
            }
        }
        
        // Sanitize textarea fields
        $textarea_fields = array('approval_pending_message', 'approval_success_message', 'approval_rejection_message');
        foreach ($textarea_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = sanitize_textarea_field($input[$field]);
            }
        }
        
        // Sanitize select fields
        $select_fields = array('custom_login_page', 'custom_register_page', 'custom_forgot_password_page', 'login_redirect_page', 'logout_redirect_page');
        foreach ($select_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = absint($input[$field]);
            }
        }
        
        // Sanitize role redirects
        if (isset($input['role_redirects']) && is_array($input['role_redirects'])) {
            $sanitized['role_redirects'] = array();
            foreach ($input['role_redirects'] as $role => $redirect_data) {
                $sanitized['role_redirects'][$role] = array();
                if (isset($redirect_data['page'])) {
                    $sanitized['role_redirects'][$role]['page'] = absint($redirect_data['page']);
                }
                if (isset($redirect_data['url'])) {
                    $sanitized['role_redirects'][$role]['url'] = esc_url_raw($redirect_data['url']);
                }
            }
        } else {
            $sanitized['role_redirects'] = array();
        }
        
        // Sanitize logout role redirects
        if (isset($input['logout_role_redirects']) && is_array($input['logout_role_redirects'])) {
            $sanitized['logout_role_redirects'] = array();
            foreach ($input['logout_role_redirects'] as $role => $redirect_data) {
                $sanitized['logout_role_redirects'][$role] = array();
                if (isset($redirect_data['page'])) {
                    $sanitized['logout_role_redirects'][$role]['page'] = absint($redirect_data['page']);
                }
                if (isset($redirect_data['url'])) {
                    $sanitized['logout_role_redirects'][$role]['url'] = esc_url_raw($redirect_data['url']);
                }
            }
        } else {
            $sanitized['logout_role_redirects'] = array();
        }
        
        // Sanitize checkbox fields
        $checkbox_fields = array('lockdown_enabled', 'disable_wp_login', 'require_admin_approval', 'send_approval_emails');
        foreach ($checkbox_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? 1 : 0;
        }
        
        // Sanitize array fields
        if (isset($input['allowed_pages']) && is_array($input['allowed_pages'])) {
            $sanitized['allowed_pages'] = array_map('absint', $input['allowed_pages']);
        } else {
            $sanitized['allowed_pages'] = array();
        }
        
        return $sanitized;
    }
    
    public function save_settings_ajax() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Handle AJAX save if needed
        wp_send_json_success(__('Settings saved successfully.', 'custom-auth-lockdown'));
    }
    
    public function approve_user_ajax() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions.', 'custom-auth-lockdown'));
        }
        
        $user_id = absint($_POST['user_id']);
        if (!$user_id) {
            wp_send_json_error(__('Invalid user ID.', 'custom-auth-lockdown'));
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            wp_send_json_error(__('User not found.', 'custom-auth-lockdown'));
        }
        
        // Update user approval status
        update_user_meta($user_id, 'cal_approval_status', 'approved');
        
        // Send approval email
        $options = get_option('cal_options', array());
        if (isset($options['send_approval_emails']) && $options['send_approval_emails']) {
            $this->send_approval_email($user, 'approved');
        }
        
        wp_send_json_success(__('User approved successfully.', 'custom-auth-lockdown'));
    }
    
    public function reject_user_ajax() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions.', 'custom-auth-lockdown'));
        }
        
        $user_id = absint($_POST['user_id']);
        if (!$user_id) {
            wp_send_json_error(__('Invalid user ID.', 'custom-auth-lockdown'));
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            wp_send_json_error(__('User not found.', 'custom-auth-lockdown'));
        }
        
        // Update user approval status
        update_user_meta($user_id, 'cal_approval_status', 'rejected');
        
        // Send rejection email
        $options = get_option('cal_options', array());
        if (isset($options['send_approval_emails']) && $options['send_approval_emails']) {
            $this->send_approval_email($user, 'rejected');
        }
        
        wp_send_json_success(__('User rejected successfully.', 'custom-auth-lockdown'));
    }
    
    public function filter_users_list($query) {
        if (!is_admin() || !function_exists('get_current_screen')) {
            return;
        }
        
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'users') {
            return;
        }
        
        if (isset($_GET['cal_filter']) && $_GET['cal_filter'] === 'pending') {
            $query->set('meta_key', 'cal_approval_status');
            $query->set('meta_value', 'pending');
        }
    }
    
    public function add_pending_users_view($views) {
        $pending_count = count(get_users(array(
            'meta_key' => 'cal_approval_status',
            'meta_value' => 'pending',
            'fields' => 'ID'
        )));
        
        if ($pending_count > 0) {
            $class = isset($_GET['cal_filter']) && $_GET['cal_filter'] === 'pending' ? 'current' : '';
            $views['pending'] = sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                add_query_arg('cal_filter', 'pending', admin_url('users.php')),
                $class,
                __('Pending Approval', 'custom-auth-lockdown'),
                $pending_count
            );
        }
        
        return $views;
    }
    
    public function add_approval_status_column($columns) {
        $columns['cal_approval_status'] = __('Approval Status', 'custom-auth-lockdown');
        return $columns;
    }
    
    public function show_approval_status_column($output, $column_name, $user_id) {
        if ($column_name === 'cal_approval_status') {
            $status = get_user_meta($user_id, 'cal_approval_status', true);
            
            switch ($status) {
                case 'pending':
                    $output = '<span style="color: #ff6b6b;">' . __('Pending', 'custom-auth-lockdown') . '</span>';
                    break;
                case 'approved':
                    $output = '<span style="color: #51cf66;">' . __('Approved', 'custom-auth-lockdown') . '</span>';
                    break;
                case 'rejected':
                    $output = '<span style="color: #868e96;">' . __('Rejected', 'custom-auth-lockdown') . '</span>';
                    break;
                default:
                    $output = '<span style="color: #51cf66;">' . __('Approved', 'custom-auth-lockdown') . '</span>';
                    break;
            }
        }
        
        return $output;
    }
    
    public function add_user_row_actions($actions, $user) {
        $status = get_user_meta($user->ID, 'cal_approval_status', true);
        
        if ($status === 'pending') {
            $actions['cal_approve'] = sprintf(
                '<a href="#" class="cal-approve-user" data-user-id="%d">%s</a>',
                $user->ID,
                __('Approve', 'custom-auth-lockdown')
            );
            $actions['cal_reject'] = sprintf(
                '<a href="#" class="cal-reject-user" data-user-id="%d">%s</a>',
                $user->ID,
                __('Reject', 'custom-auth-lockdown')
            );
        } elseif ($status === 'rejected') {
            $actions['cal_approve'] = sprintf(
                '<a href="#" class="cal-approve-user" data-user-id="%d">%s</a>',
                $user->ID,
                __('Approve', 'custom-auth-lockdown')
            );
        }
        
        return $actions;
    }
    
    private function send_approval_email($user, $status) {
        $options = get_option('cal_options', array());
        
        if ($status === 'approved') {
            $subject = sprintf(__('[%s] Account Approved'), get_option('blogname'));
            $message = isset($options['approval_success_message']) && !empty($options['approval_success_message']) 
                ? $options['approval_success_message'] 
                : __('Your account has been approved! You can now log in.', 'custom-auth-lockdown');
        } else {
            $subject = sprintf(__('[%s] Registration Status'), get_option('blogname'));
            $message = isset($options['approval_rejection_message']) && !empty($options['approval_rejection_message']) 
                ? $options['approval_rejection_message'] 
                : __('Your registration has been rejected.', 'custom-auth-lockdown');
        }
        
        $message .= "\n\n" . sprintf(__('Site: %s'), get_option('blogname'));
        $message .= "\n" . sprintf(__('Username: %s'), $user->user_login);
        
        if ($status === 'approved') {
            $login_url = wp_login_url();
            // Check for custom login page
            $custom_options = get_option('cal_options', array());
            if (!empty($custom_options['custom_login_page'])) {
                $login_url = get_permalink($custom_options['custom_login_page']);
            }
            $message .= "\n\n" . sprintf(__('Login here: %s'), $login_url);
        }
        
        wp_mail($user->user_email, $subject, $message);
        
        // Also notify admin
        if ($status === 'pending') {
            $admin_email = get_option('admin_email');
            $admin_subject = sprintf(__('[%s] New User Registration'), get_option('blogname'));
            $admin_message = sprintf(__('A new user has registered and is pending approval:'), get_option('blogname')) . "\n\n";
            $admin_message .= sprintf(__('Username: %s'), $user->user_login) . "\n";
            $admin_message .= sprintf(__('Email: %s'), $user->user_email) . "\n";
            $admin_message .= sprintf(__('Registration Date: %s'), date_i18n(get_option('date_format'), strtotime($user->user_registered))) . "\n\n";
            $admin_message .= sprintf(__('Manage pending users: %s'), admin_url('users.php?cal_filter=pending'));
            
            wp_mail($admin_email, $admin_subject, $admin_message);
        }
    }
    
    public function bulk_approve_users_ajax() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions.', 'custom-auth-lockdown'));
        }
        
        $user_ids = isset($_POST['user_ids']) ? array_map('absint', $_POST['user_ids']) : array();
        if (empty($user_ids)) {
            wp_send_json_error(__('No users selected.', 'custom-auth-lockdown'));
        }
        
        $approved_count = 0;
        $options = get_option('cal_options', array());
        
        foreach ($user_ids as $user_id) {
            $user = get_user_by('id', $user_id);
            if ($user) {
                // Update user approval status
                update_user_meta($user_id, 'cal_approval_status', 'approved');
                
                // Send approval email
                if (isset($options['send_approval_emails']) && $options['send_approval_emails']) {
                    $this->send_approval_email($user, 'approved');
                }
                
                $approved_count++;
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(_n('%d user approved successfully.', '%d users approved successfully.', $approved_count, 'custom-auth-lockdown'), $approved_count),
            'count' => $approved_count
        ));
    }
    
    public function bulk_reject_users_ajax() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions.', 'custom-auth-lockdown'));
        }
        
        $user_ids = isset($_POST['user_ids']) ? array_map('absint', $_POST['user_ids']) : array();
        if (empty($user_ids)) {
            wp_send_json_error(__('No users selected.', 'custom-auth-lockdown'));
        }
        
        $rejected_count = 0;
        $options = get_option('cal_options', array());
        
        foreach ($user_ids as $user_id) {
            $user = get_user_by('id', $user_id);
            if ($user) {
                // Update user approval status
                update_user_meta($user_id, 'cal_approval_status', 'rejected');
                
                // Send rejection email
                if (isset($options['send_approval_emails']) && $options['send_approval_emails']) {
                    $this->send_approval_email($user, 'rejected');
                }
                
                $rejected_count++;
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(_n('%d user rejected successfully.', '%d users rejected successfully.', $rejected_count, 'custom-auth-lockdown'), $rejected_count),
            'count' => $rejected_count
        ));
    }
    
    public function migrate_existing_users_ajax() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions.', 'custom-auth-lockdown'));
        }
        
        // Get all users without approval status
        $users = get_users(array(
            'meta_query' => array(
                array(
                    'key' => 'cal_approval_status',
                    'compare' => 'NOT EXISTS'
                )
            ),
            'fields' => 'ID'
        ));
        
        $migrated_count = 0;
        
        // Set all existing users as approved
        foreach ($users as $user_id) {
            update_user_meta($user_id, 'cal_approval_status', 'approved');
            $migrated_count++;
        }
        
        // Mark migration as complete
        update_option('cal_user_approval_migration_done', true);
        
        wp_send_json_success(array(
            'message' => sprintf(_n('%d existing user migrated to approved status.', '%d existing users migrated to approved status.', $migrated_count, 'custom-auth-lockdown'), $migrated_count),
            'count' => $migrated_count
        ));
    }
    
    public function emergency_disable_ajax() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have sufficient permissions.', 'custom-auth-lockdown'));
        }
        
        // Get current options
        $options = get_option('cal_options', array());
        
        // Disable both problematic features
        $options['require_admin_approval'] = false;
        $options['disable_wp_login'] = false;
        
        // Update options
        update_option('cal_options', $options);
        
        wp_send_json_success(array(
            'message' => __('Emergency disable complete. Both "Require Admin Approval" and "Disable WP Login Access" have been turned off. Please test your login now.', 'custom-auth-lockdown')
        ));
    }
}
