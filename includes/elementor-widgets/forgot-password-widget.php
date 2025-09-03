<?php
/**
 * Elementor Forgot Password Widget
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CAL_Forgot_Password_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'cal_forgot_password_form';
    }
    
    public function get_title() {
        return __('Forgot Password Form', 'custom-auth-lockdown');
    }
    
    public function get_icon() {
        return 'fa fa-key';
    }
    
    public function get_categories() {
        return array('custom-auth-lockdown');
    }
    
    public function get_keywords() {
        return array('forgot', 'password', 'reset', 'auth', 'form');
    }
    
    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'custom-auth-lockdown'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );
        
        $this->add_control(
            'show_login_link',
            array(
                'label' => __('Show Login Link', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-auth-lockdown'),
                'label_off' => __('Hide', 'custom-auth-lockdown'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $shortcode_atts = array();
        $shortcode_atts[] = 'show_login_link="' . ($settings['show_login_link'] === 'yes' ? 'true' : 'false') . '"';
        
        $shortcode = '[cal_forgot_password_form ' . implode(' ', $shortcode_atts) . ']';
        echo do_shortcode($shortcode);
    }
}
