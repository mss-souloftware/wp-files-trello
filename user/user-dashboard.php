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

function lfmt_user_dashboard()
{
    // Ensure the user is logged in and has the 'employee' role.
    if (!is_user_logged_in()) {
        return '<p>You need to be logged in to access this page.</p>';
    }

    $current_user = wp_get_current_user();
    if (!in_array('employee', $current_user->roles)) {
        return '<p>You do not have the necessary permissions to view this page.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_file_trello';
    $comments_table = $wpdb->prefix . 'wp_case_comments';
    $files_table = $wpdb->prefix . 'wp_case_files';
    $user_id = $current_user->ID;

    // Fetch cases assigned to the current user.
    $cases = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE employee_id = %d ORDER BY created_at DESC",
        $user_id
    ));

    ob_start();
    if (isset($_GET['case_id'])) { ?>

        <h1>Testing</h1>

    <?php } else {
        echo '<div class="wrap">';
        echo '<h1>Your Dashboard</h1>';

        // Display assigned cases.
        echo '<h2>Assigned Cases</h2>';
        if (!empty($cases)) {
            foreach ($cases as $case) {
                ?>

                <a class="casePanel" href="<?php echo 'http://localhost/google-sheet/panel/?case_id=' . $case->id; ?>">
                    <div class="caseDetails">
                        <span><?php echo $case->created_at; ?></span>
                        <h3>
                            <?php echo $case->case_title; ?>
                        </h3>
                        <p>
                            <?php echo $case->work_description; ?>
                        </p>
                    </div>
                    <div class="assignEmployee"></div>
                </a>

                <?php
            }
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>
                <tr>
                    <th>Case Title</th>
                    <th>Work Description</th>
                    <th>File</th>
                    <th>Created At</th>
                    <th>Comments</th>
                    <th>Uploaded Files</th>
                    <th>Actions</th>
                </tr>
              </thead>';
            echo '<tbody>';
            foreach ($cases as $case) {
                // Fetch comments for the current case.
                $comments = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM $comments_table WHERE case_id = %d ORDER BY created_at ASC",
                    $case->id
                ));

                // Fetch files for the current case.
                $files = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM $files_table WHERE case_id = %d ORDER BY uploaded_at ASC",
                    $case->id
                ));

                echo '<tr>';
                echo '<td>' . esc_html($case->case_title) . '</td>';
                echo '<td>' . esc_html($case->work_description) . '</td>';
                echo '<td>' . (!empty($case->file_path) ? '<a href="' . esc_url($case->file_path) . '" target="_blank">View File</a>' : 'No File') . '</td>';
                echo '<td>' . esc_html($case->created_at) . '</td>';

                // Display comments.
                echo '<td>';
                if (!empty($comments)) {
                    echo '<ul>';
                    foreach ($comments as $comment) {
                        echo '<li>' . esc_html($comment->comment) . ' <small>(' . esc_html($comment->created_at) . ')</small></li>';
                    }
                    echo '</ul>';
                } else {
                    echo 'No comments yet.';
                }
                echo '</td>';

                // Display uploaded files.
                echo '<td>';
                if (!empty($files)) {
                    echo '<ul>';
                    foreach ($files as $file) {
                        echo '<li><a href="' . esc_url($file->file_path) . '" target="_blank">View File</a> <small>(' . esc_html($file->uploaded_at) . ')</small></li>';
                    }
                    echo '</ul>';
                } else {
                    echo 'No files uploaded.';
                }
                echo '</td>';

                echo '<td>
                    <button class="button button-primary update-status-btn" data-case-id="' . esc_attr($case->id) . '">Update Status</button>
                    <button class="button button-secondary upload-file-btn" data-case-id="' . esc_attr($case->id) . '">Upload File</button>
                  </td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No cases assigned to you.</p>';
        }
    }
    // File upload and status update forms (hidden by default).
    echo '<div id="status-update-form" >
            <h3>Update Work Status</h3>
            <form method="post" action="">
                <input type="hidden" name="case_id" id="update_case_id">
                <textarea name="work_status" rows="5" required placeholder="Enter your status update here..."></textarea>
                <p class="submit">
                    <input type="submit" name="submit_status" class="button button-primary" value="Update Status">
                </p>
            </form>
          </div>';

    echo '<div id="file-upload-form" >
            <h3>Upload File</h3>
            <form method="post" enctype="multipart/form-data" action="">
                <input type="hidden" name="case_id" id="upload_case_id">
                <input type="file" name="case_file" required>
                <p class="submit">
                    <input type="submit" name="submit_file" class="button button-primary" value="Upload File">
                </p>
            </form>
          </div>';

    echo '</div>';

    // Handle Status Update.
    if (isset($_POST['submit_status'])) {
        $case_id = intval($_POST['case_id']);
        $work_status = sanitize_text_field($_POST['work_status']);

        // Update existing case with a comment.
        $wpdb->insert($wpdb->prefix . 'wp_case_comments', [
            'case_id' => $case_id,
            'comment' => $work_status,
            'employee_id' => $user_id,
            'created_at' => current_time('mysql'),
        ]);

        // Redirect to avoid form resubmission.
        wp_redirect(get_permalink());
        exit;
    }

    // Handle File Upload.
    if (isset($_POST['submit_file'])) {
        $case_id = intval($_POST['case_id']);
        $uploaded_file = $_FILES['case_file'];

        // Handle file upload.
        $upload = wp_handle_upload($uploaded_file, ['test_form' => false]);
        if (isset($upload['url'])) {
            $wpdb->insert($wpdb->prefix . 'wp_case_files', [
                'case_id' => $case_id,
                'file_path' => $upload['url'],
                'employee_id' => $user_id,
                'uploaded_at' => current_time('mysql'),
            ]);
        }

        // Redirect to avoid form resubmission.
        wp_redirect(get_permalink());
        exit;
    }

    return ob_get_clean();
}
