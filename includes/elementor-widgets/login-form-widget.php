<?php
/**
 * Elementor Login Form Widget
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

class CAL_Login_Form_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'cal_login_form';
    }
    
    public function get_title() {
        return __('Login Form', 'custom-auth-lockdown');
    }
    
    public function get_icon() {
        return 'fa fa-sign-in-alt';
    }
    
    public function get_categories() {
        return array('custom-auth-lockdown');
    }
    
    public function get_keywords() {
        return array('login', 'auth', 'form', 'user');
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
            'redirect_url',
            array(
                'label' => __('Redirect URL', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'custom-auth-lockdown'),
                'description' => __('Where to redirect after successful login. Leave empty for default.', 'custom-auth-lockdown'),
            )
        );
        
        $this->add_control(
            'show_register_link',
            array(
                'label' => __('Show Register Link', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-auth-lockdown'),
                'label_off' => __('Hide', 'custom-auth-lockdown'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'show_forgot_password_link',
            array(
                'label' => __('Show Forgot Password Link', 'custom-auth-lockdown'),
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
                    '{{WRAPPER}} .cal-login-form' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'form_border',
                'label' => __('Form Border', 'custom-auth-lockdown'),
                'selector' => '{{WRAPPER}} .cal-login-form',
            )
        );
        
        $this->add_control(
            'form_border_radius',
            array(
                'label' => __('Form Border Radius', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .cal-login-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->add_control(
            'form_padding',
            array(
                'label' => __('Form Padding', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%', 'em'),
                'selectors' => array(
                    '{{WRAPPER}} .cal-login-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
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
        
        $this->add_control(
            'button_text_color',
            array(
                'label' => __('Button Text Color', 'custom-auth-lockdown'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cal-submit-btn' => 'color: {{VALUE}}',
                ),
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $shortcode_atts = array();
        
        if (!empty($settings['redirect_url']['url'])) {
            $shortcode_atts[] = 'redirect="' . esc_attr($settings['redirect_url']['url']) . '"';
        }
        
        $shortcode_atts[] = 'show_register_link="' . ($settings['show_register_link'] === 'yes' ? 'true' : 'false') . '"';
        $shortcode_atts[] = 'show_forgot_password_link="' . ($settings['show_forgot_password_link'] === 'yes' ? 'true' : 'false') . '"';
        
        $shortcode = '[cal_login_form ' . implode(' ', $shortcode_atts) . ']';
        
        echo do_shortcode($shortcode);
    }
    
    protected function _content_template() {
        ?>
        <div class="cal-login-form-container">
            <div class="cal-form cal-login-form">
                <div class="cal-form-group">
                    <label><?php _e('Username or Email', 'custom-auth-lockdown'); ?></label>
                    <input type="text" placeholder="<?php _e('Username or Email', 'custom-auth-lockdown'); ?>">
                </div>
                <div class="cal-form-group">
                    <label><?php _e('Password', 'custom-auth-lockdown'); ?></label>
                    <input type="password" placeholder="<?php _e('Password', 'custom-auth-lockdown'); ?>">
                </div>
                <div class="cal-form-group">
                    <label>
                        <input type="checkbox">
                        <?php _e('Remember Me', 'custom-auth-lockdown'); ?>
                    </label>
                </div>
                <div class="cal-form-group">
                    <button type="button" class="cal-submit-btn">
                        <?php _e('Log In', 'custom-auth-lockdown'); ?>
                    </button>
                </div>
                <div class="cal-form-links">
                    <# if (settings.show_register_link === 'yes') { #>
                        <a href="#" class="cal-register-link"><?php _e('Register', 'custom-auth-lockdown'); ?></a>
                    <# } #>
                    <# if (settings.show_forgot_password_link === 'yes') { #>
                        <a href="#" class="cal-forgot-password-link"><?php _e('Forgot Password?', 'custom-auth-lockdown'); ?></a>
                    <# } #>
                </div>
            </div>
        </div>
        <?php
    }
}
