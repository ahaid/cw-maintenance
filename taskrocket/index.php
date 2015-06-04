<?php
ob_start();
/**
 * @package WordPress
 * @subpackage Default_Theme
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

<?php // If user is a client
	if( !current_user_can('client')) { ?>

    <div class="dashboard">

    	<?php if($_GET['trashed'] == '1') { // Show this message when task is completed
			$pagetitle = get_the_title( $_GET['ids'] );
			?>
			<div class="message roundness green task-complete-message">
				<?php
					$_wpnonce = wp_create_nonce( 'untrash-post_' . $_GET['ids'] );
					$url = admin_url( 'post.php?post=' . $_GET['ids'] . '&action=untrash&_wpnonce=' . $_wpnonce );
				?>
				<p>Task Complete &amp; Archived: <span class="completed-task-name"><?php echo strip_tags($pagetitle); ?></span> <span class="undo roundness"><a href="<?php echo $url ?>">Undo</a></span></p>
				<span class="close-message"></span>
            </div>
		<?php } ?>

 		<h1>Dashboard</h1>

 		<?php if (get_currentuser_role() != 'unknown'){ ?>

			<?php
			if ($options['dash_message']) { ?>
	        <div class="dash-message roundness <?php echo $options['dash_color']; ?>">
				<?php echo $options['dash_message']; ?>
	        </div>
			<?php } ?>

	        <?php require_once('include-activity-statement.php'); ?>

	        <?php require('include-chart.php'); ?>

			<?php require('include-recent.php'); ?>

	        <?php
			if ($options['disable_tips'] == false) {  ?>

	        <hr class="rule" />

	        <p class="tip"><strong>TIP</strong>
	        <?php
			$random_tip = array("Limit the scope of your search by using the Project Search Filter below the search field.",
								"If your email address is also used on your Gravatar account, your photo will be automatically shown in Task Rocket.",
								"You can change your password and other details in <a href='account'>account settings</a>.",
								"Project page overdue dates will be shown in red.",
								"Tasks priorities are indicated by colour. Blue = Low, Green = Normal, Orange = High and Red = Urgent.",
								"You can attach multiple files to projects.",
								"You can give your clients a link to a dedicated status dashboard.",
								"Task Rocket plays nice on mobile devices.",
								"Administrators can make tasks private.",
								"Grab the free <a href='https://chrome.google.com/webstore/detail/task-rocket/ffoefldcgmcldohibnklhhphdgdpjdnd' target='_blank'>Chrome Extension</a>."
								);
			srand(time());
			$sizeof = count($random_tip);
			$random = (rand()%$sizeof);
			print("$random_tip[$random]");
			?>
	        </p>
	        <?php } ?>

	     <?php }  else { ?>

	     	<p>&nbsp;</p>
	     	
	     	<h2>You don't have a role yet</h2>

			<p>An administrator must assign a role to you in the system before you can create or manage tasks.</p>
	     
	     <?php } ?>

    </div>

<?php // If user is a client
} ?>

<?php get_footer(); ?>
