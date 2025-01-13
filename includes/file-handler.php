<?php
/**
 * File Handler for Law Firm Mini Trello Plugin
 */

// Ensure direct file access is prevented.
if (!defined('ABSPATH')) {
    exit;
}

// Handle file uploads for a case.
function lfmt_handle_file_upload($file, $case_id)
{
    if (empty($file['name']) || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return new WP_Error('upload_error', 'There was an error uploading the file.');
    }

    // Define upload directory
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['path'] . '/';
    $target_file = $target_dir . basename($file['name']);

    // Check if file already exists
    if (file_exists($target_file)) {
        return new WP_Error('file_exists', 'The file already exists.');
    }

    // Move uploaded file to the WordPress upload directory
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Save file path in the database for the respective case.
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_file_trello';

        // Update the case with the file path.
        $updated = $wpdb->update(
            $table_name,
            ['file_path' => $target_file],
            ['id' => $case_id],
            ['%s'],
            ['%d']
        );

        if ($updated) {
            return $target_file; // Return the file path on success
        } else {
            return new WP_Error('db_error', 'There was an error updating the database.');
        }
    } else {
        return new WP_Error('move_error', 'The file could not be moved.');
    }
}

// Retrieve file for download.
function lfmt_get_case_file($case_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_file_trello';

    // Fetch file path from the database based on the case ID
    $case = $wpdb->get_row($wpdb->prepare("SELECT file_path FROM $table_name WHERE id = %d", $case_id));

    if ($case && !empty($case->file_path)) {
        return $case->file_path; // Return the file path
    }

    return null; // No file found
}

// Delete file from server and update database
function lfmt_delete_case_file($case_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_file_trello';

    // Fetch the file path from the database
    $case = $wpdb->get_row($wpdb->prepare("SELECT file_path FROM $table_name WHERE id = %d", $case_id));

    if ($case && !empty($case->file_path)) {
        $file_path = $case->file_path;

        // Delete the file from the server
        if (file_exists($file_path)) {
            unlink($file_path); // Remove the file
        }

        // Remove the file path from the database
        $wpdb->update(
            $table_name,
            ['file_path' => null],
            ['id' => $case_id],
            ['%s'],
            ['%d']
        );

        return true;
    }

    return false; // File not found or unable to delete
}
