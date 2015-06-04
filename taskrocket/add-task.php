<?php

	define('WP_USE_THEMES', false);

	require ("../../../wp-blog-header.php");

	$category = $_POST["categoryID"]; // Get this from the hidden field 'categoryID' on the form.
	$categorySlug = $_POST["categorySlug"]; // Get the category slug from the hidden field 'categorySlug' on the form.

	if(isset($_POST['submitted']) && isset($_POST['post_nonce_field']) && wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')) {

	$postTitle = trim($_POST['title']);
	$postContent = $_POST["minfo"];
	$duedate = $_POST["duedate"];
	$priority = $_POST["priority"];
	$private = $_POST["private"];
	
	$template_dir = get_bloginfo('template_directory');
	$my_post = array();
	$my_post['post_author'] = $_POST["project_contributor"];
	$my_post['post_status'] = 'publish';
	$my_post['post_title'] = strip_tags ($postTitle);
	$my_post['post_content'] = strip_tags($postContent);
	$my_post['post_category'] = array( $category);
	$my_post['filter'] = true;
	$post_id = wp_insert_post( $my_post);

	update_post_meta( $post_id, 'duedate', $duedate);
	update_post_meta( $post_id, 'minfo', $postContent);
	update_post_meta( $post_id, 'priority', $priority);
	update_post_meta( $post_id, 'private', $private);
	$current_user = wp_get_current_user();
	$reporter = $current_user->user_email;
	update_post_meta( $post_id, 'reporter_email', $reporter);

	// Upload file(s)
	if ( $_FILES ) {
	$files = $_FILES["tr_multiple_attachments"];
	foreach ($files['name'] as $key => $value) {
			$pid = $post_id;
			if ($files['name'][$key]) {
				$file = array(
					'name' => $files['name'][$key],
					'type' => $files['type'][$key],
					'tmp_name' => $files['tmp_name'][$key],
					'error' => $files['error'][$key],
					'size' => $files['size'][$key]
				);

				$_FILES = array (
					'tr_multiple_attachments' => $file
				);
				foreach ($_FILES as $file => $array) {
					$newupload = tr_handle_attachment($file,$pid);
				}
			}
		}
	}

	// If the refering page was the new task page...
	if ($_POST["referer"] == "new-task") {

		if ($priority == 'low') { $prioritycolor = "43bce9"; }
		if ($priority == 'normal') { $prioritycolor = "48cfae"; }
		if ($priority == 'high') { $prioritycolor = "f9b851"; }
		if ($priority == 'urgent') { $prioritycolor = "fb6e52"; }

		sendTaskEmail($post_id, 'created', null);

		// Redirect to...
		header("Location: " . home_url() . "/new-task?task_status=added&task_name=$postTitle&project=$category");

		// Otherwise....
	} else {
		header("Location: " . home_url() . "/projects/" . $categorySlug . "/?task_status=added&task_name=$postTitle");
	}
}
?>
