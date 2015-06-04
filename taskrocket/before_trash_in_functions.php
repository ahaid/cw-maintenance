<?php
function before_wp_trash_post ($post_id) {
    // If option to not send emails is true
    $options = get_option( 'taskrocket_settings' );
    if ($options['no_emails'] == true) {
        // Emails are not sent
    } else {
        // Create HTML email for the recipient (new task owner)
        $post = get_post( $post_id );
        $category = get_the_category( $post_id )[0];
        $recipient = $post->post_author;
        $user_info = get_userdata($recipient);
        $emailTo = $user_info->user_email;
        $projectURL = get_category_link($category->term_id );
        $taskURL = get_permalink( $post_id );
        $projectname = $category->name;
        $thepriority = get_post_meta($post_id, 'priority', TRUE);
        $reporter = get_post_meta($post_id, 'reporter_email', TRUE);
        $emailTo = $emailTo . ',' . $reporter;

        if ($thepriority == 'low') { $prioritycolor = "43bce9"; }
        if ($thepriority == 'normal') { $prioritycolor = "48cfae"; }
        if ($thepriority == 'high') { $prioritycolor = "f9b851"; }
        if ($thepriority == 'urgent') { $prioritycolor = "fb6e52"; }

        $current_user = wp_get_current_user();
        $taskSenderFirstName = $current_user->user_firstname;
        $taskSenderLastName = $current_user->user_lastname;
        $tasksendericon  = 'http://www.gravatar.com/avatar/' . md5($current_user->user_email) . '?s=120';
        $taskreceivericon = 'http://www.gravatar.com/avatar/' . md5($emailTo) . '?s=120';
        if(get_post_meta($post->ID, 'duedate', TRUE) == "") {
            $duedate = "Not specified";
        } else {
            $duedate = get_post_meta($post_id, 'duedate', TRUE);
        }
        $subject = 'CW Maintenance complete: ' . get_the_title();
        $body = '
        <table width="100%" height="100%" border="0" cellspacing="25" cellpadding="0" style="background:#f1f1f1;padding:100px 0;">
        <tr>
        <td align="center" valign="middle">
        <table width="400" border="0" cellspacing="0" cellpadding="0" style="padding:25px; background:#fff; width:400px;text-align:left; border-left:solid 4px #' . $prioritycolor . ';" bgcolor="#ffffff">
        <tr>
        <td>
        <p style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#333645;"><strong>' . $taskSenderFirstName . ' ' . $taskSenderLastName . '</strong> completed the task <strong>' . $post->post_title  . '</strong>.</p>
        <p style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#333645;"><strong style="display:block;float:left;width:70px;">Location:</strong> <a style="color:#333645;" href="' . $projectURL . '">' . $projectname . '</a>.</p>
        <p style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#333645;text-transform:capitalize;"><strong style="display:block;float:left;width:70px;text-transform:capitalize;">Priority:</strong> ' . $thepriority . '</p>
        <p style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#333645;"><strong style="display:block;float:left;width:70px;">Due by:</strong> '. $duedate . '</p>
        <a style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#fff; display:block; width:85px; height:18px; background:#' . $prioritycolor . ';text-decoration:none;padding:10px; text-align:center;" href="' . $taskURL . '">View Task</a>
        </td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        ';
        $headers[]= "Content-type:text/html;charset=UTF-8";
        $headers[]= 'From: CW Maintenance App <' . $current_user->user_email . '>';
        $headers[]= 'Reply-To: ' . $current_user->user_email;
        $headers[]= "MIME-Version: 1.0";
        wp_mail($emailTo, $subject, $body, $headers);
    }
    // End If option to not send emails is true
}
add_action('wp_trash_post', 'before_wp_trash_post');
?>