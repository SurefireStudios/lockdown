<?php
/**
 * Elementor Register Form Widget
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CAL_Register_Form_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'cal_register_form';
    }
    
    public function get_title() {
        return __('Register Form', 'custom-auth-lockdown');
    }
    
    public function get_icon() {
        return 'fa fa-user-plus';
    }
    
    public function get_categories() {
        return array('custom-auth-lockdown');
    }
    
    public function get_keywords() {
        return array('register', 'signup', 'auth', 'form', 'user');
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
        
        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'custom-auth-lockdown'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_control(
            'form_background_color',
            array(
                'label' => __('Form Background Color', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cal-register-form' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'form_border',
                'label' => __('Form Border', 'custom-auth-lockdown'),
                'selector' => '{{WRAPPER}} .cal-register-form',
            )
        );
        
        $this->add_control(
            'button_background_color',
            array(
                'label' => __('Button Background Color', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cal-submit-btn' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $shortcode_atts = array();
        $shortcode_atts[] = 'show_login_link="' . ($settings['show_login_link'] === 'yes' ? 'true' : 'false') . '"';
        
        $shortcode = '[cal_register_form ' . implode(' ', $shortcode_atts) . ']';
        echo do_shortcode($shortcode);
    }
}
