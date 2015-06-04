<div class="extra-nav">

<?php if ($options['dropdown_nav_label'] == "") { ?>
	<div class='dropdown-nav-label'>Pages</div>
<?php } else { ?>
	<div class='dropdown-nav-label'><?php echo $options['dropdown_nav_label']; ?></div>
<?php } ?>



<?php if ($options['page_nav_dropdown'] == true) {  ?>
        <?php // Number of pages
        $tr_page = wp_count_posts('page');
        $tr_page_publish = $tr_page->publish;
        $tr_page_drafts = $tr_page->draft;
        $tr_page_total = $tr_page_publish - 7;
        if ($tr_page_publish > 7) {
        ?>

        <div class="selection-list">
            <select id="selectnav" class="roundness">
                <option></option>
                <?php // Exclude these pages
	                $account = get_page_by_title('Account');
	                $newproject = get_page_by_title('New Project');
					$newtask = get_page_by_title('New Task');
	                $projects = get_page_by_title('Projects');
	                $support = get_page_by_title('Support');
					$users = get_page_by_title('Users');
					$report = get_page_by_title('Report');
					$client = get_page_by_title('Client');
	                $current = $post->ID;

	                $exclude = array(
	                $account->ID,
	                $newproject->ID,
					$newtask->ID,
	                $projects->ID,
	                $support->ID,
					$client->ID,
					$report->ID,
					$users->ID
                );

                $args = array(
	                'post__not_in' => $exclude,
	                'post_type' => 'page',
	                'posts_per_page' => -1,
	                'order' => 'asc'
                );

                $pages_query = new WP_Query($args);
                while ($pages_query->have_posts()) : $pages_query->the_post();?>
                <option value="<?php the_permalink(); ?>"<?php if ( $current == $post->ID ) echo ' selected="selected"'; ?>><?php the_title(); ?></option>
                <?php endwhile; wp_reset_query(); ?>
            </select>
        </div>

        <script>
            $("#selectnav").change(function(){
                if ($(this).val()!='') {
                window.location.href=$(this).val();
              }
            });
        </script>

 <?php }

 } else { ?>
    <?php // Number of pages
        $tr_page = wp_count_posts('page');
        $tr_page_publish = $tr_page->publish;
        $tr_page_drafts = $tr_page->draft;
        $tr_page_total = $tr_page_publish - 7;
        if ($tr_page_publish > 7) {
        ?>
     <ul class="nav custom-nav-list">
        <?php
	        $account = get_page_by_title('Account');
	        $newproject = get_page_by_title('New Project');
			$newtask = get_page_by_title('New Task');
	        $projects = get_page_by_title('Projects');
	        $support = get_page_by_title('Support');
			$users = get_page_by_title('Users');
			$report = get_page_by_title('Report');
			$client = get_page_by_title('Client');
	        wp_list_pages('title_li=&sort_column=title&hierarchical=0&depth=3&exclude=' . $account->ID . ',' . $client->ID . ',' . $users->ID . ',' . $report->ID . ',' . $newproject->ID . ',' . $newtask->ID . ',' .  $projects->ID . ',' . $support->ID . ''
		); ?>
    </ul>

<?php } } ?>
</div>
