<?php
ob_start();
/*
Template Name: Clients
*/
global $wpdb;
get_currentuserinfo();
global $user_ID;
$category = get_cat_id( single_cat_title("",false) );
get_header();
?>

    <?php
		// Current User ID
		$user_ID = get_current_user_id();

		// Project ID
		$projectID = $current_user->client_project;
	?>

    <?php if ($projectID =="") { ?>


    <div class="content">
    	<h1>Client Preview</h1>
        <p>You have not be given access to preview this dashboard. Please contact <a href="mailto:<?php $admin_email = get_option( 'admin_email' ); echo $admin_email; ?>?subject=Requesting access to client dashboard."><?php $admin_email = get_option( 'admin_email' ); echo $admin_email; ?></a> to enquire about gaining access.</p>
    </div>


    <?php } else { ?>


    <!--/ Start the client view /-->
    <div class="client-view">

    	<?php // Get active posts
			$activetasks = get_term_by('id','' . $projectID . '','category');
			$activetasks = $activetasks->count;

			// Get trashed posts
			$args = array(
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'post_status'    => 'trash',
				'post_type'      => 'post',
				'category'       => $projectID );
			$post=get_posts($args);
			$completedtasks = ( $post ) ? count( $post ) : 0;

			$totaltasks = $activetasks + $completedtasks;
			$remainingtasks = $totaltasks - $completedtasks;


			if ($remainingtasks != 0) {
				$percentagecomplete = 100 * $activetasks / $totaltasks;
				$inverse = 100 - $percentagecomplete;
			} else {
				$inverse = 0;
			}


			$allremainingtasks = $totaltasks - $completedtasks;


			//echo "active tasks: " . $activetasks . "<br />";
			//echo "completed tasks: " . $completedtasks . "<br />";
			//echo "remaining tasks: " . $remainingtasks . "<br />";
			//echo "total tasks: " . $totaltasks . "<br />";
			//echo "inverse: " . $inverse . "<br />";
        ?>

    	<div class="container">

            <div class="header">
                <h1><?php echo get_the_category_by_ID($projectID); ?></h1>
            </div>

            <ul>
                <li class="total"><span><?php echo $totaltasks; ?></span> tasks total</li>
                <li class="complete"><span><?php echo $completedtasks; ?></span> tasks complete</li>
                <li class="remain"><span><?php echo $allremainingtasks; ?></span> tasks remain</li>
                <li class="percent"><span><?php echo (round($inverse)) ?><em>%</em></span>progress</li>
            </ul>

        	<p class="progress">
                <span class="progress" style="width:<?php echo (round($inverse)) ?>%">
                	<em class="marker"><?php echo (round($inverse)) ?>%</em>
                </span>
                <span class="back"></span>
            </p>

        </div>

    </div>
    <!--/ End the client view /-->


    <?php } ?>


<?php get_footer(); ?>
