<?php
/**
 * Elementor Logout Link Widget
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CAL_Logout_Link_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'cal_logout_link';
    }
    
    public function get_title() {
        return __('Logout Link', 'custom-auth-lockdown');
    }
    
    public function get_icon() {
        return 'fa fa-sign-out-alt';
    }
    
    public function get_categories() {
        return array('custom-auth-lockdown');
    }
    
    public function get_keywords() {
        return array('logout', 'signout', 'auth', 'link');
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
            'text',
            array(
                'label' => __('Link Text', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Logout', 'custom-auth-lockdown'),
                'placeholder' => __('Logout text...', 'custom-auth-lockdown'),
            )
        );
        
        $this->add_control(
            'redirect_url',
            array(
                'label' => __('Redirect URL', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'custom-auth-lockdown'),
                'description' => __('Where to redirect after logout. Leave empty for home page.', 'custom-auth-lockdown'),
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
            'text_color',
            array(
                'label' => __('Text Color', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cal-logout-link' => 'color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_control(
            'text_color_hover',
            array(
                'label' => __('Text Color Hover', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cal-logout-link:hover' => 'color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'typography',
                'label' => __('Typography', 'custom-auth-lockdown'),
                'selector' => '{{WRAPPER}} .cal-logout-link',
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $shortcode_atts = array();
        
        if (!empty($settings['text'])) {
            $shortcode_atts[] = 'text="' . esc_attr($settings['text']) . '"';
        }
        
        if (!empty($settings['redirect_url']['url'])) {
            $shortcode_atts[] = 'redirect="' . esc_attr($settings['redirect_url']['url']) . '"';
        }
        
        $shortcode = '[cal_logout_link ' . implode(' ', $shortcode_atts) . ']';
        echo do_shortcode($shortcode);
    }
}
