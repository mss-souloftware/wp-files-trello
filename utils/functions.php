<?php

/**
 * 
 * @package WP Files Trello
 * @subpackage M. Sufyan Shaikh
 * 
 */


function daily_work_admin_menu()
{
    add_menu_page(
        'Daily Work Submissions',
        'Work Submissions',
        'manage_options',
        'daily-work-submissions',
        'daily_work_admin_page'
    );
}
add_action('admin_menu', 'daily_work_admin_menu');

function daily_work_admin_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'daily_work';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    echo '<h2>All Work Submissions</h2>';
    echo '<table border="1" style="width:100%; text-align:left;">
            <tr>
                <th>Employee ID</th>
                <th>Work Description</th>
                <th>File</th>
                <th>Date</th>
            </tr>';
    foreach ($results as $row) {
        echo '<tr>';
        echo '<td>' . esc_html($row->employee_id) . '</td>';
        echo '<td>' . esc_html($row->work_description) . '</td>';
        echo '<td>' . ($row->file_path ? '<a href="' . esc_url($row->file_path) . '" target="_blank">Download</a>' : 'No file') . '</td>';
        echo '<td>' . esc_html($row->created_at) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

function daily_work_employee_panel()
{
    // if (current_user_can('employee')) {
        echo '<h2>Daily Work Submission</h2>';
        ?>
        <form method="post" enctype="multipart/form-data">
            <textarea name="work_description" rows="5" cols="40" placeholder="Describe your work" required></textarea>
            <br><br>
            <label for="file_upload">Upload File:</label>
            <input type="file" name="file_upload" id="file_upload">
            <br><br>
            <button type="submit" name="submit_work">Submit</button>
        </form>
        <?php

        if (isset($_POST['submit_work'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'daily_work';
            // $employee_id = get_current_user_id();
            $employee_id = 1;
            $work_description = sanitize_textarea_field($_POST['work_description']);
            $file_path = '';

            if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
                $upload = wp_upload_bits($_FILES['file_upload']['name'], null, file_get_contents($_FILES['file_upload']['tmp_name']));
                if (!$upload['error']) {
                    $file_path = $upload['url'];
                }
            }

            $wpdb->insert($table_name, [
                'employee_id' => $employee_id,
                'work_description' => $work_description,
                'file_path' => $file_path
            ]);

            echo '<p>Work submitted successfully!</p>';
        }
    }
// }
add_shortcode('daily_work_employee_panel', 'daily_work_employee_panel');
