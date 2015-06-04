<?php $options = get_option( 'taskrocket_settings' ); ?>
<?php if ($options['hide_chart'] == false) { ?>
<div class="chart">

	<?php if(is_home()) { ?>
        <h2>Progress of all active projects</h2>
        <?php if ($projectCount == 0) { ?>
            <p>There are no active projects. 
            <?php if ($options['users_create_projects'] == true) { ?><a href="<?php echo home_url(); ?>/new-project/">Create one?</a><?php } ?>
            </p>
        <?php } ?>
    <?php } ?>
    
    <ul>   
    <?php
	$categories = get_categories('hide_empty=0&order=ASC'); // Get all categories
        foreach ($categories as $category) { 
        $query = new WP_Query( array('post_type' => 'post', 'showposts' => -1, 'post_status' => array('trash', 'publish'), 'cat' => $category->cat_ID));
    
        $completedtasks = $category->category_count;
        
        if ( $category->cat_name == 'Unassigned' ) {
            $catclass = " unassigned";
        }
        
        // This is not pretty but it works :-P
        // Let's do some math to calculate the progress percentage.
        
        // $alltasks is all tasks in the category including the ones in the trash.
        $alltasks = $query->post_count;
        
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
        } ?>
        
			<?php if ($completedtasks > 0) { // Only show if there is at least one task ?> 
    		<li title="<?php echo $remainingtasks; ?> of <?php echo $alltasks; ?> complete (<?php echo $completedtasks; ?> active)"<?php if ( $category->cat_name == 'Unassigned' ) { echo ' class="unassigned"'; } ?>>
			<a class="chart-item" href="<?php echo get_category_link($category->cat_ID); ?>">
            
            <?php if ( $category->cat_name == 'Unassigned' ) { // If unassigned ?>
            	<span class="project"><?php echo $category->cat_name; ?></span>
                <span class="unassigned-remaining"><?php echo $completedtasks; ?></span>
            <?php } else { ?>
            	<span class="percent"><?php echo round($inverse); ?>%</span>
				<span class="project"><?php echo $category->cat_name; ?></span>
				<span class="bar" style="width:<?php echo $inverse; ?>%; background:#<?php echo $colour; ?>"></span>
            <?php } ?>
            </a></li>
            <?php } ?>
	
	<?php  } ?>
    </ul>
</div>
<?php } ?>