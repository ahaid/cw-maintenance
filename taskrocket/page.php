<?php
get_header();
wp_enqueue_script( 'comment-reply' );

// If the user role is 'client' redirect to the client page.
if( current_user_can('client')) {
	header('Location: '.home_url().'/client');
	exit();
}
$options = get_option( 'taskrocket_settings' );
?>

        <div class="content roundness user-content<?php if ( has_post_thumbnail() ) { echo " user-content-padding"; } ?><?php if ($options['comments_side'] == true) { echo ' task-solo-comments-right'; } ?>">
        	<?php if ( has_post_thumbnail() ) { ?>
					<div class="header-image">
                        <h1><?php the_title(); ?></h1>
                        <?php the_post_thumbnail(); ?>
					</div>
			<?php
				}
			?>

            <div class="main-content">
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <?php if ( !has_post_thumbnail() ) { ?>
                	<h1><?php the_title(); ?></h1>
				<?php } ?>

                <?php the_content(); ?>
                <?php endwhile; endif; ?>
            </div>

            <?php
			if ($options['allow_comments_pages'] == true) { ?>
				<!--/ Start Comments /-->
			    <div id="comment-area">
					<?php if ($options['comments_side'] == true) { echo '<div class="comment-pos">'; } ?>
			        <?php comments_template( 'comments.php' ); ?>
					<?php if ($options['comments_side'] == true) { echo '</div>'; } ?>
			    </div>
			    <!--/ End Comments /-->
			<?php } ?>

        </div>

<?php get_footer(); ?>
