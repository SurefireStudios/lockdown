<?php
/**
 * Shortcodes for Custom Auth & Lockdown plugin
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

class CAL_Shortcodes {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('cal_options', array());
        
        // Register shortcodes
        add_shortcode('cal_login_form', array($this, 'login_form_shortcode'));
        add_shortcode('cal_register_form', array($this, 'register_form_shortcode'));
        add_shortcode('cal_forgot_password_form', array($this, 'forgot_password_form_shortcode'));
        add_shortcode('cal_logout_link', array($this, 'logout_link_shortcode'));
        add_shortcode('cal_user_info', array($this, 'user_info_shortcode'));
        add_shortcode('cal_login_status', array($this, 'login_status_shortcode'));
    }
    
    public function login_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'redirect' => '',
            'show_register_link' => 'true',
            'show_forgot_password_link' => 'true',
            'form_id' => 'cal-login-form'
        ), $atts);
        
        // If user is already logged in, show logged in message
        if (is_user_logged_in()) {
            return $this->get_already_logged_in_message();
        }
        
        $redirect_to = !empty($atts['redirect']) ? $atts['redirect'] : (isset($_GET['redirect_to']) ? $_GET['redirect_to'] : '');
        
        ob_start();
        ?>
        <div class="cal-login-form-container">
            <form id="<?php echo esc_attr($atts['form_id']); ?>" class="cal-form cal-login-form" method="post">
                <?php wp_nonce_field('cal_nonce', 'cal_nonce'); ?>
                <input type="hidden" name="action" value="cal_login">
                <?php if ($redirect_to): ?>
                    <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
                <?php endif; ?>
                
                <div class="cal-form-group">
                    <label for="cal-username"><?php _e('Username or Email', 'custom-auth-lockdown'); ?></label>
                    <input type="text" id="cal-username" name="username" required>
                </div>
                
                <div class="cal-form-group">
                    <label for="cal-password"><?php _e('Password', 'custom-auth-lockdown'); ?></label>
                    <input type="password" id="cal-password" name="password" required>
                </div>
                
                <div class="cal-form-group">
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        <?php _e('Remember Me', 'custom-auth-lockdown'); ?>
                    </label>
                </div>
                
                <div class="cal-form-group">
                    <button type="submit" class="cal-submit-btn">
                        <?php _e('Log In', 'custom-auth-lockdown'); ?>
                    </button>
                </div>
                
                <div class="cal-form-messages"></div>
                
                <div class="cal-form-links">
                    <?php if ($atts['show_register_link'] === 'true' && get_option('users_can_register')): ?>
                        <a href="<?php echo esc_url($this->get_register_url()); ?>" class="cal-register-link">
                            <?php _e('Register', 'custom-auth-lockdown'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_forgot_password_link'] === 'true'): ?>
                        <a href="<?php echo esc_url($this->get_forgot_password_url()); ?>" class="cal-forgot-password-link">
                            <?php _e('Forgot Password?', 'custom-auth-lockdown'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function register_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_login_link' => 'true',
            'form_id' => 'cal-register-form'
        ), $atts);
        
        // Check if registration is allowed
        if (!get_option('users_can_register')) {
            return '<p class="cal-error">' . __('User registration is currently not allowed.', 'custom-auth-lockdown') . '</p>';
        }
        
        // If user is already logged in, show logged in message
        if (is_user_logged_in()) {
            return $this->get_already_logged_in_message();
        }
        
        ob_start();
        ?>
        <div class="cal-register-form-container">
            <form id="<?php echo esc_attr($atts['form_id']); ?>" class="cal-form cal-register-form" method="post">
                <?php wp_nonce_field('cal_nonce', 'cal_nonce'); ?>
                <input type="hidden" name="action" value="cal_register">
                
                <div class="cal-form-group">
                    <label for="cal-reg-username"><?php _e('Username', 'custom-auth-lockdown'); ?> *</label>
                    <input type="text" id="cal-reg-username" name="username" required>
                </div>
                
                <div class="cal-form-group">
                    <label for="cal-reg-email"><?php _e('Email', 'custom-auth-lockdown'); ?> *</label>
                    <input type="email" id="cal-reg-email" name="email" required>
                </div>
                
                <div class="cal-form-group">
                    <label for="cal-reg-password"><?php _e('Password', 'custom-auth-lockdown'); ?> *</label>
                    <input type="password" id="cal-reg-password" name="password" required>
                </div>
                
                <div class="cal-form-group">
                    <label for="cal-reg-password-confirm"><?php _e('Confirm Password', 'custom-auth-lockdown'); ?> *</label>
                    <input type="password" id="cal-reg-password-confirm" name="password_confirm" required>
                </div>
                
                <div class="cal-form-group">
                    <button type="submit" class="cal-submit-btn">
                        <?php _e('Register', 'custom-auth-lockdown'); ?>
                    </button>
                </div>
                
                <div class="cal-form-messages"></div>
                
                <?php if ($atts['show_login_link'] === 'true'): ?>
                <div class="cal-form-links">
                    <a href="<?php echo esc_url($this->get_login_url()); ?>" class="cal-login-link">
                        <?php _e('Already have an account? Log in', 'custom-auth-lockdown'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function forgot_password_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_login_link' => 'true',
            'form_id' => 'cal-forgot-password-form'
        ), $atts);
        
        // If user is already logged in, show logged in message
        if (is_user_logged_in()) {
            return $this->get_already_logged_in_message();
        }
        
        // Check if this is a password reset request
        if (isset($_GET['action']) && $_GET['action'] === 'rp' && isset($_GET['key']) && isset($_GET['login'])) {
            return $this->password_reset_form($_GET['key'], $_GET['login']);
        }
        
        ob_start();
        ?>
        <div class="cal-forgot-password-form-container">
            <form id="<?php echo esc_attr($atts['form_id']); ?>" class="cal-form cal-forgot-password-form" method="post">
                <?php wp_nonce_field('cal_nonce', 'cal_nonce'); ?>
                <input type="hidden" name="action" value="cal_forgot_password">
                
                <div class="cal-form-group">
                    <label for="cal-user-login"><?php _e('Username or Email', 'custom-auth-lockdown'); ?></label>
                    <input type="text" id="cal-user-login" name="user_login" required>
                </div>
                
                <div class="cal-form-group">
                    <button type="submit" class="cal-submit-btn">
                        <?php _e('Send Reset Email', 'custom-auth-lockdown'); ?>
                    </button>
                </div>
                
                <div class="cal-form-messages"></div>
                
                <?php if ($atts['show_login_link'] === 'true'): ?>
                <div class="cal-form-links">
                    <a href="<?php echo esc_url($this->get_login_url()); ?>" class="cal-login-link">
                        <?php _e('Back to Login', 'custom-auth-lockdown'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function password_reset_form($key, $login) {
        ob_start();
        ?>
        <div class="cal-password-reset-form-container">
            <form id="cal-password-reset-form" class="cal-form cal-password-reset-form" method="post">
                <?php wp_nonce_field('cal_nonce', 'cal_nonce'); ?>
                <input type="hidden" name="action" value="cal_reset_password">
                <input type="hidden" name="key" value="<?php echo esc_attr($key); ?>">
                <input type="hidden" name="login" value="<?php echo esc_attr($login); ?>">
                
                <div class="cal-form-group">
                    <label for="cal-new-password"><?php _e('New Password', 'custom-auth-lockdown'); ?></label>
                    <input type="password" id="cal-new-password" name="password" required>
                </div>
                
                <div class="cal-form-group">
                    <label for="cal-new-password-confirm"><?php _e('Confirm New Password', 'custom-auth-lockdown'); ?></label>
                    <input type="password" id="cal-new-password-confirm" name="password_confirm" required>
                </div>
                
                <div class="cal-form-group">
                    <button type="submit" class="cal-submit-btn">
                        <?php _e('Reset Password', 'custom-auth-lockdown'); ?>
                    </button>
                </div>
                
                <div class="cal-form-messages"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function logout_link_shortcode($atts) {
        $atts = shortcode_atts(array(
            'text' => __('Logout', 'custom-auth-lockdown'),
            'redirect' => '',
            'class' => 'cal-logout-link'
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '';
        }
        
        $redirect_to = !empty($atts['redirect']) ? $atts['redirect'] : home_url();
        
        return sprintf(
            '<a href="#" class="%s" data-redirect="%s">%s</a>',
            esc_attr($atts['class']),
            esc_attr($redirect_to),
            esc_html($atts['text'])
        );
    }
    
    public function user_info_shortcode($atts) {
        $atts = shortcode_atts(array(
            'field' => 'display_name',
            'avatar_size' => '50',
            'show_avatar' => 'false',
            'logged_out_text' => __('Please log in', 'custom-auth-lockdown')
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '<span class="cal-logged-out-text">' . esc_html($atts['logged_out_text']) . '</span>';
        }
        
        $user = wp_get_current_user();
        $output = '';
        
        if ($atts['show_avatar'] === 'true') {
            $output .= get_avatar($user->ID, $atts['avatar_size'], '', '', array('class' => 'cal-user-avatar'));
        }
        
        switch ($atts['field']) {
            case 'username':
                $output .= '<span class="cal-user-username">' . esc_html($user->user_login) . '</span>';
                break;
            case 'email':
                $output .= '<span class="cal-user-email">' . esc_html($user->user_email) . '</span>';
                break;
            case 'first_name':
                $output .= '<span class="cal-user-first-name">' . esc_html($user->first_name) . '</span>';
                break;
            case 'last_name':
                $output .= '<span class="cal-user-last-name">' . esc_html($user->last_name) . '</span>';
                break;
            case 'full_name':
                $output .= '<span class="cal-user-full-name">' . esc_html(trim($user->first_name . ' ' . $user->last_name)) . '</span>';
                break;
            case 'display_name':
            default:
                $output .= '<span class="cal-user-display-name">' . esc_html($user->display_name) . '</span>';
                break;
        }
        
        return '<span class="cal-user-info">' . $output . '</span>';
    }
    
    public function login_status_shortcode($atts) {
        $atts = shortcode_atts(array(
            'logged_in_content' => '',
            'logged_out_content' => '',
            'show_default' => 'true'
        ), $atts);
        
        if (is_user_logged_in()) {
            if (!empty($atts['logged_in_content'])) {
                return do_shortcode($atts['logged_in_content']);
            } elseif ($atts['show_default'] === 'true') {
                return '<p class="cal-logged-in-status">' . __('You are logged in.', 'custom-auth-lockdown') . '</p>';
            }
        } else {
            if (!empty($atts['logged_out_content'])) {
                return do_shortcode($atts['logged_out_content']);
            } elseif ($atts['show_default'] === 'true') {
                return '<p class="cal-logged-out-status">' . __('You are not logged in.', 'custom-auth-lockdown') . '</p>';
            }
        }
        
        return '';
    }
    
    private function get_already_logged_in_message() {
        $user = wp_get_current_user();
        return sprintf(
            '<div class="cal-already-logged-in"><p>%s <strong>%s</strong>. <a href="%s" class="cal-logout-link">%s</a></p></div>',
            __('You are already logged in as', 'custom-auth-lockdown'),
            esc_html($user->display_name),
            wp_logout_url(),
            __('Logout', 'custom-auth-lockdown')
        );
    }
    
    private function get_login_url() {
        $custom_login_page = isset($this->options['custom_login_page']) ? $this->options['custom_login_page'] : '';
        return $custom_login_page ? get_permalink($custom_login_page) : wp_login_url();
    }
    
    private function get_register_url() {
        $custom_register_page = isset($this->options['custom_register_page']) ? $this->options['custom_register_page'] : '';
        return $custom_register_page ? get_permalink($custom_register_page) : wp_registration_url();
    }
    
    private function get_forgot_password_url() {
        $custom_forgot_password_page = isset($this->options['custom_forgot_password_page']) ? $this->options['custom_forgot_password_page'] : '';
        return $custom_forgot_password_page ? get_permalink($custom_forgot_password_page) : wp_lostpassword_url();
    }
}
