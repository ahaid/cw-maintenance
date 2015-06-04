<?php
/*
Template Name: New Task
*/
if (is_user_logged_in()) $current_user = wp_get_current_user();
get_header();
$category = get_cat_id( single_cat_title("",false) );
require_once(ABSPATH . "wp-admin/includes/taxonomy.php");
global $userdata;
get_currentuserinfo();
get_header();

// If the user role is 'client' redirect to the client page.
if( current_user_can('client')) {
	header('Location: '.home_url().'/client');
	exit();
}
$options = get_option( 'taskrocket_settings' );
?>


        <div class="content roundness add-task-page">

        	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <h1><?php the_title(); ?></h1>
            <?php endwhile; endif; ?>

            <p>Create a new maintenace task.</p>

        	<?php if($_GET['task_status'] == 'added') { // Show this message when task is added
			$taskName = $_GET['task_name'];
			?>
			<div class="message roundness green">
				<p><span class="completed-task-name">The task '<?php $str = stripslashes ($taskName); echo(str_replace("\\", '',$str)); ?>' was added to
                <?php $category = $_GET['project']; echo get_cat_name($category); ?>.
                </span></p>
                <span class="close-message"></span>
			</div>
			<?php } ?>


            <form action="<?php echo get_template_directory_uri(); ?>/add-task.php" name="taskForm" id="new_post" method="post" onsubmit="return validateForm()" enctype="multipart/form-data">
                <label for="title">Task Name <span class="required">*</span><span id="title-chars" class="chars"></span></label>
                <input type="text" id="title" name="title" class="text roundness" maxlength="100" value="" />

                <div class="category-id">
                    <label for="cat">Location</label>
                    <?php $args = array(
                        'type'                     => 'post',
                        'child_of'                 => 0,
                        'parent'                   => '',
                        'orderby'                  => 'name',
                        'order'                    => 'ASC',
                        'hide_empty'               => 0,
                        'hierarchical'             => 1,
                        'exclude'                  => '',
                        'selected' 				 => 1,
                        'include'                  => '',
                        'number'                   => '',
                        'taxonomy'                 => 'category',
                        'name'                 	=> 'categoryID',
                        'pad_counts'               => false );
                        wp_dropdown_categories( $args );
                    ?>
                </div>

                <label for="minfo">Additional information <span class="help">?</span></label>
                <p class="help-topic">Provide additional information about this task. HTML will be stripped here.</p>
                <textarea name="minfo" id="minfo" class="text textarea roundness" rows="10" cols="20"></textarea>

                <div class="task-box due-date">
                    <label>Due date</label>
                    <input type="text" class="text date" id="duedate" name="duedate" />
                </div>

                <div class="task-box radiogroup">
                    <label>Priority</label>
                    <label class="radio new-task-priority-low"><input type="radio" name="priority" value="1" id="low">Low</label>
                    <label class="radio new-task-priority-normal"><input type="radio" name="priority" value="3" id="normal" checked>Normal</label>
                    <label class="radio new-task-priority-high"><input type="radio" name="priority" value="5" id="high">High</label>
                    <label class="radio new-task-priority-urgent"><input type="radio" name="priority" value="7" id="urgent">Urgent</label>
                </div>

                <?php
                if ($options['file_uploads'] == true) { ?>
                <div class="task-box addfiles">
                    <label>Attach files</label>
                    <input type="file" name="tr_multiple_attachments[]"  multiple="multiple" />
                </div>
                <?php } ?>

                <?php if ($options['users_reassign_tasks'] == true) { ?>
                <div class="task-box assign-task-container">
                    <label for="project_contributor">Assign this task to:</label>
                    <select name="project_contributor" id="project_contributor" class="roundness">
                    <?php
                        $trusers = get_users('blog_id=1&orderby=nicename');
                        foreach ($trusers as $user) { ?>
                        <option <?php if ($user->ID == get_current_user_id()) echo 'selected';?> value="<?php echo $user->ID; ?>" id="<?php echo $user->ID; ?>">
                        <?php if ($user->first_name !== "") {
                        echo $user->first_name . " " . $user->last_name;
                        } else {
                        echo "Captain Noname";
                        }
                        ?> (<?php echo $user->user_email; ?>)</option>
                        <?php
                            }
                        ?>
                    </select>
                </div>
                <?php } else { ?>
                	<input type="hidden" name="project_contributor" id="project_contributor" value="<?php echo get_current_user_id(); ?>" />
                <?php } ?>

                <div class="clear"></div>

                <input type="submit" name="submit" class="button add-icon roundness" value="Add Task" />

                <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
                <input type="hidden" name="submitted" id="submitted" value="true" />
                <input type="hidden" name="referer" id="referer" value="new-task" />
                <?php
                if ($options['send_plain'] == true) { ?>
                <input type="hidden" name="send_plain_text_email" id="send_plain_text_email" value="yes" />
                <?php } ?>

            </form>

        </div>

<?php get_footer(); ?>
