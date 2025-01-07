<?php
/**
 * Plugin Name: WP Files Trello
 * Plugin URI: https://souloftware.com/
 * Description: This is a mini trello plugin for wordpress to connect with employee.
 * Version: 1.0.0
 * Author: Souloftware
 * Author URI: https://souloftware.com/contact
 */

if (!defined('ABSPATH')) {
    exit;
}


require_once plugin_dir_path(__FILE__) . './admin/activationPlugin/activatePlugin.php';
// ACTIIVATION PLUGIN FUNCTION -CREATE TABLES -
register_activation_hook(__FILE__, 'createAllTables');

register_deactivation_hook(__FILE__, 'deactivationSetNull');

register_uninstall_hook(__FILE__, 'removeAllTables');



// Include mfp-functions.php, use require_once to stop the script if mfp-functions.php is not found
require_once plugin_dir_path(__FILE__) . 'utils/functions.php';Ya empecé a trabajar en el plugin de abogado.