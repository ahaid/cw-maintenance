<div class="my-recent-tasks">
	<?php if($options['recent_tasks'] !== '') {
		$recentTaskCount = $options['recent_tasks'];
	} else {
		$recentTaskCount = 10;
	}?>
	<h2>Active tasks</h2>

	<ul class="recent">
	<?php
	$author_query = array(
		'posts_per_page' => $recentTaskCount,
		'meta_key' => 'priority',
        'orderby' => array(
				        'meta_value' => 'DESC',
				        'date' => 'DESC'
				    ),
	);//,'author' => $current_user->ID);
	$author_posts = new WP_Query($author_query);


	if (sizeof($author_posts) == 0) { ?>

			<p>There are no tasks yet. 

			<?php if (get_currentuser_role() != 'unknown'){ ?>
					<a href="<?php echo home_url(); ?>/new-task/">Create one?</a></p>
			<?php } ?>
			
	<?php } else { 	

		while($author_posts->have_posts()) : $author_posts->the_post();
		?>
			<li class="home-task <?php if( getTaskPriority($post->ID) != '' ) { echo " priority-" . getTaskPriority($post->ID); } else { echo " priority-normal"; } ?>">
				<?php if( date('zY') == get_the_time('zY') ) { echo '<span class="new roundness" title="Added Recently">new</span>';}?>
				<strong title="<?php echo getTaskPriority($post->ID); ?> priority"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong>
				<span class="priority"><?php echo getTaskPriority($post->ID); ?> priority</span>
				<?php $category = get_the_category(); echo '<a href="'.get_category_link($category[0]->term_id ).'" class="project-name">'.$category[0]->cat_name.'</a>'; ?>
				<?php require('include-comment-count.php'); ?>
				<?php if ($post->post_author == $current_user->ID || current_user_can( 'edit_others_posts' )){ // If the post author is the current user or an administrator ?>
                <label class="checkbox-label"><input type="checkbox" class="checkbox" title="Mark this task as complete" onclick="location.href='<?php echo get_delete_post_link( get_the_ID() ); ?>'" /></label>
                <?php } ?>
			</li>
		<?php
		endwhile;
		?>
		</ul>

	<?php } ?>

</div>
