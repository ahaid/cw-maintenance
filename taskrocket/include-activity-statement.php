<?php  if (!$options['disabled_enhanced']) { ?>

<?php wp_count_terms( 'category');
$projectCount = wp_count_terms( 'category', array( 'hide_empty' => TRUE));

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
    <?php if ($mytasks > 0) { ?>
        <p class="yours"><?php // tasks for you 
			echo "<em>" . $mytasks . "</em>";?>
        </p>
        <span class="description">Tasks for you</span>
        <div class="helper">You have <span><?php echo $mytasks; ?></span> active task<?php if ($mytasks > 1) { echo "s"; } ?>.</div>
        <?php } else { ?>
        <p class="yours no-tasks"><?php // tasks for you 
			echo "<em>" . $mytasks . "</em>";?>
        </p>
        <span class="description">Sweet!</span>
        <div class="helper">There aren't any tasks for you to do!</div>
        <?php } ?>
    </li>
    
     <li>
        <p class="load<?php if ($taskload == 0) { echo " no-load"; } ?>"><?php // load 
			echo "<em>" . round($taskload, 0) . "<span class='percentage'>%</span></em>";?>
        </p>
        <span class="description">Load carried</span>
        <div class="helper">You are reponsible for 
        <?php if ($taskload !=0) { ?>
		<span><?php echo round($taskload, 0); ?>%</span> of all tasks accross all projects.
        <?php } else { ?>
        no tasks on any projects.
        <?php } ?>
        </div>
    </li>
    
    <li>
        <p class="else<?php if ($allelse == 0) { echo " no-else"; } ?>"><?php // tasks for everyone else 
			echo "<em>" . $allelse . "</em>";?>
        </p>
        <span class="description">Tasks for everyone else</span>
        <div class="helper"> 
        
		<?php if ($allelse > 1) { ?>
        There are <span><?php echo $allelse; ?> </span> active tasks belonging to other people.
        <?php } ?>
        
        <?php  if ($allelse < 1) { ?>
        There are no active tasks for anyone.
        <?php } ?>
        
        <?php  if ($allelse == 1) { ?>
        There is <span>1</span> active task that belongs to someone else.
        <?php } ?>
        
        </div>
    </li>

    <li>
        <p class="outstanding<?php if (count($posts_array) == 0) { echo " no-outstanding"; } ?>"><?php // active tasks
			echo "<em>" . count($posts_array) . "</em>";?>
        </p>
        <span class="description">Total active tasks</span>
        
        <div class="helper">
			<?php if (count($posts_array) > 1) { ?>
                There are <span><?php echo count($posts_array); ?></span> active tasks accross all active projects.
            <?php } else { ?>
            	There are no active tasks accross all active projects.
            <?php } ?>
        </div> 
    </li>
    
    
    <li>
        <p class="active<?php if ($projectCount == 0) { echo " no-active"; } ?>"><?php // active projects 
			echo "<em>" . $projectCount . "</em>";?>
        </p>
        <span class="description">Active projects</span>
        <div class="helper">
        <?php if ($projectCount > 1) { ?>
        	There is a total of <span><?php echo $projectCount; ?></span> active projects (including unassigned).
        <?php } else { ?>
        	There are no active projects (including unassigned).
        <?php } ?>
        </div>
    </li>
    
</ul>

<?php } else { ?>

<?php wp_count_terms( 'category');
$projectCount = wp_count_terms( 'category', array( 'hide_empty' => TRUE));

// Get all posts except for uncategorized
// $args = array('posts_per_page' => -1,'category' => '1',);
$args = array('posts_per_page' => -1);
$posts_array = get_posts( $args );
?>

<p class="stats simple">
    There 
    <?php if (count($posts_array) == 1 ) { echo "is"; } else { echo "are"; }?> 
    <span><?php echo count($posts_array); ?></span> 
    active <?php if (count($posts_array) == 1 ) { echo "task"; } else { echo "tasks"; }?>. 
</p>

<?php } ?>