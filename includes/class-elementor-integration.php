<?php
/**
 * Elementor integration for Custom Auth & Lockdown plugin
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

class CAL_Elementor_Integration {
    
    public function __construct() {
        add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'add_elementor_widget_categories'));
    }
    
    public function add_elementor_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'custom-auth-lockdown',
            array(
                'title' => __('Custom Auth & Lockdown', 'custom-auth-lockdown'),
                'icon' => 'fa fa-lock',
            )
        );
    }
    
    public function register_widgets() {
        // Include widget files
        require_once CAL_PLUGIN_PATH . 'includes/elementor-widgets/login-form-widget.php';
        require_once CAL_PLUGIN_PATH . 'includes/elementor-widgets/register-form-widget.php';
        require_once CAL_PLUGIN_PATH . 'includes/elementor-widgets/forgot-password-widget.php';
        require_once CAL_PLUGIN_PATH . 'includes/elementor-widgets/user-info-widget.php';
        require_once CAL_PLUGIN_PATH . 'includes/elementor-widgets/logout-link-widget.php';
        
        // Register widgets
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \CAL_Login_Form_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \CAL_Register_Form_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \CAL_Forgot_Password_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \CAL_User_Info_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \CAL_Logout_Link_Widget());
    }
}
