<li class="nav-dashboard"><a href="<?php echo home_url(); ?>/">Dashboard</a></li>
<ul>
	<?php

	global $current_user;
	get_currentuserinfo();
	$args = array( 'author' => $current_user->ID, 'taxonomy' => 'category', 'posts_per_page' => -1 );
	$get_posts = new WP_Query( $args );

	  if($get_posts->have_posts()) {
		  while($get_posts->have_posts()) { $get_posts->the_post();
			$post_categories = wp_get_post_categories( get_the_ID() );
			foreach($post_categories as $c){
				$cat = get_category( $c );
				$cats[] = $cat->name .','.get_category_link( $cat->term_id ) ;
				$i++;
			}
		} //endwhile

		$vals = sort($cats);
		$vals = array_count_values($cats);
		$all_projects = count($vals);



		echo  '<li class="nav-projects"><a href="' . home_url() . '/projects">Locations</a><span class="active-projects-count"> ' . $all_projects . ' </span>';
		echo  '<ul>';

		foreach($vals as $key => $value) {

			$cat_val = explode(",", $key);

			// Get the slug and set as a variable
			$current = $cat_val[0];
			if(is_category($current)) { $current = "current-cat"; }

			echo '<li class="' . $current . '"><a href="' . $cat_val[1] . '">' . $cat_val[0] . '</a> <span>' . $value . '</span></li>';
		}
		echo '</ul>';
		echo '</li>';

	   } //endif
	 wp_reset_postdata();
	?>
</ul>
