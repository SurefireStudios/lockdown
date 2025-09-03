<?php
/**
 * Plugin Name: Custom Auth & Lockdown
 * Plugin URI: https://github.com/SurefireStudios/lockdown
 * Description: Create custom login/register/forgot password pages with page builders and lockdown site functionality
 * Version: 1.0.0
 * Author: Surefire Studios
 * Author URI: https://www.surefirestudios.io
 * License: GPL v2 or later
 * Text Domain: custom-auth-lockdown
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CAL_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CAL_PLUGIN_VERSION', '1.0.0');

// Main plugin class
class CustomAuthLockdown {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Include required files
        $this->includes();
        
        // Initialize components
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('custom-auth-lockdown', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function includes() {
        // Include admin class
        require_once CAL_PLUGIN_PATH . 'includes/class-admin.php';
        
        // Include lockdown functionality
        require_once CAL_PLUGIN_PATH . 'includes/class-lockdown.php';
        
        // Include custom auth pages
        require_once CAL_PLUGIN_PATH . 'includes/class-custom-auth.php';
        
        // Include shortcodes
        require_once CAL_PLUGIN_PATH . 'includes/class-shortcodes.php';
        
        // Include Elementor integration if Elementor is active
        if (defined('ELEMENTOR_VERSION')) {
            require_once CAL_PLUGIN_PATH . 'includes/class-elementor-integration.php';
        }
    }
    
    public function plugins_loaded() {
        // Initialize admin interface
        if (is_admin()) {
            new CAL_Admin();
        }
        
        // Initialize lockdown functionality
        new CAL_Lockdown();
        
        // Initialize custom auth pages
        new CAL_Custom_Auth();
        
        // Initialize shortcodes
        new CAL_Shortcodes();
        
        // Initialize Elementor integration
        if (defined('ELEMENTOR_VERSION')) {
            new CAL_Elementor_Integration();
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('cal-frontend', CAL_PLUGIN_URL . 'assets/css/frontend.css', array(), CAL_PLUGIN_VERSION);
        wp_enqueue_script('cal-frontend', CAL_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), CAL_PLUGIN_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script('cal-frontend', 'cal_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cal_nonce'),
            'messages' => array(
                'login_required' => __('Please log in to access this content.', 'custom-auth-lockdown'),
                'invalid_credentials' => __('Invalid credentials.', 'custom-auth-lockdown'),
                'registration_disabled' => __('Registration is currently disabled.', 'custom-auth-lockdown')
            )
        ));
    }
    
    public function admin_enqueue_scripts() {
        wp_enqueue_style('cal-admin', CAL_PLUGIN_URL . 'assets/css/admin.css', array(), CAL_PLUGIN_VERSION);
        wp_enqueue_script('cal-admin', CAL_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CAL_PLUGIN_VERSION, true);
    }
    
    public function activate() {
        // Create default options
        $default_options = array(
            'lockdown_enabled' => false,
            'lockdown_redirect_url' => wp_login_url(),
            'allowed_pages' => array(),
            'custom_login_page' => '',
            'custom_register_page' => '',
            'custom_forgot_password_page' => '',
            'login_redirect_page' => '',
            'login_redirect_url' => '',
            'logout_redirect_page' => '',
            'logout_redirect_url' => '',
            'role_redirects' => array(),
            'logout_role_redirects' => array(),
            'disable_wp_login' => false,
            'lockdown_message' => __('Please log in to access this content.', 'custom-auth-lockdown')
        );
        
        add_option('cal_options', $default_options);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function custom_auth_lockdown() {
    return CustomAuthLockdown::get_instance();
}

// Start the plugin
custom_auth_lockdown();
