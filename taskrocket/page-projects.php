<?php
/*
Template Name: Projects
*/
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

    <p class="stats-text">
        There
        <?php if (count($posts_array) == 1 ) { echo "is"; } else { echo "are"; }?>
        <span><?php echo count($posts_array); ?></span>
        active <?php if (count($posts_array) == 1 ) { echo "task"; } else { echo "tasks"; }?>
        over
        <span><?php echo $projectCount ?></span>
        active <?php if ($projectCount == 1 ) { echo "project"; } else { echo "projects"; }?> (including all unassigned).
        <span><?php echo count_user_posts( $user_ID );?></span>  of those tasks are yours.
    </p>

	<div class="all-active-projects">

    	<script>
			// Reload page after deleteing project
			function reload() {
				setTimeout("location.reload()", 1000)
			}
        </script>

		<?php
            $categories = get_categories('hide_empty=0&order=ASC'); // Get all categories
            foreach ($categories as $cat) {
            $posts = new WP_Query( array('post_type' => 'post', 'showposts' => -1, 'post_status' => array('trash', 'publish'), 'cat' => $cat->cat_ID));

            $completedtasks = $cat->category_count;

            if ( $cat->cat_name == 'Unassigned' ) {
                $catclass = " unassigned";
            }

            // This is not pretty but it works :-P
            // Let's do some math to calculate the progress percentage.

            // $alltasks is all tasks in the category including the ones in the trash.
            $alltasks = $posts->post_count;

            // $remainingtasks is $alltasks minus the $completedtasks (this gives us the difference).
            $remainingtasks = $alltasks - $completedtasks;

            // But there's a problem: division by zero! Let's "fix" it!
            if ($remainingtasks != 0) {
                $percentagecomplete = 100 * $completedtasks / $alltasks;
                $inverse = 100 - $percentagecomplete;
            } else {
                $inverse = 0;
            }

            // Colours for the progress bars.
            if ($inverse <= 25) {
                $colour = "f99874";
                    } else
                if ($inverse > 25 && $inverse <= 50) {
                    $colour = "f8b974";
                    } else
                if ($inverse > 50 && $inverse <= 75) {
                $colour = "eddb74";
                    } else
                if ($inverse > 75) {
                $colour = "aee189";
            }
        ?>

        <div class="project roundness<?php echo $catclass ?>">
            <h2><a href="<?php echo $cat->category_nicename; ?>"><?php echo $cat->cat_name; ?></a></h2>
            <p class="project-desc"><?php echo $cat->category_description; ?></p>

            <?php $options = get_option( 'taskrocket_settings' );
                   if ($options['delete_projects'] == true || current_user_can( 'manage_options' )) {  ?>
            <?php if ( $cat->cat_name !== 'Unassigned' ) { ?><span class="show-delete" title="Delete this project">&#215;</span><?php } ?>
            <div class="deleter roundness">
                <div>
                    <p>Really delete the <?php echo $cat->cat_name; ?> project?</p>
                    <a href="<?php echo wp_nonce_url(home_url() . "/wp-admin/edit-tags.php?action=delete&taxonomy=category&tag_ID=".$cat->cat_ID."", 'delete-tag_' . $cat->cat_ID);?>" class="confirm-delete roundness" target="deletey" onClick="reload()">Yep, do it!</a>
                    <span class="hide-delete roundness">No!</span>
                </div>
            </div>
            <?php } ?>
            <?php if ($inverse >= 100) { ?>
            <p class="status complete-text">All tasks complete!</p>
            <?php } else { ?>
                <?php if ( $cat->cat_name == 'Unassigned' ) { // If unassigned ?>
                <span class="percent"><?php echo $completedtasks ?></span>
                <p class="status"><?php echo $completedtasks; ?> <?php if ($completedtasks == 1 ) { echo "task is"; } else { echo "tasks are "; }?>  unassigned.</p>
                <?php } else { ?>
                <span class="percent"><?php echo (round($inverse)) ?><span>%</span></span>
                <p class="status"><?php echo $remainingtasks; ?> of <?php echo $alltasks; ?> complete<?php if ($inverse < 100) { ?>, <?php echo $completedtasks; ?> active.<?php } ?></p>
                <?php } ?>
            <?php } ?>

            <?php if ( $cat->cat_name !== 'Unassigned' ) { // If unassigned ?>
            <div class="progress">
                <div style="width:<?php echo $inverse; ?>%; background:#<?php echo $colour; ?>" class="roundness"></div>
            </div>
            <?php } ?>

        </div>

	<?php }
    ?>
    </div>

<iframe id="deletey" name="deletey" width="0" height="0" frameborder="0"></iframe>
</div>

<?php get_footer(); ?>
