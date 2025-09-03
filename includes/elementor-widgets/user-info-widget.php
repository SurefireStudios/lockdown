<?php
/**
 * Elementor User Info Widget
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CAL_User_Info_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'cal_user_info';
    }
    
    public function get_title() {
        return __('User Info', 'custom-auth-lockdown');
    }
    
    public function get_icon() {
        return 'fa fa-user';
    }
    
    public function get_categories() {
        return array('custom-auth-lockdown');
    }
    
    public function get_keywords() {
        return array('user', 'info', 'profile', 'display');
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
            'field',
            array(
                'label' => __('Field to Display', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'display_name',
                'options' => array(
                    'display_name' => __('Display Name', 'custom-auth-lockdown'),
                    'username' => __('Username', 'custom-auth-lockdown'),
                    'email' => __('Email', 'custom-auth-lockdown'),
                    'first_name' => __('First Name', 'custom-auth-lockdown'),
                    'last_name' => __('Last Name', 'custom-auth-lockdown'),
                    'full_name' => __('Full Name', 'custom-auth-lockdown'),
                ),
            )
        );
        
        $this->add_control(
            'show_avatar',
            array(
                'label' => __('Show Avatar', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-auth-lockdown'),
                'label_off' => __('Hide', 'custom-auth-lockdown'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );
        
        $this->add_control(
            'avatar_size',
            array(
                'label' => __('Avatar Size', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 20,
                        'max' => 200,
                        'step' => 5,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => 50,
                ),
                'condition' => array(
                    'show_avatar' => 'yes',
                ),
            )
        );
        
        $this->add_control(
            'logged_out_text',
            array(
                'label' => __('Logged Out Text', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Please log in', 'custom-auth-lockdown'),
                'placeholder' => __('Text to show when user is not logged in', 'custom-auth-lockdown'),
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $shortcode_atts = array();
        $shortcode_atts[] = 'field="' . esc_attr($settings['field']) . '"';
        $shortcode_atts[] = 'show_avatar="' . ($settings['show_avatar'] === 'yes' ? 'true' : 'false') . '"';
        
        if ($settings['show_avatar'] === 'yes') {
            $shortcode_atts[] = 'avatar_size="' . esc_attr($settings['avatar_size']['size']) . '"';
        }
        
        if (!empty($settings['logged_out_text'])) {
            $shortcode_atts[] = 'logged_out_text="' . esc_attr($settings['logged_out_text']) . '"';
        }
        
        $shortcode = '[cal_user_info ' . implode(' ', $shortcode_atts) . ']';
        echo do_shortcode($shortcode);
    }
}
