<?php

$currentLink = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

if (is_user_logged_in()) $current_user = wp_get_current_user();
get_header();
$category = get_cat_id( single_cat_title("",false) );

// If the user role is 'client' redirect to the client page.
if( current_user_can('client')) {
    header('Location: '.home_url().'/client');
    exit();
}
$options = get_option( 'taskrocket_settings' );
?>

<!--/ Start Content /-->
<div class="content">

    <?php if($_GET['trashed'] == '1') { // Show this message when task is completed
        $pagetitle = get_the_title( $_GET['ids'] );
    ?>
    <div class="message roundness green task-complete-message">
        <?php
            $_wpnonce = wp_create_nonce( 'untrash-post_' . $_GET['ids'] );
            $url = admin_url( 'post.php?post=' . $_GET['ids'] . '&action=untrash&_wpnonce=' . $_wpnonce );
        ?>
        <p>Task Complete &amp; Archived: <span class="completed-task-name"><?php echo strip_tags($pagetitle); ?></span> <span class="undo roundness"><a href="<?php echo $url ?>">Undo</a></span></p>
        <span class="close-message"></span>
    </div>
    <?php } ?>

    <?php if($_GET['deleted'] == '1') { // Show this message when task is deleted
        $pagetitle =  $_GET['title'] ;
    ?>
    <div class="message roundness tomato task-complete-message">
        <p>Task Permenantly Deleted:  <span class="completed-task-name"><?php echo strip_tags($pagetitle); ?></span> </p>
        <span class="close-message"></span>
    </div>
    <?php } ?>

    <?php if($_GET['untrashed'] == '1') { // Show this message when task is restored
        $pagetitle = get_the_title( $_GET['ids'] );
    ?>
    <div class="message roundness aqua task-restored-message">
        <p>Task Restored</p>
        <span class="close-message"></span>
    </div>
    <?php } ?>

    <?php if($_GET['task_status'] == 'added') { // Show this message when task is added
        $taskName = $_GET['task_name'];
    ?>
    <div class="message roundness green">
        <p>Task Added: <span class="completed-task-name"><?php $str = stripslashes ($taskName); echo(str_replace("\\", '',$str)); ?></span></p>
        <span class="close-message"></span>
    </div>
    <?php } ?>

    <h1><?php single_cat_title(); ?></h1>

    <?php if ($wp_query->found_posts < 1) { ?>
        <?php $current_category = single_cat_title("", false); if ($current_category !== "Unassigned") { // Don't show the 'all done' message if unassigned category ?>
        <div class="all-done roundness green">
            <p>Booyah! There are no active tasks here. <a href="<?php echo home_url(); ?>/projects/">What's next?</a></p>
        </div>
        <?php } ?>
    <?php } ?>

    <?php
        $categories = get_the_category(); // Get just this category
        foreach ($categories as $cat) {
        $posts = new WP_Query( 
            array(
                'post_type' => 'post', 
                'showposts' => -1, 
                'post_status' => array(
                    'trash', 
                    'publish'), 
                'cat' => $cat->cat_ID,
                'orderby' => array('post_modified' => 'DESC')
                ));

        $completedtasks = $cat->category_count;

        // This is not pretty but it works :-P
        // Let's do some math to calculate the progress percentage.

        // $alltasks is all tasks in the category including the ones in the trash.
        $alltasks = $posts->post_count;

    
    ?>

   <?php } ?>


    <div class="toolbar roundness">
        <ul class="toggle">
            <li><a class="small-button roundness light-grey all-active focused">Active Tasks</a></li>
            <li><a class="small-button roundness light-grey all-completed">Completed Tasks</a></li>
            <li class="new-task"><a class="new-task-toggle small-button roundness aqua">New Task</a></li>
        </ul>
    </div>

    <div class="add-task">
        <form action="<?php echo get_template_directory_uri(); ?>/add-task.php" name="taskForm" id="new_post" method="post" onsubmit="return validateForm()" enctype="multipart/form-data">
            <h2>New Task</h2>

            <div class="task-left">
                <p class="add-task-description">Create a new task for the <?php single_cat_title(); ?> location.</p>
                <label for="title">Task Name <span class="required">*</span><span id="title-chars" class="chars"></span></label>
                <input type="text" id="title" name="title" class="text roundness" maxlength="80" value="" />

                <label for="minfo">Additional information <span class="help">?</span></label>
                <p class="help-topic">Provide additional information about this task. HTML will be stripped here.</p>
                <textarea name="minfo" id="minfo" class="text textarea roundness" rows="10" cols="20"></textarea>
            </div>

            <div class="task-right">
                <div class="due-date">
                    <label>Due date</label>
                    <input type="text" class="text date" id="duedate" name="duedate" />
                </div>

                <div class="radiogroup">
                    <label class="priority-label">Priority</label>
                    <label class="radio new-task-priority-low"><input type="radio" name="priority" value="1" id="low">Low</label>
                    <label class="radio new-task-priority-normal"><input type="radio" name="priority" value="3" id="normal" checked>Normal</label>
                    <label class="radio new-task-priority-high"><input type="radio" name="priority" value="5" id="high">High</label>
                    <label class="radio new-task-priority-urgent"><input type="radio" name="priority" value="7" id="urgent">Urgent</label>
                </div>

                <?php
                if ($options['file_uploads'] == true) { ?>
                <div class="addfiles">
                    <label>Attach files</label>
                    <input type="file" name="tr_multiple_attachments[]"  multiple="multiple" />
                </div>
                <?php } ?>
                <div class="clear"></div>

                <input type="submit" name="submit" class="button add-icon roundness" value="Add Task" />

                <span class="cancel-task roundness">&#215;</span>
            </div>

            <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
            <input type="hidden" name="submitted" id="submitted" value="true" />
            <input type="hidden" name="categoryID" id="categoryID" value="<?php $cat_id = get_query_var('cat'); echo $cat_id; ?>" />
            <input type="hidden" name="categorySlug" id="categorySlug" value="<?php $categorySlug = get_category( get_query_var( "cat" ) ); $cat_id = $categorySlug->slug; echo $cat_id; ?>" />

        </form>
    </div>

    <div class="all-tasks-list">


    <!--/ Start All Active Tasks /-->
    <div class="task-list all-active-list">
        <?php
        $myposts = get_posts(array(
            'numberposts' => 1000,
            'offset' => 0,
            'category__in' => array($category),
            'post_status'=>'publish',
            'orderby' => array(
                            'meta_value' => 'DESC',
                            'date' => 'DESC'
                        ),
            'meta_key' => 'priority'
            )
        );
        foreach($myposts as $post) :
        setup_postdata($post);
            if ($options['show_ID'] == true) {
                $showID = '<span class="task-id" title="Task ID">[' . get_the_ID() . ']</span>';
            }
        ?>
        <div class="task border-soft roundness<?php if(  getTaskPriority($post->ID) != '' ) { echo " task-priority-" . getTaskPriority($post->ID); } else { echo " task-priority-normal"; } ?>">

            <h2>
                <?php if( date('Yz') == get_the_time('Yz') ) { echo '<span class="new roundness" title="Added Recently">new</span>';}?>

				<?php if ($post->post_author == $current_user->ID || current_user_can( 'edit_others_posts' )){ // If the post author is the current user or an administrator ?>
                <label class="checkbox-label"><input type="checkbox" class="checkbox" title="Mark this task as complete" onclick="location.href='<?php echo get_delete_post_link( get_the_ID() ); ?>'" /></label>
                <?php } ?>

                <?php //If you are an administrator....
                if (current_user_can( 'manage_options' ) ) { ?>

                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <?php echo $showID; ?>

                <?php // ... otherwise you must be a project contributor.
                } else { ?>

                    <?php if( get_post_meta($post->ID, 'private', TRUE) == 'yes' ) { ?>
                        Private Task (owned by an administrator)
                    <?php } else { ?>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <?php echo $showID; ?>
                    <?php } ?>

                <?php } ?>
            </h2>

            <em class="date">

                <?php if( get_post_meta($post->ID, 'private', TRUE) == 'yes' ) { ?>
                    <span class="private-task roundness">Private</span>
                <?php } ?>

                Added <?php echo get_the_time('jS F'); ?>

                <?php if(  get_post_meta($post->ID, 'duedate', TRUE) != '' ) {
                    // Convert old date format to new date format
                    $olddateformat = get_post_meta($post->ID, 'duedate', TRUE);
                    $newdateformat = new DateTime($olddateformat);
                ?>

                    <?php
                    $date = get_post_meta($post->ID, 'duedate', TRUE); // Pull your value
                    $datetime = strtotime( $date );                    // Convert to + seconds
                    $yesterday = strtotime("-1 days");                 // Convert today -1 day to seconds
                    if ( $datetime >= $yesterday ) {                   // if date value pulled is today or later, overdue
                        $overdue = ' class="notoverdue"';
                    } else {
                        $overdue = ' class="overdue"';
                    }
                    ?>
                    <span<?php echo( $overdue ); ?>><em>&rarr;</em> Due
                    <?php echo $newdateformat->format('jS F'); ?>
                    </span>
                <?php } ?>
                <?php require('include-comment-count.php'); ?>
            </em>

            <span class="priority">
                <?php if(  getTaskPriority($post->ID) != '' ) { ?>
                <em class="priority roundness <?php echo getTaskPriority($post->ID); ?>" title="<?php echo getTaskPriority($post->ID); ?> priority"><?php echo getTaskPriority($post->ID); ?> Priority</em>
                <?php } else { ?>
                <em class="priority roundness normal" title="normal priority">Normal Priority</em>
                <?php } ?>
            </span>

            <?php //If you are an administrator....
            if (current_user_can( 'manage_options' ) ) { ?>

                <?php if( get_post_meta($post->ID, 'minfo', TRUE) != '' ) { // If there is content... ?>
                <!--/ Start Options /-->
                <div class="options">
                    <a class="roundness show-more show-more-<?php if(  getTaskPriority($post->ID) != '' ) { echo getTaskPriority($post->ID); } else { echo "normal"; } ?> linky">Show More</a>
                    <div class="more roundness">
                        <pre>
    <?php echo strip_tags(get_post_meta($post->ID, 'minfo', TRUE)); ?>
                        </pre>
                    </div>
                </div>
                <!--/ End Options /-->
                <?php } ?>

                <?php require('include-attachments.php'); ?>

            <?php // ... otherwise you must be a project contributor.
            } else { ?>

                <?php if( get_post_meta($post->ID, 'private', TRUE) == 'yes' ) { ?>

                <?php } else { ?>

                    <?php if( get_post_meta($post->ID, 'minfo', TRUE) != '' ) { // If there is content... ?>
                    <!--/ Start Options /-->
                    <div class="options">
                        <a class="roundness show-more show-more-<?php if(  getTaskPriority($post->ID) != '' ) { echo getTaskPriority($post->ID); } else { echo "normal"; } ?> linky">Show More</a>
                        <div class="more roundness">
                            <pre>
        <?php echo strip_tags(get_post_meta($post->ID, 'minfo', TRUE)); ?>
                            </pre>
                        </div>
                    </div>
                    <!--/ End Options /-->
                    <?php } ?>

                    <?php require('include-attachments.php'); ?>

                <?php } ?>

            <?php } ?>

        </div>
        <?php endforeach; ?>
        <?php wp_reset_postdata(); ?>
    </div>
    <!--/ End All Active Tasks /-->



    <!--/ Start All Completed Tasks /-->
    <div class="task-list completed-list all-completed-list">

    <?php
        $myposts = get_posts(array('numberposts' => 1000, 'offset' => 0, 'category__in' => array($category), 'post_status'=>'trash', 'orderby' => $_COOKIE['OrderBy'], 'order' => $_COOKIE['Order'] ));
        foreach($myposts as $post) :
        setup_postdata($post);
        if ($options['show_ID'] == true) {
            $showID = '<span class="task-id" title="Task ID">[' . get_the_ID() . ']</span>';
        }
        ?>

        <?php
            $_wpnonce = wp_create_nonce( 'untrash-post_' . get_the_ID() );
            $restore = admin_url( 'post.php?post=' . get_the_ID() . '&action=untrash&_wpnonce=' . $_wpnonce );
        ?>
        <div class="task border-soft roundness completed">

            
            <h3>

            	<?php if ($options['admins_complete_tasks'] == true) { ?>

					<?php if ($post->post_author == $current_user->ID || current_user_can( 'manage_options' )){ // If the post author is the current user or an administrator ?>
                    <label class="checkbox-label"><input type="checkbox" class="checkbox" title="Mark this task as incomplete" onclick="location.href='<?php echo $restore; ?>';" checked="checked" /></label>
                    <?php } ?>

                <?php } else { ?>

                	<?php if ($post->post_author == $current_user->ID){ // If the post author is the current user ?>
                    <label class="checkbox-label"><input type="checkbox" class="checkbox" title="Mark this task as incomplete" onclick="location.href='<?php echo $restore; ?>';" checked="checked" /></label>
                    <?php } ?>

                <?php } ?>

                <?php //If you are an administrator....
                if (current_user_can( 'manage_options' ) ) { ?>

                    <?php the_title(); ?><?php echo $showID; ?>

                <?php // ... otherwise you must be a project contributor.
                } else { ?>

                    <?php if( get_post_meta($post->ID, 'private', TRUE) == 'yes' ) { ?>
                        Private Task (owned by an administrator)
                    <?php } else { ?>
                        <?php the_title(); ?> <?php echo $showID; ?>
                    <?php } ?>

                <?php } ?>
            </h3>
            <em class="date">

                <?php if( get_post_meta($post->ID, 'private', TRUE) == 'yes' ) { ?>
                    <span class="private-task roundness">Private</span>
                <?php } ?>

                Completed <?php echo the_modified_date(); ?>
            </em>

        </div>
        <?php endforeach; ?>
        <?php wp_reset_postdata(); ?>
    </div>
    <!--/ End All Completed Tasks /-->

    </div>

</div>
<!--/ End Content /-->
<iframe id="deletey" name="deletey" width="0" height="0" frameborder="0"></iframe>


<?php get_footer(); ?>
