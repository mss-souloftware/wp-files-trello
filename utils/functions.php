<?php

/**
 * 
 * @package WP Files Trello
 * @subpackage M. Sufyan Shaikh
 * 
 */

require_once plugin_dir_path(__FILE__) . '../admin/admin-dashboard.php';
require_once plugin_dir_path(__FILE__) . '../includes/case-manager.php';
require_once plugin_dir_path(__FILE__) . '../user/user-dashboard.php';



// Add 'employee' role if it doesn't exist.
if (!get_role('employee')) {
    $result = add_role('employee', 'Employee', [
        'read' => true,
        'upload_files' => true,
    ]);

    if ($result === null) {
        error_log('Failed to create employee role.');
    }
}


// Add a menu page for the admin dashboard.
add_action('admin_menu', 'lfmt_add_admin_menu');

function lfmt_add_admin_menu()
{
    // Add main menu page
    add_menu_page(
        'Law Firm Dashboard',       // Page title
        'Law Firm Dashboard',       // Menu title
        'manage_options',           // Capability
        'law-firm-dashboard',       // Menu slug
        'lfmt_admin_dashboard',     // Callback function
        'dashicons-clipboard',      // Menu icon
        6                           // Position
    );

    // Add submenu page under the main menu
    add_submenu_page(
        'law-firm-dashboard',        // Parent menu slug (same as the main menu slug)
        'Case Manager',              // Page title
        'Case Manager',              // Menu title
        'manage_options',            // Capability
        'case-manager',              // Menu slug
        'lfmt_case_manager_page',    // Callback function
        7                            // Position
    );
}


// Shortcode to display the user dashboard.
add_shortcode('user_dashboard', 'lfmt_user_dashboard');



// Frontend Scripts
function wpft_frontend_script()
{
    wp_enqueue_script('frontenScript', plugins_url('../assets/js/script.js', __FILE__), ['jquery'], null, true);
    wp_enqueue_style('frontendStyle', plugins_url('../assets/css/styles.css', __FILE__), array(), false);

    wp_localize_script('frontenScript', 'ajax_variables', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('my-ajax-nonce')
    ));
}
add_action('wp_enqueue_scripts', 'wpft_frontend_script');

