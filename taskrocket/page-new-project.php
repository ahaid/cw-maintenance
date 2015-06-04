<?php
/*
Template Name: New Project
*/

require_once(ABSPATH . "wp-admin/includes/taxonomy.php");

get_header();
global $wp;
$current_url = home_url(add_query_arg(array(),$wp->request));
// If the user role is 'client' redirect to the client page.
if( current_user_can('client')) {
	header('Location: '.home_url().'/client');
	exit();
}
$options = get_option( 'taskrocket_settings' );
?>


        <div class="content">
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <h1><?php the_title(); ?></h1>
            <?php endwhile; endif; ?>

            <?php
				if( isset( $_POST['submit'] ) ) {
				if( !empty( $_REQUEST['newcat'] ) ) {

					$cat_ID = get_cat_ID( sanitize_title_for_query($_POST['newcat']) );

					// Sanitise
					$cat_name = sanitize_text_field($_POST['newcat']);
					$cat_desc = sanitize_text_field($_POST['description']);
					$cat_parent = sanitize_text_field($_POST['cat']);
					$cat_slug = sanitize_title_with_dashes($cat_name);

					$project = array(
						'cat_name' => $cat_name,
						'category_description' => $cat_desc,
						'category_nicename' => $cat_slug,
						'category_parent' => $cat_parent
					);

					if( wp_insert_category( $project ) ) { ?>

						<div class="message roundness green project-added">
                        	<p>Project added. <span>Go to <a href="<?php echo home_url(); ?>/projects/<?php echo $cat_parent; ?><?php echo $cat_slug; ?>"><?php echo stripslashes($cat_name); ?></a> to start adding tasks, or add another project below.</span></p><span class="close-message"></span>
                        </div>

					<?php } else { ?>

						<div class="message roundness error"><p>Sorry, but a project called <?php echo $cat_name; ?> already exists.</p><span class="close-message"></span></div>

					<?php } ?> <?php
				}
			}
			?>


           <?php
		   if ($options['users_create_projects'] == true || current_user_can( 'manage_options' )) { ?>
            <p>Add a project here and you'll be able to start adding tasks to it.</p>
            <div class="add-project">
                <form action="<?php echo $current_url; ?>" name="projectForm" method="post" onsubmit="return validateTitle()">
                    <label for="newcat">Project Name <span class="required">*</span><span id="title-chars" class="chars"></span></label>
                    <input type="text" id="newcat" name="newcat" class="text half-text roundness" value="" maxlength="100" />

                    <label for="description">Description <span id="desc-chars" class="chars"></span></label>
                    <input name="description" id="description" type="text" class="text half-text roundness" value="" maxlength="200" />

                    <input type="submit" name="submit" class="button add-icon roundness" value="Create" />
                </form>
			</div>
            <?php } else { ?>
            <p>Dnag. It looks like the ability to add projects from here has been disabled by an administrator. You'll need to ping one of these admins:</p>

            <?php
			$trusers = get_users('orderby=nicename&role=administrator');
			foreach ($trusers as $user) { ?>
            <p>
			<?php if ($user->first_name !== "") {
			echo $user->first_name . " " . $user->last_name;
			} else {
			echo "Captain Noname";
			}
			?> (<?php echo $user->user_email; ?>)</p>
			<?php
				}
			?>

            <?php } ?>

        </div>

<?php get_footer(); ?>
