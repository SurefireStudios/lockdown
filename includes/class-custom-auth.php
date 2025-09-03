<?php
/**
 * Custom authentication pages functionality
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

class CAL_Custom_Auth {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('cal_options', array());
        
        // Handle login redirects
        add_filter('login_url', array($this, 'custom_login_url'), 10, 3);
        add_filter('register_url', array($this, 'custom_register_url'));
        add_filter('lostpassword_url', array($this, 'custom_lostpassword_url'), 10, 2);
        
        // Handle wp-login.php redirects
        add_action('init', array($this, 'redirect_wp_login'));
        
        // Handle form submissions
        add_action('wp_ajax_nopriv_cal_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_cal_register', array($this, 'handle_register'));
        add_action('wp_ajax_nopriv_cal_forgot_password', array($this, 'handle_forgot_password'));
        add_action('wp_ajax_nopriv_cal_reset_password', array($this, 'handle_reset_password'));
        
        // Handle logout
        add_action('wp_ajax_cal_logout', array($this, 'handle_logout'));
        
        // Handle password reset
        add_action('wp_loaded', array($this, 'handle_password_reset_link'));
        
        // Handle standard WordPress login redirects
        add_filter('login_redirect', array($this, 'handle_wp_login_redirect'), 10, 3);
        
        // Handle logout redirects
        add_filter('logout_redirect', array($this, 'handle_wp_logout_redirect'), 10, 3);
    }
    
    public function custom_login_url($login_url, $redirect, $force_reauth) {
        $custom_login_page = isset($this->options['custom_login_page']) ? $this->options['custom_login_page'] : '';
        
        if ($custom_login_page) {
            $custom_url = get_permalink($custom_login_page);
            if ($redirect) {
                $custom_url = add_query_arg('redirect_to', urlencode($redirect), $custom_url);
            }
            if ($force_reauth) {
                $custom_url = add_query_arg('reauth', '1', $custom_url);
            }
            return $custom_url;
        }
        
        return $login_url;
    }
    
    public function custom_register_url($register_url) {
        $custom_register_page = isset($this->options['custom_register_page']) ? $this->options['custom_register_page'] : '';
        
        if ($custom_register_page) {
            return get_permalink($custom_register_page);
        }
        
        return $register_url;
    }
    
    public function custom_lostpassword_url($lostpassword_url, $redirect) {
        $custom_forgot_password_page = isset($this->options['custom_forgot_password_page']) ? $this->options['custom_forgot_password_page'] : '';
        
        if ($custom_forgot_password_page) {
            $custom_url = get_permalink($custom_forgot_password_page);
            if ($redirect) {
                $custom_url = add_query_arg('redirect_to', urlencode($redirect), $custom_url);
            }
            return $custom_url;
        }
        
        return $lostpassword_url;
    }
    
    public function redirect_wp_login() {
        // Only redirect if disable_wp_login is enabled
        if (!isset($this->options['disable_wp_login']) || !$this->options['disable_wp_login']) {
            return;
        }
        
        global $pagenow;
        
        if ($pagenow === 'wp-login.php' && !isset($_GET['action'])) {
            $custom_login_page = isset($this->options['custom_login_page']) ? $this->options['custom_login_page'] : '';
            
            if ($custom_login_page) {
                $redirect_url = get_permalink($custom_login_page);
                
                // Preserve redirect_to parameter
                if (isset($_GET['redirect_to'])) {
                    $redirect_url = add_query_arg('redirect_to', $_GET['redirect_to'], $redirect_url);
                }
                
                wp_redirect($redirect_url);
                exit;
            }
        }
    }
    
    public function handle_login() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;
        $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : '';
        
        if (empty($username) || empty($password)) {
            wp_send_json_error(array(
                'message' => __('Please enter both username and password.', 'custom-auth-lockdown')
            ));
        }
        
        $creds = array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => $remember
        );
        
        $user = wp_signon($creds, false);
        
        if (is_wp_error($user)) {
            wp_send_json_error(array(
                'message' => $user->get_error_message()
            ));
        }
        
        // Successful login - determine redirect URL
        $redirect_url = $this->get_login_redirect_url($user, $redirect_to);
        
        wp_send_json_success(array(
            'message' => __('Login successful! Redirecting...', 'custom-auth-lockdown'),
            'redirect_url' => $redirect_url
        ));
    }
    
    public function handle_register() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        if (!get_option('users_can_register')) {
            wp_send_json_error(array(
                'message' => __('User registration is currently not allowed.', 'custom-auth-lockdown')
            ));
        }
        
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        
        // Validate inputs
        if (empty($username) || empty($email) || empty($password)) {
            wp_send_json_error(array(
                'message' => __('Please fill in all required fields.', 'custom-auth-lockdown')
            ));
        }
        
        if ($password !== $password_confirm) {
            wp_send_json_error(array(
                'message' => __('Passwords do not match.', 'custom-auth-lockdown')
            ));
        }
        
        if (!is_email($email)) {
            wp_send_json_error(array(
                'message' => __('Please enter a valid email address.', 'custom-auth-lockdown')
            ));
        }
        
        // Check if username exists
        if (username_exists($username)) {
            wp_send_json_error(array(
                'message' => __('Username already exists.', 'custom-auth-lockdown')
            ));
        }
        
        // Check if email exists
        if (email_exists($email)) {
            wp_send_json_error(array(
                'message' => __('Email address already exists.', 'custom-auth-lockdown')
            ));
        }
        
        // Create user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(array(
                'message' => $user_id->get_error_message()
            ));
        }
        
        // Auto-login after registration
        $creds = array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => true
        );
        
        wp_signon($creds, false);
        
        // Get user object for redirect
        $user = get_user_by('id', $user_id);
        $redirect_url = $this->get_login_redirect_url($user, '');
        
        wp_send_json_success(array(
            'message' => __('Registration successful! Welcome!', 'custom-auth-lockdown'),
            'redirect_url' => $redirect_url
        ));
    }
    
    public function handle_forgot_password() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        $user_login = sanitize_text_field($_POST['user_login']);
        
        if (empty($user_login)) {
            wp_send_json_error(array(
                'message' => __('Please enter your username or email address.', 'custom-auth-lockdown')
            ));
        }
        
        // Check if user exists
        if (strpos($user_login, '@')) {
            $user_data = get_user_by('email', trim($user_login));
        } else {
            $user_data = get_user_by('login', trim($user_login));
        }
        
        if (!$user_data) {
            wp_send_json_error(array(
                'message' => __('Invalid username or email address.', 'custom-auth-lockdown')
            ));
        }
        
        // Generate reset key
        $key = get_password_reset_key($user_data);
        
        if (is_wp_error($key)) {
            wp_send_json_error(array(
                'message' => $key->get_error_message()
            ));
        }
        
        // Send email
        $message = $this->get_password_reset_email($user_data, $key);
        $title = sprintf(__('[%s] Password Reset'), get_option('blogname'));
        $title = apply_filters('retrieve_password_title', $title, $user_data->user_login, $user_data);
        
        if (wp_mail($user_data->user_email, wp_specialchars_decode($title), $message)) {
            wp_send_json_success(array(
                'message' => __('Password reset email sent! Please check your inbox.', 'custom-auth-lockdown')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('The email could not be sent. Please try again later.', 'custom-auth-lockdown')
            ));
        }
    }
    
    public function handle_reset_password() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        $key = sanitize_text_field($_POST['key']);
        $login = sanitize_text_field($_POST['login']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        
        if (empty($password) || empty($password_confirm)) {
            wp_send_json_error(array(
                'message' => __('Please enter and confirm your new password.', 'custom-auth-lockdown')
            ));
        }
        
        if ($password !== $password_confirm) {
            wp_send_json_error(array(
                'message' => __('Passwords do not match.', 'custom-auth-lockdown')
            ));
        }
        
        $user = check_password_reset_key($key, $login);
        
        if (is_wp_error($user)) {
            wp_send_json_error(array(
                'message' => $user->get_error_message()
            ));
        }
        
        // Reset password
        reset_password($user, $password);
        
        wp_send_json_success(array(
            'message' => __('Password reset successfully! You can now log in.', 'custom-auth-lockdown'),
            'redirect_url' => $this->custom_login_url(wp_login_url(), '', false)
        ));
    }
    
    public function handle_logout() {
        check_ajax_referer('cal_nonce', 'nonce');
        
        // Get current user before logout
        $user = wp_get_current_user();
        
        // Get logout redirect URL
        $redirect_url = $this->get_logout_redirect_url($user, '');
        
        wp_logout();
        
        wp_send_json_success(array(
            'message' => __('Logged out successfully!', 'custom-auth-lockdown'),
            'redirect_url' => $redirect_url
        ));
    }
    
    public function handle_password_reset_link() {
        if (isset($_GET['action']) && $_GET['action'] === 'rp' && isset($_GET['key']) && isset($_GET['login'])) {
            $custom_forgot_password_page = isset($this->options['custom_forgot_password_page']) ? $this->options['custom_forgot_password_page'] : '';
            
            if ($custom_forgot_password_page) {
                $redirect_url = add_query_arg(array(
                    'action' => 'rp',
                    'key' => $_GET['key'],
                    'login' => $_GET['login']
                ), get_permalink($custom_forgot_password_page));
                
                wp_redirect($redirect_url);
                exit;
            }
        }
    }
    
    public function handle_wp_login_redirect($redirect_to, $request, $user) {
        // Only handle successful logins
        if (!isset($user->ID)) {
            return $redirect_to;
        }
        
        // Get custom redirect URL
        $custom_redirect = $this->get_login_redirect_url($user, $redirect_to);
        
        // Return custom redirect if different from default
        if ($custom_redirect !== $redirect_to) {
            return $custom_redirect;
        }
        
        return $redirect_to;
    }
    
    public function handle_wp_logout_redirect($redirect_to, $requested_redirect_to, $user) {
        // Only handle if we have a user object
        if (!isset($user->ID)) {
            return $redirect_to;
        }
        
        // Get custom logout redirect URL
        $custom_redirect = $this->get_logout_redirect_url($user, $requested_redirect_to);
        
        // Return custom redirect if different from default
        if ($custom_redirect !== $redirect_to) {
            return $custom_redirect;
        }
        
        return $redirect_to;
    }
    
    private function get_password_reset_email($user_data, $key) {
        $custom_forgot_password_page = isset($this->options['custom_forgot_password_page']) ? $this->options['custom_forgot_password_page'] : '';
        
        if ($custom_forgot_password_page) {
            $reset_url = add_query_arg(array(
                'action' => 'rp',
                'key' => $key,
                'login' => rawurlencode($user_data->user_login)
            ), get_permalink($custom_forgot_password_page));
        } else {
            $reset_url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_data->user_login), 'login');
        }
        
        $message = __('Someone has requested a password reset for the following account:') . "\r\n\r\n";
        $message .= sprintf(__('Site Name: %s'), get_option('blogname')) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_data->user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
        $message .= $reset_url . "\r\n";
        
        return $message;
    }
    
    /**
     * Get login redirect URL based on settings and user role
     */
    public function get_login_redirect_url($user, $default_redirect = '') {
        // Check if there's a specific redirect_to parameter
        if (!empty($default_redirect)) {
            return $default_redirect;
        }
        
        // Get user roles
        $user_roles = $user->roles;
        $primary_role = !empty($user_roles) ? $user_roles[0] : 'subscriber';
        
        // Check role-based redirects first
        $role_redirects = isset($this->options['role_redirects']) ? $this->options['role_redirects'] : array();
        
        if (isset($role_redirects[$primary_role])) {
            $role_redirect = $role_redirects[$primary_role];
            
            // Custom URL takes priority
            if (!empty($role_redirect['url'])) {
                return $role_redirect['url'];
            }
            
            // Then page selection
            if (!empty($role_redirect['page'])) {
                $page_url = get_permalink($role_redirect['page']);
                if ($page_url) {
                    return $page_url;
                }
            }
        }
        
        // Check global redirect settings
        // Custom URL takes priority
        if (!empty($this->options['login_redirect_url'])) {
            return $this->options['login_redirect_url'];
        }
        
        // Then page selection
        if (!empty($this->options['login_redirect_page'])) {
            $page_url = get_permalink($this->options['login_redirect_page']);
            if ($page_url) {
                return $page_url;
            }
        }
        
        // Apply filter for custom logic
        $redirect_url = apply_filters('cal_login_redirect_url', '', $user, $default_redirect);
        if (!empty($redirect_url)) {
            return $redirect_url;
        }
        
        // Default to admin dashboard for administrators, home for others
        if (in_array('administrator', $user_roles)) {
            return admin_url();
        }
        
        return home_url();
    }
    
    /**
     * Get logout redirect URL based on settings and user role
     */
    public function get_logout_redirect_url($user, $default_redirect = '') {
        // Check if there's a specific redirect_to parameter
        if (!empty($default_redirect)) {
            return $default_redirect;
        }
        
        // Get user roles
        $user_roles = $user->roles;
        $primary_role = !empty($user_roles) ? $user_roles[0] : 'subscriber';
        
        // Check role-based logout redirects first
        $logout_role_redirects = isset($this->options['logout_role_redirects']) ? $this->options['logout_role_redirects'] : array();
        
        if (isset($logout_role_redirects[$primary_role])) {
            $role_redirect = $logout_role_redirects[$primary_role];
            
            // Custom URL takes priority
            if (!empty($role_redirect['url'])) {
                return $role_redirect['url'];
            }
            
            // Then page selection
            if (!empty($role_redirect['page'])) {
                $page_url = get_permalink($role_redirect['page']);
                if ($page_url) {
                    return $page_url;
                }
            }
        }
        
        // Check global logout redirect settings
        // Custom URL takes priority
        if (!empty($this->options['logout_redirect_url'])) {
            return $this->options['logout_redirect_url'];
        }
        
        // Then page selection
        if (!empty($this->options['logout_redirect_page'])) {
            $page_url = get_permalink($this->options['logout_redirect_page']);
            if ($page_url) {
                return $page_url;
            }
        }
        
        // Apply filter for custom logic
        $redirect_url = apply_filters('cal_logout_redirect_url', '', $user, $default_redirect);
        if (!empty($redirect_url)) {
            return $redirect_url;
        }
        
        // Default to home page
        return home_url();
    }
    
    /**
     * Get current user info for shortcodes
     */
    public function get_current_user_info() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $user = wp_get_current_user();
        return array(
            'ID' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'avatar' => get_avatar_url($user->ID)
        );
    }
}
