<?php
/**
 * Lockdown functionality for Custom Auth & Lockdown plugin
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

class CAL_Lockdown {
    
    private $options;
    
    public function __construct() {
        $this->options = get_option('cal_options', array());
        
        // Only initialize lockdown if it's enabled
        if (isset($this->options['lockdown_enabled']) && $this->options['lockdown_enabled']) {
            add_action('template_redirect', array($this, 'check_page_access'));
            add_filter('wp_nav_menu_items', array($this, 'filter_menu_items'), 10, 2);
            add_filter('get_pages', array($this, 'filter_pages_list'));
        }
        
        // Always handle admin bar for logged-in users
        add_action('wp_before_admin_bar_render', array($this, 'add_admin_bar_items'));
    }
    
    public function check_page_access() {
        // Skip checks for admin area, AJAX, REST API, and cron
        if (is_admin() || wp_doing_ajax() || wp_doing_cron() || defined('REST_REQUEST')) {
            return;
        }
        
        // Skip if user is logged in
        if (is_user_logged_in()) {
            return;
        }
        
        global $post;
        $current_page_id = get_queried_object_id();
        
        // Get allowed pages
        $allowed_pages = isset($this->options['allowed_pages']) ? $this->options['allowed_pages'] : array();
        
        // Always allow custom auth pages
        $auth_pages = array(
            isset($this->options['custom_login_page']) ? $this->options['custom_login_page'] : '',
            isset($this->options['custom_register_page']) ? $this->options['custom_register_page'] : '',
            isset($this->options['custom_forgot_password_page']) ? $this->options['custom_forgot_password_page'] : ''
        );
        
        $allowed_pages = array_merge($allowed_pages, array_filter($auth_pages));
        
        // Check if current page is allowed
        $is_allowed = false;
        
        // Allow if it's a page in the allowed list
        if (is_page() && in_array($current_page_id, $allowed_pages)) {
            $is_allowed = true;
        }
        
        // Allow if it's the home page and home page is in allowed list
        if (is_front_page()) {
            $front_page_id = get_option('page_on_front');
            if ($front_page_id && in_array($front_page_id, $allowed_pages)) {
                $is_allowed = true;
            } elseif (!$front_page_id && in_array('home', $allowed_pages)) {
                $is_allowed = true;
            }
        }
        
        // Allow RSS feeds, robots.txt, etc.
        if (is_feed() || is_robots() || is_trackback()) {
            $is_allowed = true;
        }
        
        // Apply filter to allow custom logic
        $is_allowed = apply_filters('cal_is_page_allowed', $is_allowed, $current_page_id, $post);
        
        if (!$is_allowed) {
            $this->handle_restricted_access();
        }
    }
    
    private function handle_restricted_access() {
        $redirect_url = isset($this->options['lockdown_redirect_url']) ? $this->options['lockdown_redirect_url'] : '';
        
        if (!empty($redirect_url)) {
            // Redirect to specified URL
            wp_redirect($redirect_url);
            exit;
        } else {
            // Show lockdown message
            $this->show_lockdown_message();
        }
    }
    
    private function show_lockdown_message() {
        $message = isset($this->options['lockdown_message']) ? $this->options['lockdown_message'] : __('Please log in to access this content.', 'custom-auth-lockdown');
        $login_url = $this->get_login_url();
        
        // Set appropriate HTTP status
        status_header(401);
        nocache_headers();
        
        // Get the site's theme header
        get_header();
        
        ?>
        <div class="cal-lockdown-container">
            <div class="cal-lockdown-message">
                <h2><?php _e('Access Restricted', 'custom-auth-lockdown'); ?></h2>
                <p><?php echo wp_kses_post($message); ?></p>
                <p>
                    <a href="<?php echo esc_url($login_url); ?>" class="cal-login-link button">
                        <?php _e('Log In', 'custom-auth-lockdown'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
        
        // Get the site's theme footer
        get_footer();
        exit;
    }
    
    public function filter_menu_items($items, $args) {
        // Only filter if lockdown is enabled and user is not logged in
        if (!isset($this->options['lockdown_enabled']) || !$this->options['lockdown_enabled'] || is_user_logged_in()) {
            return $items;
        }
        
        $allowed_pages = isset($this->options['allowed_pages']) ? $this->options['allowed_pages'] : array();
        
        // Parse menu items and remove restricted ones
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $items);
        $links = $dom->getElementsByTagName('a');
        
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $page_id = url_to_postid($href);
            
            if ($page_id && !in_array($page_id, $allowed_pages)) {
                $link->parentNode->removeChild($link);
            }
        }
        
        return $dom->saveHTML();
    }
    
    public function filter_pages_list($pages) {
        // Only filter if lockdown is enabled and user is not logged in
        if (!isset($this->options['lockdown_enabled']) || !$this->options['lockdown_enabled'] || is_user_logged_in()) {
            return $pages;
        }
        
        $allowed_pages = isset($this->options['allowed_pages']) ? $this->options['allowed_pages'] : array();
        
        return array_filter($pages, function($page) use ($allowed_pages) {
            return in_array($page->ID, $allowed_pages);
        });
    }
    
    public function add_admin_bar_items() {
        if (!is_admin_bar_showing() || !current_user_can('manage_options')) {
            return;
        }
        
        global $wp_admin_bar;
        
        $wp_admin_bar->add_menu(array(
            'id' => 'cal-lockdown-status',
            'title' => $this->get_lockdown_status_text(),
            'href' => admin_url('options-general.php?page=custom-auth-lockdown'),
            'meta' => array(
                'title' => __('Custom Auth & Lockdown Status', 'custom-auth-lockdown')
            )
        ));
    }
    
    private function get_lockdown_status_text() {
        if (isset($this->options['lockdown_enabled']) && $this->options['lockdown_enabled']) {
            return '<span style="color: #ff6b6b;">🔒 ' . __('Lockdown: ON', 'custom-auth-lockdown') . '</span>';
        } else {
            return '<span style="color: #51cf66;">🔓 ' . __('Lockdown: OFF', 'custom-auth-lockdown') . '</span>';
        }
    }
    
    private function get_login_url() {
        $custom_login_page = isset($this->options['custom_login_page']) ? $this->options['custom_login_page'] : '';
        
        if ($custom_login_page) {
            return get_permalink($custom_login_page);
        }
        
        return wp_login_url();
    }
    
    /**
     * Check if a specific page is allowed for non-logged-in users
     */
    public function is_page_allowed($page_id) {
        $allowed_pages = isset($this->options['allowed_pages']) ? $this->options['allowed_pages'] : array();
        return in_array($page_id, $allowed_pages);
    }
    
    /**
     * Get all allowed pages
     */
    public function get_allowed_pages() {
        return isset($this->options['allowed_pages']) ? $this->options['allowed_pages'] : array();
    }
    
    /**
     * Add a page to allowed list
     */
    public function allow_page($page_id) {
        $allowed_pages = $this->get_allowed_pages();
        if (!in_array($page_id, $allowed_pages)) {
            $allowed_pages[] = $page_id;
            $this->options['allowed_pages'] = $allowed_pages;
            update_option('cal_options', $this->options);
        }
    }
    
    /**
     * Remove a page from allowed list
     */
    public function disallow_page($page_id) {
        $allowed_pages = $this->get_allowed_pages();
        $key = array_search($page_id, $allowed_pages);
        if ($key !== false) {
            unset($allowed_pages[$key]);
            $this->options['allowed_pages'] = array_values($allowed_pages);
            update_option('cal_options', $this->options);
        }
    }
}
