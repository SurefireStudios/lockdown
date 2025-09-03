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
        $checkbox_fields = array('lockdown_enabled', 'disable_wp_login');
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
}
