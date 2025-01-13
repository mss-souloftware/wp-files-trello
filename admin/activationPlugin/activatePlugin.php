<?php
/**
 * 
 * @package WP Files Trello
 * @subpackage M. Sufyan Shaikh
 * 
 */

function createAllTables()
{
  global $wpdb;
  $wpft_registered = "wpft_registered";

  if (get_option($wpft_registered) === null) {
    return;
  }

  try {
    $charset_collate = $wpdb->get_charset_collate();

    // Table for cases.
    $table_plugin = $wpdb->prefix . "wp_file_trello";
    $createTablePlugin = "CREATE TABLE $table_plugin (
             id INT(11) NOT NULL AUTO_INCREMENT,
             employee_id INT(11) NOT NULL,
             work_description TEXT NOT NULL,
             file_path VARCHAR(255) DEFAULT NULL,
             created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
             PRIMARY KEY (id)
         ) $charset_collate;";

    // Table for comments on cases.
    $table_comments = $wpdb->prefix . "wp_case_comments";
    $createTableComments = "CREATE TABLE $table_comments (
             id INT(11) NOT NULL AUTO_INCREMENT,
             case_id INT(11) NOT NULL,
             comment TEXT NOT NULL,
             employee_id INT(11) NOT NULL,
             created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
             PRIMARY KEY (id),
             FOREIGN KEY (case_id) REFERENCES $table_plugin(id) ON DELETE CASCADE
         ) $charset_collate;";

    // Table for uploaded files related to cases.
    $table_files = $wpdb->prefix . "wp_case_files";
    $createTableFiles = "CREATE TABLE $table_files (
             id INT(11) NOT NULL AUTO_INCREMENT,
             case_id INT(11) NOT NULL,
             file_path VARCHAR(255) NOT NULL,
             employee_id INT(11) NOT NULL,
             uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
             PRIMARY KEY (id),
             FOREIGN KEY (case_id) REFERENCES $table_plugin(id) ON DELETE CASCADE
         ) $charset_collate;";

    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    dbDelta($createTablePlugin);
    dbDelta($createTableComments);
    dbDelta($createTableFiles);

    // Mark plugin as registered.
    update_option($wpft_registered, true);
  } catch (Throwable $error) {
    error_log('Error during plugin activation: ' . $error->getMessage());
    deactivate_plugins(plugin_basename(__FILE__)); // Deactivate the plugin on failure.
  }
}

function removeAllTables()
{
  global $wpdb;

  $table_plugin = $wpdb->prefix . "wp_file_trello";
  $table_comments = $wpdb->prefix . "wp_case_comments";
  $table_files = $wpdb->prefix . "wp_case_files";

  $optionsToDelete = ["wpft_registered"];

  try {
    // Drop all custom tables.
    $wpdb->query("DROP TABLE IF EXISTS $table_files");
    $wpdb->query("DROP TABLE IF EXISTS $table_comments");
    $wpdb->query("DROP TABLE IF EXISTS $table_plugin");

    // Remove plugin options.
    foreach ($optionsToDelete as $option) {
      if (get_option($option)) {
        delete_option($option);
      }
    }
  } catch (Throwable $error) {
    error_log('Error during plugin deactivation: ' . $error->getMessage());
  }
}