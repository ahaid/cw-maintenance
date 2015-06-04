<?php
get_currentuserinfo();
if (is_user_logged_in()) $current_user = wp_get_current_user();

$category = get_cat_id( single_cat_title("",false) );


if( get_post_meta($post->ID, 'private', TRUE) == 'yes' && (!current_user_can( 'manage_options' ) ) ) {
	echo '<div style="padding:20px;text-align:center; font-family:arial; font-size:14px; color:#606777; background:#f3f3e7;">This is a private task owned by an administrator.</div>';
	exit;
}

if(isset($_POST['submitted']) && isset($_POST['post_nonce_field']) && wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')) {

	
		if(trim($_POST['title']) === '') {
		$postTitleError = 'Please enter a title.';
		$hasError = true;
	}

	if (!$hasError) {

		$postTitle = trim($_POST['title']);
		$postContent = $_POST["minfo"];
		$duedate = $_POST["duedate"];
		$priority = $_POST["priority"];
		$previousowner = $_POST["previousowner"];
		$project_contributor = $_POST["project_contributor"];
		$private = $_POST["private"];

		$post_id = get_the_ID();
		$my_post['ID'] = $post_id;

		$template_dir = get_bloginfo('template_directory');
		$my_post = array();
		$my_post['post_author'] = $project_contributor;
		$my_post['post_status'] = 'publish';
		$my_post['post_title'] = $postTitle;
		$my_post['post_content'] = $postContent;
		$my_post['previousowner'] = $previousowner;
		$my_post['filter'] = true;
		$my_post['post_name'] = str_replace(' ', '-', ''); // Need to redirect to category or updated permalink
		wp_update_post( $my_post);

		// Update the custom fields
		update_post_meta( $post_id, 'duedate', $duedate);
		update_post_meta( $post_id, 'private', $private);
		update_post_meta( $post_id, 'priority', $priority);
		update_post_meta( $post_id, 'minfo', $postContent);
		update_post_meta( $post_id, 'previousowner', $previousowner);
		update_post_meta( $post_id, '_updated', 'yes');

		// Update the category
		wp_set_object_terms( $post_id, intval( $_POST['cat'] ), 'category', false );

		// Upload file(s)
		if ( $_FILES ) {
		$files = $_FILES["tr_multiple_attachments"];
		foreach ($files['name'] as $key => $value) {
				$pid = $post_id;
				if ($files['name'][$key]) {
					$file = array(
						'name' => $files['name'][$key],
						'type' => $files['type'][$key],
						'tmp_name' => $files['tmp_name'][$key],
						'error' => $files['error'][$key],
						'size' => $files['size'][$key]
					);
					$_FILES = array ("tr_multiple_attachments" => $file);
					foreach ($_FILES as $file => $array) {
						$newupload = tr_handle_attachment($file,$pid);
					}
				}
			}
		}
		if ($_POST['notify'] == 'yes') {
			sendTaskEmail($post_id, 'assigned', null);
		}
        
		wp_redirect( get_permalink( $post_id ) );exit;
	}
}
get_header();
wp_enqueue_script( 'comment-reply' );

// If the user role is 'client' redirect to the client page.
if( current_user_can('client')) {
	header('Location: '.home_url().'/client');
	exit();
}
$options = get_option( 'taskrocket_settings' );
?>

<div class="content task-solo roundness<?php if ($options['comments_side'] == true) { echo ' task-solo-comments-right'; } ?>">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <h1><?php the_title(); ?></h1>

    <?php if ($hasError) {?>
    <div class="message roundness orange">
    	<p>Sorry, but the task name field can't be empty.</p>
        <span class="close-message"></span>
    </div>

    <?php }
    elseif ($post_id || get_post_meta($post->ID, '_updated', TRUE) == 'yes') {
    ?>
    <div class="message roundness green">
    	<p>Your task was updated.</p>
        <span class="close-message"></span>
    </div>

    <?php }
    update_post_meta( $post->ID, '_updated', 'false' ) ;
    ?>

   	<div class="toolbar roundness">

    	<?php foreach((get_the_category()) as $category) { ?>
        <?php if ($_GET['source'] == 'search') { ?>
            <a href="javascript:history.back()" class="action">&larr; Go back to search results</a>
        <?php } else { ?>
        <a href="<?php echo get_category_link($category->cat_ID);?>" class="action">&larr; <?php echo $category->cat_name; ?> Task List</a>
        <?php } ?>
		<?php
        	}
        ?>

    	<?php if ($post->post_author == $current_user->ID || current_user_can( 'edit_others_posts' )){ // If the post author is the current user ?>
        <a class="small-button roundness aqua linky show-edit-toggle">Edit Task</a>
        <?php } ?>

        <a href="javascript:window.print()" class="small-button roundness grey print">Print</a>
        <div class="clear"></div>

    </div>

        <div class="edit-task <?php echo getTaskPriority($post->ID); ?>">
         <form action="" id="new_post" method="post" enctype="multipart/form-data">

            <label for="title">Task Name <span class="required">*</span> <span id="title-chars" class="chars"></span></label>
            <input type="text" id="title" maxlength="80" name="title" class="text roundness" value="<?php if ($postTitle) echo $postTitle; else the_title(); ?>" />

            <label for="minfo">Additional information <span class="help">?</span></label>
            <p class="help-topic">Provide additional information about this task. All HTML will be stripped here.</p>
            <textarea name="minfo" id="minfo" class="text textarea roundness" rows="10" cols="20"><?php  echo get_post_meta($post->ID, 'minfo', TRUE); ?></textarea>

            <div class="due-date">
                <label for="duedate">Due date</label>
                <input type="text" class="text date" id="duedate" name="duedate" value="<?php if ($duedate) echo $duedate; else echo get_post_meta($post->ID, 'duedate', TRUE);// the_time('F j, Y'); ?>" />
           	</div>

            <div class="radiogroup">
                <label>Priority</label>
                <label class="radio new-task-priority-low"><input type="radio" name="priority" value="1" id="low" <?php if(  get_post_meta($post->ID, 'priority', TRUE) == '1' ) { echo "checked";}?>>Low</label>
                <label class="radio new-task-priority-normal"><input type="radio" name="priority" value="3" id="normal" <?php if(  get_post_meta($post->ID, 'priority', TRUE) == '3' ) { echo "checked";}?>>Normal</label>
                <label class="radio new-task-priority-high"><input type="radio" name="priority" value="5" id="high" <?php if(  get_post_meta($post->ID, 'priority', TRUE) == '5' ) { echo "checked";}?>>High</label>
                <label class="radio new-task-priority-urgent"><input type="radio" name="priority" value="7" id="urgent" <?php if(  get_post_meta($post->ID, 'priority', TRUE) == '7' ) { echo "checked";}?>>Urgent</label>
            </div>

            <?php
				if ($options['file_uploads'] == true) { ?>
				<div class="addfiles">
					<label>Attach <?php if ( $attachments ) { echo " more "; } ?>files</label>
					<input type="file" name="tr_multiple_attachments[]"  multiple="multiple" />
				</div>
            <?php } ?>

            <div class="project-hierarchy">
                <label for="parent">Location</label>
                <?php $categories = get_the_category();
					  $category_id = $categories[0]->cat_ID;?>
                <?php wp_dropdown_categories( "show_count=1&hierarchical=1&orderby=name&order=ASC&selected=" . $category_id . "&hide_empty=0&show_option_all=None" ); ?>


            	


            </div>

            <div class="clear"></div>

        <input type="submit" name="submit" class="button update-icon roundness" value="Update Task" />

        <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
        <input type="hidden" name="submitted" id="submitted" value="true" />
        <?php
		if ($options['send_plain'] == true) { ?>
		<input type="hidden" name="send_plain_text_email" id="send_plain_text_email" value="yes" />
		<?php } ?>
        <input type="hidden" name="previousowner" id="previousowner" value="<?php echo $current_user->user_firstname . " " . $current_user->user_lastname; ?>" />
    </form>
    </div>

    <ul class="info-box roundness">
        <li><strong>Location:</strong> <a href="<?php echo get_category_link($category->cat_ID);?>"><?php echo $category->cat_name; ?></a></li>
        <li><strong>Added:</strong> <?php echo get_the_time('jS F'); ?> <?php if( date('Yz') == get_the_time('Yz') ) { echo '(<span class="new roundness" title="Added Today">new</span>)';}?></li>
        <?php if(get_post_meta($post->ID, 'duedate', TRUE) != '' ) { ?>
        <li><strong>Due:</strong>

        <?php if(  get_post_meta($post->ID, 'duedate', TRUE) != '' ) {
             // Convert old date format to new date format
            $olddateformat = get_post_meta($post->ID, 'duedate', TRUE);
            $newdateformat = new DateTime($olddateformat);
            ?>
            <?php
            $date = get_post_meta($post->ID, 'duedate', TRUE); // Pull the value
            $datetime = strtotime( $date ); 				     // Convert to + seconds
            $yesterday = strtotime("-1 days");				  // Convert today -1 day to seconds
            if ( $datetime >= $yesterday ) { 				   // If date value pulled is today or later, it's overdue
                $overdue = ' class="notoverdue"';
            } else {
                $overdue = ' class="overdue"';
            }
			if ($options['show_ID'] == true) {
				$showID = get_the_ID();
			}
            ?>
            <span<?php echo( $overdue ); ?>>
            <?php echo $newdateformat->format('jS F'); ?>
            </span>
        <?php } ?></li>

        <?php } ?>
        <li><strong>Priority:</strong>
        <?php if(getTaskPriority($post->ID) != '' ) { ?>
        <span class="capitalize <?php echo getTaskPriority($post->ID); ?>"><?php echo getTaskPriority($post->ID); ?></span>
        <?php } else { ?>
        <span class="normal capitalize">Normal</span>
        <?php } ?>
        </li>
        <li><strong>Reported By:</strong>
        <?php if(get_post_meta($post->ID, 'reporter_email', TRUE) != '' ) { ?>
        <?php echo get_post_meta($post->ID, 'reporter_email', TRUE); ?>
        <?php } else { ?>
        Unknown
        <?php } ?>
        </li>
        <?php if ($options['show_ID'] == true) {
				$showID = get_the_ID(); ?>
        <li><strong>Task ID:</strong><?php echo $showID; ?></li>
        <?php } ?>

		<?php if( get_post_meta($post->ID, 'private', TRUE) == 'yes' ) { ?>
		<li><strong>Visibility:</strong>Private</li>
		<?php } else { ?>
			<li><strong>Visibility:</strong>Public</li>
		<?php } ?>


    </ul>

    <?php require_once('include-attachments.php'); ?>

    <?php if(  get_post_meta($post->ID, 'minfo', TRUE) != '' ) { // If there is content... ?>
    <h3>Additional Information</h3>
    <div class="task-alone roundness border-soft">
        <pre>
<?php echo get_post_meta($post->ID, 'minfo', TRUE); ?>
        </pre>

        <span class="priority">
            <?php if(  getTaskPriority($post->ID) != '' ) { ?>
            <em class="priority roundness <?php echo getTaskPriority($post->ID); ?>" title="<?php echo getTaskPriority($post->ID); ?> priority"><?php echo getTaskPriority($post->ID); ?> Priority</em>
            <?php } else { ?>
            <em class="priority roundness normal" title="normal priority">Normal Priority</em>
            <?php } ?>
        </span>
    </div>
    <?php } ?>

    <?php endwhile; endif; ?>


	<div class="edit-task">
		<?php
			$post_type = get_post_type($post);
			$delLink = wp_nonce_url(  get_template_directory_uri() . "/delete-task.php?post_id=" . $post->ID );
		?>
		<input type="button" name="delete" class="button delete update-icon roundness" onclick="deleteTask('<?php echo $delLink ?>')" value="Delete Task" />
	</div> 

    <?php
	if ($options['allow_comments'] == true) { ?>
    <!--/ Start Comments /-->
    <div id="comment-area">
		<?php if ($options['comments_side'] == true) { echo '<div class="comment-pos">'; } ?>
        <?php comments_template( 'comments.php' ); ?>
		<?php if ($options['comments_side'] == true) { echo '</div>'; } ?>
    </div>
    <!--/ End Comments /-->
    <?php } ?>

</div>
<iframe id="deletey" name="deletey" width="0" height="0" frameborder="0"></iframe>
<?php get_footer(); ?>
