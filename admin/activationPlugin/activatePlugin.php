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

  if (get_option($wpft_registered) !== false) {
    return;
  }

  try {
    // Create the custom database table.
    $table_plugin = $wpdb->prefix . "wp_file_trello";
    $charset_collate = $wpdb->get_charset_collate();

    $createTablePlugin = "CREATE TABLE $table_plugin (
             id INT(11) NOT NULL AUTO_INCREMENT,
             employee_id INT(11) NOT NULL,
             work_description TEXT NOT NULL,
             file_path VARCHAR(255) DEFAULT NULL,
             created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
             PRIMARY KEY (id)
         ) $charset_collate;";

    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    dbDelta($createTablePlugin);

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

    // Mark plugin as registered.
    add_option($wpft_registered, true);
  } catch (Throwable $error) {
    error_log('Error during plugin activation: ' . $error->getMessage());
    deactivate_plugins(plugin_basename(__FILE__)); // Deactivate the plugin on failure.
  }
}


function removeAllTables()
{
  $optionsToDelette = [
    "wpft_registered"
  ];

  global $wpdb;

  $table_plugin = $wpdb->prefix . "wp_file_trello";

  try {
    $removal_pluginDatabase = "DROP TABLE IF EXISTS {$table_plugin}";
    $remResult2 = $wpdb->query($removal_pluginDatabase);

    foreach ($optionsToDelette as $options_value) {
      if (get_option($options_value)) {
        delete_option($options_value);
      }
    }

    return $remResult2;
  } catch (\Throwable $erro) {
    error_log($erro->getMessage());
    return $erro;
  }
}