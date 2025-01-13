<?php
/**
 * 
 * @package WP Files Trello
 * @subpackage M. Sufyan Shaikh
 * 
 */

// Ensure direct file access is prevented.
if (!defined('ABSPATH')) {
    exit;
}

// Admin dashboard content.
function lfmt_admin_dashboard()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_file_trello';

    // Fetch cases and associated employees from the database.
    $cases = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    echo '<div class="wrap">';
    echo '<h1>Law Firm Dashboard</h1>';

    // Case Management Section.
    echo '<h2>All Cases</h2>';
    if (!empty($cases)) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>
                <tr>
                    <th>Title</th>
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
            echo '<td>' . esc_html($case->case_title) . '</td>';
            echo '<td>' . esc_html($case->employee_id) . '</td>';
            echo '<td>' . esc_html($case->work_description) . '</td>';
            echo '<td>' . (!empty($case->file_path) ? '<a href="' . esc_url($case->file_path) . '" target="_blank">View File</a>' : 'No File') . '</td>';
            echo '<td>' . esc_html($case->created_at) . '</td>';
            echo '<td>
                    <a href="?page=law-firm-dashboard&action=delete&id=' . intval($case->id) . '" class="button button-danger">Delete</a>
                  </td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No cases found.</p>';
    }

    // Add Case Form.
    echo '<h2>Add New Case</h2>';
    echo '<form method="post" action="">
        <table class="form-table">
        <tr>
                <th><label for="case_title">Case Title</label></th>
                <td><input type="text" id="case_title" name="case_title" required class="regular-text"></textarea></td>
            </tr>
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
            <input type="submit" name="submit_case" id="submit" class="button button-primary" value="Add Case">
        </p>
      </form>';

    echo '</div>';


    // Handle Case Submission.
    if (isset($_POST['submit_case'])) {
        $case_title = sanitize_text_field($_POST['case_title']);
        $employee_id = intval($_POST['employee_ids']);
        $work_description = sanitize_text_field($_POST['work_description']);
        $file_path = sanitize_text_field($_POST['file_path']);

        $wpdb->insert($table_name, [
            'employee_id' => $employee_id,
            'case_title' => $case_title,
            'work_description' => $work_description,
            'file_path' => $file_path,
            'created_at' => current_time('mysql'),
        ]);

        // Redirect to avoid form resubmission.
        wp_redirect(admin_url('admin.php?page=law-firm-dashboard'));
        exit;
    }

    // Handle Case Deletion.
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $wpdb->delete($table_name, ['id' => $id]);

        // Redirect after deletion.
        wp_redirect(admin_url('admin.php?page=law-firm-dashboard'));
        exit;
    }
}
