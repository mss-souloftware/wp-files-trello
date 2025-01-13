<?php
/**
 * Case Manager for Law Firm Mini Trello Plugin
 */

// Ensure direct file access is prevented.
if (!defined('ABSPATH')) {
    exit;
}


function lfmt_case_manager_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_file_trello';

    // Handle case creation.
    if (isset($_POST['add_case'])) {
        $employee_ids = array_map('intval', $_POST['employee_ids']);
        $work_description = sanitize_text_field($_POST['work_description']);
        $file_path = sanitize_text_field($_POST['file_path']);

        foreach ($employee_ids as $employee_id) {
            $wpdb->insert($table_name, [
                'employee_id' => $employee_id,
                'work_description' => $work_description,
                'file_path' => $file_path,
                'created_at' => current_time('mysql'),
            ]);
        }

        echo '<div class="notice notice-success is-dismissible"><p>Case added successfully!</p></div>';
    }

    // Handle case deletion.
    if (isset($_GET['delete_case'])) {
        $case_id = intval($_GET['delete_case']);
        $wpdb->delete($table_name, ['id' => $case_id]);
        echo '<div class="notice notice-success is-dismissible"><p>Case deleted successfully!</p></div>';
    }

    // Fetch all cases.
    $cases = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    echo '<div class="wrap">';
    echo '<h1>Case Manager</h1>';

    // Form to add a new case.
    echo '<h2>Add New Case</h2>';
    echo '<form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="employee_ids">Assign Employees</label></th>
                    <td>
                        <select id="employee_ids" name="employee_ids[]" multiple required class="regular-text">';
                        
                        // Fetch all users with the 'employee' role.
                        $employees = get_users(['role' => 'employee']);
                        if (!empty($employees)) {
                            foreach ($employees as $employee) {
                                echo '<option value="' . esc_attr($employee->ID) . '">' . esc_html($employee->display_name) . ' (' . esc_html($employee->user_email) . ')</option>';
                            }
                        } else {
                            echo '<option value="" disabled>No employees found</option>';
                        }

    echo '          </select>
                        <p class="description">Hold down the Ctrl (Windows) or Command (Mac) key to select multiple employees.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="work_description">Work Description</label></th>
                    <td><textarea id="work_description" name="work_description" rows="5" required class="regular-text"></textarea></td>
                </tr>
                <tr>
                    <th><label for="file_path">File Path (optional)</label></th>
                    <td><input type="text" id="file_path" name="file_path" class="regular-text"></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="add_case" class="button button-primary" value="Add Case">
            </p>
          </form>';

    // Display existing cases.
    echo '<h2>All Cases</h2>';
    if (!empty($cases)) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>
                <tr>
                    <th>Case ID</th>
                    <th>Employee ID</th>
                    <th>Work Description</th>
                    <th>File</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
              </thead>';
        echo '<tbody>';
        foreach ($cases as $case) {
            echo '<tr>';
            echo '<td>' . esc_html($case->id) . '</td>';
            echo '<td>' . esc_html($case->employee_id) . '</td>';
            echo '<td>' . esc_html($case->work_description) . '</td>';
            echo '<td>' . (!empty($case->file_path) ? '<a href="' . esc_url($case->file_path) . '" target="_blank">View File</a>' : 'No File') . '</td>';
            echo '<td>' . esc_html($case->created_at) . '</td>';
            echo '<td>
                    <a href="' . esc_url(add_query_arg(['delete_case' => $case->id])) . '" class="button button-secondary">Delete</a>
                  </td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No cases found.</p>';
    }

    echo '</div>';
}
