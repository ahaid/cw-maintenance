<?php
/*
Template Name: Users
*/
global $wpdb;
$user_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->users" );
get_header();

// If the user role is 'client' redirect to the client page.
if( current_user_can('client')) {
	header('Location: '.home_url().'/client');
	exit();
}
$options = get_option( 'taskrocket_settings' );
?>

<div class="content all-projects roundness">

    <h1><?php the_title(); ?></h1>
    <p class="user-count">Currently there<?php if ($user_count <= 1) { echo " is " . $user_count . " user"; } else { echo " are <span>" . $user_count . "</span> users"; } ?> registered in Task Rocket.</p>

	<?php require('include-users.php'); ?>

</div>

<?php get_footer(); ?>
