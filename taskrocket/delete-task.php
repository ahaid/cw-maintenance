<?php

	define('WP_USE_THEMES', false);

	require ("../../../wp-blog-header.php");

	$post_id = $_GET["post_id"];

	$post = get_post( $post_id );
    $category = get_the_category( $post_id )[0];
    $current_user = wp_get_current_user();
   
    $projectURL = get_category_link($category->term_id ) . '?deleted=1&title=' . urlencode($post->post_title);
    
    wp_delete_post( $post_id, true );

    wp_redirect( $projectURL );
	exit;

?>