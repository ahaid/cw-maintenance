<?php
/*
Template Name: Report
*/

require_once(ABSPATH . "wp-admin/includes/taxonomy.php");

get_header();
global $wp;
$current_url = home_url(add_query_arg(array(),$wp->request));
// If the user role is 'client' redirect to the client page.
if( current_user_can('client')) {
	header('Location: ' . home_url() . '/client');
	exit();
}
$options = get_option( 'taskrocket_settings' );
?>

		<?php // If the current user is an administrator...
		if($options['show_report_to_all'] == true || current_user_can( 'manage_options' )) { ?>

	        <div class="content report">
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	            <h1><?php the_title(); ?></h1>
	            <?php endwhile; endif; ?>

				<div class="report-container">
					<h2>Summary</h2>

					<?php wp_count_terms( 'category');
					$projectCount = wp_count_terms( 'category', array( 'hide_empty' => TRUE));
					$allProjectCount = wp_count_terms( 'category', array( 'hide_empty' => FALSE));
					$completedProjects = $allProjectCount - $projectCount;

					// Get all posts except for uncategorized
					// $args = array('posts_per_page' => -1,'category' => '1',);
					$args = array('posts_per_page' => -1);
					$posts_array = get_posts( $args );
					$alltasks = count($posts_array);
					$allelse = $alltasks - count_user_posts( $user_ID );
					$mytasks = count_user_posts( $user_ID );

					// Fix division by zero
					if ($mytasks != 0) {
						$taskload = 100 * $mytasks / $alltasks;
						$inverse = 100 - $taskload;
					} else {
						$inverse = 0;
					}

					?>

					<ul class="stats roundness">

						<li>
					        <p class="active"><?php // active projects
								echo "<em>" . $projectCount . "</em>";?>
					        </p>
					        <span class="description">Active projects</span>

					    </li>

						<li>
					        <p class="yours"><?php // completed projects
								echo "<em>" . $completedProjects . "</em>";?>
					        </p>
					        <span class="description">Completed projects</span>

					    </li>

					    <li>
					        <p class="outstanding"><?php // outstanding tasks
								echo "<em>" . count($posts_array) . "</em>";?>
					        </p>
					        <span class="description">Total active tasks</span>
					    </li>

					</ul>

					<?php if ($options['show_users_in_report'] == true) { ?>
						<h2>Users</h2>
						<?php require('include-users.php'); ?>
					<?php }?>


					<h2>Project Progress</h2>
					<?php require('include-chart.php'); ?>

					<div class="breakdown">
						<h2>Task Breakdown</h2>
						<?php // Show all projects and tasks.
						$cat_args=array(
							'orderby' => 'name',
							'order' => 'ASC'
						);
						$categories=get_categories($cat_args);
						foreach($categories as $category) {
						$args=array(
							'showposts' => -1,
							'category__in' => array($category->term_id),
							'caller_get_posts'=>1
						);
						$posts=get_posts($args);
						$catcount = $category->category_count;
						if($catcount == 1) {
							$grammar = " Task Remains";
						} else {
							$grammar = " Tasks Remain";
						}
						if ($posts) {
							echo "<h3><a href='" . home_url() . "/projects/" . $category->category_nicename . "'>" . $category->name . " <span>" . $category->category_count . $grammar . "</span></a></h3>\n"; ?>

							<div class="table-scroll">
								<table border="0" cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="table-task" align="left">Task</th>
										<th class="table-date-added" align="left">Date Added</th>
										<th class="table-due-date" align="left">Due Date</th>
										<th class="table-priority" align="left">Priority</th>
										<th class="table-owner" align="left">Owner</th>
									</tr>
								</thead>

								<?php foreach($posts as $post) {
									setup_postdata($post);
								?>

							<tr>

								<td style="task-name">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									<?php if( get_post_meta($post->ID, 'private', TRUE) == 'yes' ) { ?>
										<span class="private-task roundness">Private</span>
									<?php } ?>
								</td>

								<td>
									<?php echo get_the_time('jS F'); ?>
								</td>

								<td>
					                <?php if(  get_post_meta($post->ID, 'duedate', TRUE) != '' ) {
					                    // Convert old date format to new date format
					                    $olddateformat = get_post_meta($post->ID, 'duedate', TRUE);
					                    $newdateformat = new DateTime($olddateformat);
					                ?>

					                <?php
						                $date = get_post_meta($post->ID, 'duedate', TRUE); // Pull your value
						                $datetime = strtotime( $date );                      // Convert to + seconds since unix epoch
						                $yesterday = strtotime("-1 days");                // Convert today -1 day to seconds since unix epoch
					                if ( $datetime >= $yesterday ) {                   // if date value pulled is today or later, we're overdue
					                    $overdue = ' class="notoverdue"';
					                } else {
					                    $overdue = ' class="overdue" title="This task is OVERDUE"';
					                }
					                ?>
					                    <span<?php echo( $overdue ); ?>>
					                    <?php echo $newdateformat->format('jS F'); ?>
					                    </span>
					                <?php } ?>
								</td>

								<td class="priority">
									<?php $priority = get_post_meta($post->ID, 'priority', TRUE);
									if($priority == "low") {
										echo '<span class="low">Low</span>';
									}
									if($priority == "normal") {
										echo '<span class="normal">Normal</span>';
									}
									if($priority == "high") {
										echo '<span class="high">High</span>';
									}
									if($priority == "urgent") {
										echo '<span class="urgent">Urgent</span>';
									}
									?>
								</td>

								<td class="name">
									<?php if (get_the_author_meta( 'first_name') !== "" ) {
								    echo get_the_author_meta( 'first_name' ) . " " . get_the_author_meta( 'last_name' );
								    } else {
								    echo "Captain Noname";
								    }
								    ?>
								</td>

							</tr>

							<?php
									}
								} ?>
							</table>
						</div>
					<?php }
						?>
					</div>

				</div>

	        </div>

		<?php } ?>

<?php get_footer(); ?>
