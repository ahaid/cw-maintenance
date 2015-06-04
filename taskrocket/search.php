<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */
get_header(); ?>

        <div class="content">
			<?php if (have_posts()) : ?>
            <h1><?php $total_results = $wp_query->found_posts; echo $total_results; ?> tasks related to <?php $allsearch = &new WP_Query("s=$s&showposts=-1"); $key = esc_html($s, 1); $count = $allsearch->post_count; _e(''); _e('<span class="term">'); echo $key; _e('</span>'); ?></h1>

            <div id="task-list" class="search-results">

                <?php while (have_posts()) : the_post(); ?>
                <div class="task border-soft roundness<?php if(  get_post_meta($post->ID, 'priority', TRUE) != '' ) { ?> task-priority-<?php echo get_post_meta($post->ID, 'priority', TRUE); ?><?php } else { ?> task-priority-normal<?php } ?>">

                    <h2>
                        <?php //If you are an administrator....
                        if (current_user_can( 'manage_options' ) ) { ?>

                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

                        <?php // ... otherwise you must be a project contributor.
                        } else { ?>

                            <?php if( get_post_meta($post->ID, 'private', TRUE) == 'yes' ) { ?>
                                Private Task (owned by an administrator)
                            <?php } else { ?>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            <?php } ?>

                        <?php } ?>
                    </h2>



                    <p class="date">
                        Added <?php echo get_the_time('jS F'); ?>

                        <?php if(  get_post_meta($post->ID, 'duedate', TRUE) != '' ) {
							// Convert old date format to new date format
							$olddateformat = get_post_meta($post->ID, 'duedate', TRUE);
							$newdateformat = new DateTime($olddateformat);
							?>

							<?php
							$date = get_post_meta($post->ID, 'duedate', TRUE); // Pull your value
							$datetime = strtotime( $date ); 				     // Convert to + seconds since unix epoch
							$yesterday = strtotime("-1 days");				  // Convert today -1 day to seconds since unix epoch
							if ( $datetime >= $yesterday ) { 				   // if date value pulled is today or later, we're overdue
								$overdue = ' class="notoverdue"';
							} else {
								$overdue = ' class="overdue"';
							}
							?>
							<span<?php echo( $overdue ); ?>><em>&rarr;</em> Due <?php echo $newdateformat->format('jS F'); ?></span>
                        <?php
							}
						?>
                    </p>

                    <?php require('include-comment-count.php'); ?>

                    <p class="project-result">
						<a href="<?php echo home_url(); ?>/projects/<?php $category = get_the_category(); echo $category[0]->category_nicename; ?>"><?php $category = get_the_category(); echo $category[0]->cat_name; ?></a>
                    </p>

                    <?php require('include-task-author.php'); ?>

                    <span class="priority"></span>

                </div>
                <?php endwhile; ?>

            </div>

            <?php else : ?>

            <h1>Oops?</h1>
            <h2 class="no-search-results">I couldn't find any tasks related to <?php /* Search Count */ $allsearch = &new WP_Query("s=$s&showposts=-1"); $key = esc_html($s, 1); $count = $allsearch->post_count; _e(''); _e('<span class="term">'); echo $key; _e('</span>'); ?>.</h2>
            <?php endif; ?>
        </div>

<?php get_footer(); ?>
