<?php
/*
Template Name: Account
*/
$wpdb->hide_errors(); nocache_headers();

global $userdata; get_currentuserinfo();

if(!empty($_POST['action'])){

	require_once(ABSPATH . 'wp-admin/includes/user.php');
	require_once(ABSPATH . WPINC . '/registration.php');

	check_admin_referer('update-profile_' . $user_ID);

	$errors = edit_user($user_ID);

	if ( is_wp_error( $errors ) ) {
		foreach( $errors->get_error_messages() as $message )
			$errmsg = "$message";
	}

	if($errmsg == '')
	{
		do_action('personal_options_update',$user_ID);
		$d_url = $_POST['dashboard_url'];
		wp_redirect(home_url() . '?page_id='.$post->ID.'&updated=true' );
	}
	else{
		$errmsg = '' . $errmsg . '';

	}
}

get_header();
get_currentuserinfo();

// If the user role is 'client' redirect to the client page.
if( current_user_can('client')) {
	header('Location: '.home_url().'/client');
	exit();
}

?>

        <div class="content">
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <h1><?php echo $userdata->first_name ?> <?php echo $userdata->last_name ?></h1>
            <?php endwhile; endif; ?>

            <p>You can update your account details here, but only an administrator can change your role.</p>

            <form name="accountForm" action="" method="post" id="form" enctype="multipart/form-data" onsubmit="return validateName()">

                    <?php wp_nonce_field('update-profile_' . $user_ID) ?>
                    <input type="hidden" name="from" value="profile" />
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="checkuser_id" value="<?php echo $user_ID ?>" />
                    <input type="hidden" name="dashboard_url" value="<?php echo get_option("dashboard_url"); ?>" />
                    <input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id; ?>" />
                    <?php if ( isset($_GET['updated']) ): $d_url = $_GET['d'];?>
                    <div class="message roundness green">
                        <p>Awesomeness. Your details were successfully updated.</p>
                        <span class="close-message"></span>
                    </div>
                    <?php elseif($errmsg!=""): ?>
                    <div class="message roundness orange"><?php echo $errmsg;?></div>
                    <?php endif;?>

                    <fieldset class="update-profile">

						<div class="avatar-info">

                            <?php if( user_has_gravatar( $userdata->user_email )) {
								echo get_avatar( $current_user->ID, 100 ) . '<p>Edit your avatar by logging in (or creating an account) with your ' . $userdata->user_email . ' email address at <a href="https://en.gravatar.com/" target="_blank">gravatar.com</a>.</p>';
							} else {
								echo get_avatar( $current_user->ID, 100 ) . '<p>Don\'t have an avatar? Create a free <a href="https://en.gravatar.com/" target="_blank">Gravatar account</a> using your ' . $userdata->user_email . ' email address.</p>';
							} ?>

                        </div>

                        <label>First name <span class="required">*</span>
                        <input type="text" name="first_name" class="text roundness" id="first_name" value="<?php echo $userdata->first_name ?>" /></label>

                        <label>Last name
                        <input type="text" name="last_name" class="text roundness" id="last_name" value="<?php echo $userdata->last_name ?>" /></label>

                        <label>Email <span class="required">*</span>
                        <input type="text" name="email" class="text roundness" id="email" value="<?php echo $userdata->user_email ?>" /></label>

                        <label class="user-role">Role:
                        <em class="pseudo-field"><?php $user_roles = $current_user->roles; $user_role = array_shift($user_roles); echo $user_role; ?></em></label>

                        <label>New password
                        <input type="password" name="pass1" class="text roundness" id="pass1" value="" /></label>

                        <label>Confirm your new password
                        <input type="password" name="pass2" class="text roundness" id="pass2" value="" /></label>

                        <input type="submit" value="Update" class="button update-icon roundness" />
                        <input type="hidden" name="action" value="update" />

                 </fieldset>
            </form>

        </div>

<?php get_footer(); ?>
