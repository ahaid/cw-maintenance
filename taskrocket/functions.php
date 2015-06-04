<?php
// Update checker
// include("update_notifier.php");

// Remove FTP nag for updates
define('FS_METHOD','direct');

// Enable file uploads
function tr_handle_attachment($file_handler,$post_id,$set_thu=false) {

	if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();

	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	require_once(ABSPATH . "wp-admin" . '/includes/file.php');
	require_once(ABSPATH . "wp-admin" . '/includes/media.php');

	$attach_id = media_handle_upload( $file_handler, $post_id );
}

function get_currentuser_role(){
    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;
    $user_role = array_shift($user_roles);
    return $user_role;
}

function get_users_in_role($role){
    $args = array(
    'role'  => $role
    );
    return get_users( $args );
}

// Prevent trash from being automatically emptied
define('EMPTY_TRASH_DAYS', 100000 ); // Amount of days

function getTaskPriority($post_id){
    $value = get_post_meta($post_id, 'priority', TRUE);
    return getPriorityText($value);
}

function getPriorityText($value) {
    $text = '';
    if ($value == '1') {$text = "low";}
    if ($value == '3') {$text = "normal";}
    if ($value == '5') {$text = "high";}
    if ($value == '7') {$text = "urgent";} 
    return $text;
}

function comment_inserted($comment_id, $comment) {
    sendTaskEmail($comment->comment_post_ID, 'commented', $comment->comment_content);
}

add_action('wp_insert_comment','comment_inserted',99,2);

function addEmailAddress($curEmails, $emailToAdd){
    if ($current_user->user_email != $emailToAdd){
        $addedEmail = $emailToAdd;
    } else {
        $addedEmail = '';
    }
    if (strlen($curEmails)>0) {$curEmails = $curEmails . ',';}
        $curEmails = $curEmails . $addedEmail;
    return $curEmails;
}

function getMaintenanceEmails(){
    $users = get_users_in_role('maintenance');
    $emails = '';
    foreach($users as $user){
        $emails = addEmailAddress($emails, $user->user_email);
    }
    return $emails;
}

function sendTaskEmail($post_id, $event, $commentText){ // events are: created, completed, commented
    // If option to not send emails is true
    $options = get_option( 'taskrocket_settings' );
    if ($options['no_emails'] == true) {
        // Emails are not sent
    } else {
        // Create HTML email for the recipient (new task owner)
        $post = get_post( $post_id );
        $category = get_the_category( $post_id )[0];
        $current_user = wp_get_current_user();

        $owner_id = $post->post_author;
        $owner = get_userdata($owner_id);
        
        $projectURL = get_category_link($category->term_id );
        $taskURL = get_permalink( $post_id );
        $projectname = $category->name;
        $thepriority = getTaskPriority($post_id);
        if ($event == 'completed' || $event == 'commented') { // send notification of completion or comment to task reporter
            $reporter = get_post_meta($post_id, 'reporter_email', TRUE);
            $emailTo = addEmailAddress('', $reporter);
        }
        if ($event == 'created' || $event == 'commented') { // send notification of new tasks or comments to maintenance role
            $emailTo = getMaintenanceEmails();
        }

        if ($thepriority == 'low') { $prioritycolor = "43bce9"; }
        if ($thepriority == 'normal') { $prioritycolor = "48cfae"; }
        if ($thepriority == 'high') { $prioritycolor = "f9b851"; }
        if ($thepriority == 'urgent') { $prioritycolor = "fb6e52"; }

        
        $taskSenderFirstName = $current_user->user_firstname;
        $taskSenderLastName = $current_user->user_lastname;
        $tasksendericon  = 'http://www.gravatar.com/avatar/' . md5($current_user->user_email) . '?s=120';
        $taskreceivericon = 'http://www.gravatar.com/avatar/' . md5($emailTo) . '?s=120';
        if(get_post_meta($post->ID, 'duedate', TRUE) == "") {
            $duedate = "Not specified";
        } else {
            $duedate = get_post_meta($post_id, 'duedate', TRUE);
        }
        
        if ($event == 'created') {$presubject = 'New task: '; $bodytext = 'created the task';}
        if ($event == 'completed') {$presubject = 'Task complete: '; $bodytext = 'completed the task';}
        if ($event == 'commented') {$presubject = 'New comment: '; $bodytext = 'commented on the task';}

        $subject = $presubject . $post->post_title;

        $body = '
        <table width="100%" height="100%" border="0" cellspacing="25" cellpadding="0" style="background:#f1f1f1;padding:100px 0;">
        <tr>
        <td align="center" valign="middle">
        <table width="400" border="0" cellspacing="0" cellpadding="0" style="padding:25px; background:#fff; width:400px;text-align:left; border-left:solid 4px #' . $prioritycolor . ';" bgcolor="#ffffff">
        <tr>
        <td>
        <p style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#333645;"><strong>' . $taskSenderFirstName . ' ' . $taskSenderLastName . '</strong> ' . $bodytext . ' <strong>' . $post->post_title  . '</strong>.</p>
        <p style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#333645;"><strong style="display:block;float:left;width:70px;">Location:</strong> <a style="color:#333645;" href="' . $projectURL . '">' . $projectname . '</a>.</p>
        <p style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#333645;text-transform:capitalize;"><strong style="display:block;float:left;width:70px;text-transform:capitalize;">Priority:</strong> ' . $thepriority . '</p>
        <p style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#333645;"><strong style="display:block;float:left;width:70px;">Due by:</strong> '. $duedate . '</p>
        ';

        if ($event == 'commented') {$body = $body . '<p style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#333645;"><strong style="display:block;float:left;width:70px;">Comment:</strong> '. $commentText . '</p>';}

        $body = $body . '<a style="font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#fff; display:block; width:85px; height:18px; background:#' . $prioritycolor . ';text-decoration:none;padding:10px; text-align:center;" href="' . $taskURL . '">View Task</a>
        </td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        ';
        $headers[]= "Content-type:text/html;charset=UTF-8";
        $headers[]= 'From: CW Maintenance <' . $current_user->user_email . '>';
        $headers[]= 'Reply-To: ' . $current_user->user_email;
        $headers[]= "MIME-Version: 1.0";
        wp_mail($emailTo, $subject, $body, $headers);
    }
}


function task_completed ($post_id) {
    sendTaskEmail($post_id, 'completed', null);
}
add_action('wp_trash_post', 'task_completed');

// Setup required pages
if (isset($_GET['activated']) && is_admin()){
    add_action('init', 'setup_required_pages');
}
function setup_required_pages() {

$pages = array(
	// Array of Pages and associated Templates
    'Account' => array(
        'Account'=>'page-account.php'),

    'New Project' => array(
        'New Project'=>'page-new-project.php'),

	'New Task' => array(
        'New Task'=>'page-new-task.php'),

    'Projects' => array(
        'Projects'=>'page-projects.php'),

	'Users' => array(
        'Users'=>'page-users.php'),

	'Client' => array(
        'Client'=>'page-clients.php'),

	'Report' => array(
        'Report'=>'page-report.php')
);

foreach($pages as $page_url_title => $page_meta) {
        $id = get_page_by_title($page_url_title);

    foreach ($page_meta as $page_content=>$page_template){
		$page = array(
			'post_type'   => 'page',
			'post_title'  => $page_url_title,
			'post_name'   => $page_url_title,
			'post_status' => 'publish',
			'post_content' => $page_content,
			'post_author' => 1,
			'post_parent' => ''
		);

		if(!isset($id->ID)){
			$new_page_id = wp_insert_post($page);
			if(!empty($page_template)){
					update_post_meta($new_page_id, '_wp_page_template', $page_template);
			}
		}
	 }
   }
}


// If user has gravatar function
function user_has_gravatar( $email_address ) {
	$url = 'http://www.gravatar.com/avatar/' . md5( strtolower( trim ( $email_address ) ) ) . '?d=404';
	$headers = @get_headers( $url );
	return preg_match( '|200|', $headers[0] ) ? true : false;
}

// Add selection field to user profile
// Todo: Only show if the role of the user being edited is 'client'.
add_action( 'show_user_profile', 'show_extra_profile_fields' );
add_action( 'edit_user_profile', 'show_extra_profile_fields' );

function show_extra_profile_fields( $user ) { ?>
	<h3>Client Access</h3>
	<table class="form-table">
		<tr>
			<th><label for="gender">Project</label></th>
			<td>
				<select name="client_project" id="client_project" >
                    <option></option>
                	<?php
					foreach (get_categories('sort_order=asc&hide_empty=0') as $category){ ?>
                    <option value="<?php echo $category->cat_ID; ?>" <?php selected( $category->cat_ID, get_the_author_meta( 'client_project', $user->ID ) ); ?>><?php echo $category->name; ?></option>
                	<?php } ?>
				</select>
                <br />
                <span class="description">Allow
                    <?php
                    $userID = $_GET['user_id'];
                    $user_info = get_userdata($userID);
                    echo "<strong>" . $user_info->user_login . "</strong>";
                    ?>
                    to view this project.</span>
			</td>
		</tr>
	</table>
<?php }

add_action( 'personal_options_update', 'save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_profile_fields' );

function save_extra_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
		update_user_meta( $user_id, 'client_project', $_POST['client_project'] );
}

// Set default thumbnail size when theme is activated
if ( ! function_exists( 'activated_theme' ) ) {
    function activated_theme() {
        update_option( 'thumbnail_size_w', 0 );
        update_option( 'thumbnail_size_h', 150 );
    }
    add_action( 'after_switch_theme', 'activated_theme' );
}


// Change permalink structure
add_action('init', 'changePermalink');
function changePermalink() {
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/%postname%/' );
}

// Change the category base
add_action('init', 'categoryBase');
function categoryBase() {
	global $wp_rewrite;
	$wp_rewrite->set_category_base( 'projects' );
}


// Encue jQuery in front end
function sp_load_jquery() {
    if ( ! is_admin() && !wp_script_is( 'jquery' ) ) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js", false, null);
        wp_enqueue_script('jquery');
    }
}
add_action('wp_enqueue_scripts', 'sp_load_jquery');


// Replace the login logo URL
function my_login_logo_url() {
    return "";
}
add_filter( 'login_headerurl', 'my_login_logo_url' );
function my_login_logo_url_title() {
    return "";
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );


// Rename 'uncategorized'
wp_update_term(1, 'category', array(
    'name' => 'Unassigned',
    'slug' => 'unassigned',
    'description' => 'Tasks that are not assigned to any project'
));


// Check if current project has children
function project_has_children() {
global $wpdb;
$term = get_queried_object();
$project_children_check = $wpdb->get_results(" SELECT * FROM wp_term_taxonomy WHERE parent = '$term->term_id' ");
    if ($project_children_check) {
        return true;
    } else {
        return false;
    }
}


// List categories for the current author
function my_list_authors() {

    $authors = wp_list_authors( array(
    'exclude_admin' => false,
    'html' => false,
    'echo' => false
    ) );

    $authors = explode( ',', $authors );

    echo '<ul>';

    foreach ( $authors as $author ) {

    $author = get_user_by( 'login', $author );
    $link = get_author_posts_url( false, $author->ID );
    echo "<li><a href='{$link}'>{$author->display_name}</a><ul>";

    $posts = get_posts( array(
        'author' => $author->ID,
        'numberposts' => -1
    ) );

    $categories = array();

    foreach ( $posts as $post )
        foreach( get_the_category( $post->ID ) as $category )
        $categories[$category->term_id] =  $category->term_id;

    $output = wp_list_categories( array(
        'include' => $categories,
        'title_li' => '',
        'echo' => false
        ) );

    echo $output . '</ul></li>';
    }
}


// Change the posts per page limit (used mainly for search results)
function wpse8982_filter_pre_get_posts( $query ) {
    if ( $query->is_main_query() ) {
        $query->set( 'posts_per_page', '99' );
    }
}
add_action( 'pre_get_posts', 'wpse8982_filter_pre_get_posts' );



// Custom styles for the login page
function custom_login_stylesheet() { ?>
    <link rel="stylesheet" id="custom_wp_admin_css"  href="<?php echo get_bloginfo( 'stylesheet_directory' ) . '/style-login.css'; ?>" type="text/css" media="all" />
<?php }
add_action( 'login_enqueue_scripts', 'custom_login_stylesheet' );


// Disable the Admin bar.
show_admin_bar(false);


// Only allow authors into the admin area
add_action("admin_menu","redirect_nonadmin");
function redirect_nonadmin(){
// If the user can not publish posts,...
	if (!current_user_can('activate_plugins')) {
		header( 'Location: ' . home_url() . '' ) ;
  	}
}


// Exclude pages from search
function SearchFilter($query) {
if ($query->is_search) {
$query->set('post_type', 'post');
}
return $query;
}

add_filter('pre_get_posts','SearchFilter');

// Remove roles
remove_role('subscriber');
remove_role('editor');
remove_role('author');
remove_role('contributor');
remove_role('basic_contributor');
remove_role('client');


// Create a role for Clients
// add_role('client', 'Client', array(
//     'read' => true,
//     'edit_posts' => false,
//     'delete_posts' => false,
// ));

// Create a role for Directors
add_role('director', 'Director', array(
    'read' => true,
    'edit_posts' => true,
    'delete_posts' => true,
    'upload_files' => true
));

// Create a role for Maintenance
add_role('maintenance', 'Maintenance', array(
    'read' => true,
    'edit_posts' => true,
    'delete_posts' => true,
    'upload_files' => true,
    'edit_others_posts' => true,
    'delete_others_posts' => true
));

// Create a role for Unknown users
add_role('unknown', 'Unknown', array(
    'read' => false
));



// Remove metaboxes from posts
function remove_themeta_boxes() {
    remove_meta_box( 'postexcerpt' , 'post' , 'normal' );
    remove_meta_box( 'trackbacksdiv' , 'post' , 'normal' );
    remove_meta_box( 'tagsdiv-post_tag' , 'post' , 'normal' );
    remove_meta_box( 'slugdiv' , 'post' , 'normal' );
	remove_meta_box( 'postimagediv' , 'post' , 'normal' );
}
add_action('admin_init', 'remove_themeta_boxes');


// Remove the posts editor
function remove_posts_editor(){
    remove_post_type_support( 'post', 'editor' );
}
add_action( 'init', 'remove_posts_editor' );


// Remove metaboxes from admin dashboard
function remove_dashboard_widgets(){
	remove_meta_box('dashboard_right_now', 'dashboard', 'normal');   // Right Now
    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');  // Incoming Links
    remove_meta_box('dashboard_plugins', 'dashboard', 'normal');   // Plugins
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');  // Quick Press
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');  // Recent Drafts
    remove_meta_box('dashboard_primary', 'dashboard', 'side');   // WordPress blog
    remove_meta_box('dashboard_secondary', 'dashboard', 'side');   // Other WordPress News
}
add_action('wp_dashboard_setup', 'remove_dashboard_widgets');

// Remove welcome panel from admin dashboard
remove_action( 'welcome_panel', 'wp_welcome_panel' );

// Remove admin menu items
add_action( 'admin_menu', 'my_remove_menu_pages' );
function my_remove_menu_pages() {

	remove_submenu_page('edit.php','edit-tags.php?taxonomy=post_tag');
	remove_submenu_page('options-general.php', 'options-reading.php');
	remove_submenu_page('themes.php','nav-menus.php');
	remove_submenu_page('themes.php','customize.php');
	remove_submenu_page('themes.php','theme-editor.php');
}

// Rename admin labels
function change_post_menu_label() {
    global $menu;
    global $submenu;
    $menu[5][0] = 'Tasks';
    $submenu['edit.php'][5][0] = 'Task Items';
    $submenu['edit.php'][10][0] = 'Add New Task';
	$submenu['edit.php'][15][0] = 'Projects';
}
add_action( 'admin_menu', 'change_post_menu_label' );


// Hide the featured image metabox because it can't be done using remove_meta_box
add_action('do_meta_boxes', 'remove_thumbnail_box');
function remove_thumbnail_box() {
    remove_meta_box( 'postimagediv','post','side' );
}

// Featured images
add_theme_support( 'post-thumbnails' );
if ( function_exists( 'add_theme_support' ) ) {
add_theme_support( 'post-thumbnails' );
set_post_thumbnail_size( 620, 400, $crop = true );
}

// Remove the theme editor
function remove_editor_menu() {
  remove_action('admin_menu', '_add_themes_utility_last', 101);
}
add_action('_admin_menu', 'remove_editor_menu', 1);


// Hide titles attributes from category list
function wp_list_categories_remove_title_attributes($output) {
    $output = preg_replace('` title="(.+)"`', '', $output);
    return $output;
}
add_filter('wp_list_categories', 'wp_list_categories_remove_title_attributes');


// Add custom fields to profiles
function extra_profile_details( $contactmethods ) {
// Add background choice
$contactmethods['background'] = 'Background';
return $contactmethods;
}
add_filter('user_contactmethods','extra_profile_details',10,1);

// Hide the parent selection box in categories (admin)
// add_action( 'admin_head-edit-tags.php', 'tr_remove_parent_category' );

add_action( 'admin_head-edit-tags.php', 'tr_remove_parent_category' );

function tr_remove_parent_category() {

    $parent = 'parent()';

    if ( isset( $_GET['action'] ) )
        $parent = 'parent().parent()';

    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($)
            {
                $('label[for=parent]').<?php echo $parent; ?>.remove();
            });
        </script>
    <?php
}


// Convert the categories checkboxes into radio buttons so that only one category can be selected.
function convert_root_cats_to_radio() {
global $post_type;
?>
<script type="text/javascript">
jQuery("#categorychecklist>li>input").each(function(){
    this.disabled = "disabled";
});
jQuery("#categorychecklist>li>label input").each(function(){
    this.type = 'radio';
});
// Hide the 'most used' tab
jQuery("#category-tabs li:odd").hide();
</script> <?php
}
add_action( 'admin_footer-post.php',     'convert_root_cats_to_radio' );
add_action( 'admin_footer-post-new.php', 'convert_root_cats_to_radio' );


// Encue date picker scripts
function dp_scripts() {
  wp_enqueue_script( 'jquery' );
  wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );

  wp_register_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/base/jquery-ui.css');
  wp_enqueue_style( 'jquery-ui' );
}
add_action( 'wp_enqueue_scripts', 'dp_scripts' );


// Create a new metabox for additional task information
class more_info_box {

    var $plugin_dir;
    var $plugin_url;

    function  __construct() {

        add_action( 'add_meta_boxes', array( $this, 'moreinfo_meta_box' ) );
        add_action( 'save_post', array($this, 'save_data') );
    }

	// Add the meta box to POSTS
    function moreinfo_meta_box(){
        add_meta_box(
		'more_info', 				// metabox ID, it also will be it id HTML attribute
		'Additional Information',  // title
		array( &$this, 'meta_box_content' ),
		'post',					 // post type
		'normal', 				   // position of the screen where metabox should be displayed (normal, side, advanced)
		'high' 				     // priority over another metaboxes on this page (default, low, high, core)
	  );
    }

    function meta_box_content(){
        global $post;
        // Use nonce for verification
        wp_nonce_field( plugin_basename( __FILE__ ), 'more_info_box_nounce' );
        // The actual fields for data entry
		?>

		<div class="new-task-admin">

			<label>More details about this task:</label>
			<textarea id="minfo" name="minfo" size="20" class="large-text" rows="20"><?php echo get_post_meta($post->ID, 'minfo', TRUE)?></textarea>

			<div class="task-specifics">

				<div class="spec privacy">
					<label>
						<strong>Make Private</strong>

						<?php
						// global $post;
						// If the author of this task (post) is an admin, then they can make the task private.
						if ( user_can( $post->post_author, 'administrator' ) ) { ?>
						<input type="checkbox" name="private" value="yes" id="private" <?php echo (get_post_meta($post->ID, 'private', TRUE)=="yes" ? "checked" : '') ?> />
						This task will only be visible to administrators.
						<?php } else { ?>
							Only tasks owned by administrators can be private.
						<?php } ?>
					</label>
				</div>

				<div class="spec due-date">
					<label><strong>Due Date</strong>
						<input id="duedate" name="duedate" class="regular-text code" value="<?php echo get_post_meta($post->ID, 'duedate', TRUE)?>" />
					</label>
				</div>

				<div class="spec priority">
					<strong>Priority</strong>

					<label>
						<input type="radio" name="priority" value="low" id="low" <?php echo (get_post_meta($post->ID, 'priority', TRUE)=="low" ? "checked" : '') ?> /> Low
					</label>

					<label>
						<input type="radio" name="priority" value="normal" id="normal" <?php echo (get_post_meta($post->ID, 'priority', TRUE)=="normal" ? "checked" : '') ?> />
						Normal
					</label>

					<label>
						<input type="radio" name="priority" value="high" id="high" <?php echo (get_post_meta($post->ID, 'priority', TRUE)=="high" ? "checked" : '') ?> />
						High
					</label>

					<label>
						<input type="radio" name="priority" value="urgent" id="urgent" <?php echo (get_post_meta($post->ID, 'priority', TRUE)=="urgent" ? "checked" : '') ?> />
						Urgent
					</label>
				</div>

				<div style="display:block; clear:both;"></div>

				<div class="spec attached">
					<label>
						<strong>Attach Files</strong>
						<a href="#" class="button insert-media add_media" data-editor="content" title="Attach Files"><span class="wp-media-buttons-icon"></span>Attach Files</a>
						Note: Attached files can only be attched to one task at any given time.
					</label>

					<!--/ Start Attachments /-->
				    <div class="attachments">
				    <ul>
				    <?php

					// Attachments
					$attachments = get_posts( array(
						'post_type' => 'attachment',
						'posts_per_page' => -1,
						'post_parent' => $post->ID,
						'orderby' => 'title',
						'order' => 'ASC'
					) );

					if ($options['files_new_tab'] == true) {
						$newTab = ' target="_blank"';
					}

					foreach ( $attachments as $attachment ) {
						$filethumb = wp_get_attachment_thumb_url( $attachment->ID);	 // Path to the thumbnail
						$filepath = wp_get_attachment_url( $attachment->ID);			// Path to the original file
						$filename = $attachment->post_title;
						$filesize = @filesize( get_attached_file( $attachment->ID ) );
						$filesize = size_format($filesize, 2);
						$deleteAttachment = wp_nonce_url(home_url() . "/wp-admin/post.php?action=delete&amp;post=".$attachment->ID."", 'delete-post_' . $attachment->ID); ?>
							<?php if ( wp_attachment_is_image( $attachment->ID ) ) { ?>
				            <li class="file-image">
				                <a href="<?php echo $filepath; ?>?TB_iframe=true" class="<?php $options = get_option( 'taskrocket_settings' ); if ($options['use_thickbox'] == true) { echo "thickbox"; } ?>" title="<?php echo $filename; ?>"><img src="<?php echo $filethumb; ?>" /></a>
				                <a class="delete-attachment-button roundness" title="Delete this file">&#215;</a>
				                <span class="filesize"><?php echo $filesize; ?></span>
				                <em class="delete-file-confirmation"><span><strong>Delete?</strong> <a href="<?php echo $deleteAttachment; ?>" target="deletey" class="delete-yes roundness">Yes</a> <a class="delete-no roundness">No</a></span></em>
				            </li>
				           <?php } else { ?>
				           	<li class="file-other">
				                <a href="<?php echo $filepath; ?>" class="the-file-name" title="<?php echo $filename; ?>" target="_blank"><span><?php echo substr($filename, 0, 50); ?>.<?php echo get_icon_for_attachment($attachment->ID); ?></span></a>
				                <a class="delete-attachment-button roundness" title="Delete this file">&#215;</a>
				                <span class="filesize"><?php echo $filesize; ?></span>
				                <em class="delete-file-confirmation"><span><strong>Delete?</strong> <a href="<?php echo $deleteAttachment; ?>" target="deletey" class="delete-yes">Yes</a> <a class="delete-no">No</a></span></em>
				            </li>
				           <?php } ?>
					   <?php } ?>
				   </ul>
				   </div>
				   <!--/ End Attachments /-->

				<script>
					$(".delete-attachment-button").click(function () {
						$(this).closest('.attachments li').find('.delete-file-confirmation').fadeIn(250);
					});
					$(".delete-no").click(function () {
					  $(this).closest(".delete-file-confirmation").fadeOut(250);
				    });
					// Fadeout attachment list item onclick
					$(".delete-yes").click(function () {
					  $(this).closest(".attachments li").fadeOut(1000);
				    });
				</script>

				</div>

			</div>

			<iframe id="deletey" name="deletey" width="0" height="0" frameborder="0"></iframe>

		</div>


		<?php
	}

    function save_data($post_id){
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( !wp_verify_nonce( $_POST['more_info_box_nounce'], plugin_basename( __FILE__ ) ) )
            return;

        // Check permissions
        if ( 'post' == $_POST['post_type'] ){
            if ( !current_user_can( 'edit_page', $post_id ) )
                return;
        }

		// Update the fields
		$data = $_POST['minfo'];
        update_post_meta($post_id, 'minfo', $data, get_post_meta($post_id, 'minfo', TRUE));

		$data = $_POST['duedate'];
		update_post_meta($post_id, 'duedate', $data, get_post_meta($post_id, 'duedate', TRUE));

		$data = $_POST['priority'];
		update_post_meta($post_id, 'priority', $data, get_post_meta($post_id, 'priority', TRUE));

		$data = $_POST['private'];
		update_post_meta($post_id, 'private', $data, get_post_meta($post_id, 'private', TRUE));

		return $data;
    }
}
$more_info_box = new more_info_box;


// Remove these columns from posts table in admin
function tasks_columns_filter( $columns ) {
    unset($columns['tags']);
//  unset($columns['comments']);
    return $columns;
}
add_filter( 'manage_edit-post_columns', 'tasks_columns_filter', 10, 1 );



// Add column to admin posts table
add_filter( 'manage_edit-post_columns', 'admin_post_header_columns', 10, 1);
add_action( 'manage_posts_custom_column', 'admin_post_data_row', 10, 2);

function admin_post_header_columns($columns)
{
    if (!isset($columns['priority']))
        $columns['priority'] = "Priority";
	if (!isset($columns['duedate']))
        $columns['duedate'] = "Due Date";

    return $columns;
}

function admin_post_data_row($column_name, $post_id) {
    switch($column_name) {
        case 'priority':
            $priority = get_post_meta($post_id, 'priority', true);
            if ($priority)   echo $priority;
            break;

        default:
        break;
    }
	switch($column_name) {
        case 'duedate':
            $duedate = get_post_meta($post_id, 'duedate', true);
            if ($duedate)   echo $duedate;
            break;

        default:
        break;
    }
}


// Add stylesheet to admin
function custom_admin_style() {
    wp_enqueue_style('my-admin-style', get_template_directory_uri() . '/style-admin.css');
}
add_action('admin_enqueue_scripts', 'custom_admin_style');


// Add date picker script to posts in admin (both new posts and existing posts).
function custom_admin_js( $hook ) {

    if ( $hook == 'post.php' || $hook == 'post-new.php' ) {

        wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
		wp_register_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/base/jquery-ui.css');
		wp_enqueue_style( 'jquery-ui' );

    } ?>
    <script src="http://code.jquery.com/jquery-latest.min.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
	<script>
    $(function() {
    jQuery("#duedate").datepicker({
    dateFormat : "mm/dd/yy"
    });
    });
    </script>
    <?php
}
add_action('admin_enqueue_scripts','custom_admin_js',10,1);


// ---------------------------
// Task Rocket Settings Page -
// ---------------------------

// Register settings
function taskrocket_settings_init(){
    register_setting( 'taskrocket_settings', 'taskrocket_settings' );
}

// Add settings page to menu
function add_settings_page() {
$icon_path = get_option('siteurl').'/wp-content/themes/'.basename(dirname(__FILE__)).'/images/admin-icon.png';
add_menu_page( __( 'Task Rocket' ), __( 'Task Rocket' ), 'manage_options', 'settings', 'taskrocket_settings_page' ,$icon_path);
}

// Add actions
add_action( 'admin_init', 'taskrocket_settings_init' );
add_action( 'admin_menu', 'add_settings_page' );

// Define your variables
$color_scheme = array('default','blue','green',);

// Start settings page
function taskrocket_settings_page() {
?>

<div class="wrap">
<h2><?php _e( 'Task Rocket Settings' );?></h2>

<?php // show saved options message
if($_GET['settings-updated'] == 'true') { ?>
	<div id="message" class="updated fade"><p><strong><?php _e( 'Options saved' ); ?></strong></p></div>
<?php } ?>

<form method="post" action="options.php">
	<?php settings_fields( 'taskrocket_settings' ); ?>
    <?php $options = get_option( 'taskrocket_settings' ); ?>

    <div class="tr-wrap">

        <div class="tr-floater trcol1">
            <h3>User Settings</h3>
            <table class="form-table">
                <tbody>
                <tr>
                    <td><strong><input id="taskrocket_settings[own_nav]" name="taskrocket_settings[own_nav]" type="checkbox" value="1" <?php checked( '1', $options['own_nav'] ); ?> /> <?php _e( 'Only show users projects in nav' ); ?></strong><br />
                    <label for="taskrocket_settings[own_nav]"><?php _e( 'Only show projects the user is involved with in the main nav.' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <td><strong><input id="taskrocket_settings[admins_edit_all_tasks]" name="taskrocket_settings[admins_edit_all_tasks]" type="checkbox" value="1" <?php checked( '1', $options['admins_edit_all_tasks'] ); ?> /> <?php _e( 'Administrators edit any task' ); ?></strong><br />
                    <label for="taskrocket_settings[admins_edit_all_tasks]"><?php _e( 'Let administrators edit any task from the front-end.' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <td><strong><input id="taskrocket_settings[admins_complete_tasks]" name="taskrocket_settings[admins_complete_tasks]" type="checkbox" value="1" <?php checked( '1', $options['admins_complete_tasks'] ); ?> /> <?php _e( 'Administrators mark any task complete' ); ?></strong><br />
                    <label for="taskrocket_settings[admins_complete_tasks]"><?php _e( 'Let administrators mark any task as complete from the front-end.' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <td><strong><input id="taskrocket_settings[users_create_projects]" name="taskrocket_settings[users_create_projects]" type="checkbox" value="1" <?php checked( '1', $options['users_create_projects'] ); ?> /> <?php _e( 'Let users create projects' ); ?></strong><br />
                    <label for="taskrocket_settings[users_create_projects]"><?php _e( 'Let users to create projects from the front-end.' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <td><strong><input id="taskrocket_settings[delete_projects]" name="taskrocket_settings[delete_projects]" type="checkbox" value="1" <?php checked( '1', $options['delete_projects'] ); ?> /> <?php _e( 'Let users delete projects' ); ?></strong><br />
                    <label for="taskrocket_settings[delete_projects]"><?php _e( 'Let users delete projects from the front-end.' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <td><strong><input id="taskrocket_settings[users_reassign_tasks]" name="taskrocket_settings[users_reassign_tasks]" type="checkbox" value="1" <?php checked( '1', $options['users_reassign_tasks'] ); ?> /> <?php _e( 'Let users reassign tasks' ); ?></strong><br />
                    <label for="taskrocket_settings[users_reassign_tasks]"><?php _e( 'Let users reassign tasks to someone else from the front-end.' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <td><strong><input id="taskrocket_settings[allow_comments]" name="taskrocket_settings[allow_comments]" type="checkbox" value="1" <?php checked( '1', $options['allow_comments'] ); ?> /><?php _e( 'Allow comments on tasks' ); ?></strong> <br />
                    <label for="taskrocket_settings[allow_comments]"><?php _e( 'Allow commenting on tasks.' ); ?></label>
                    </td>
                </tr>
                <tr>
                  <td><strong><input id="taskrocket_settings[allow_comments_pages]" name="taskrocket_settings[allow_comments_pages]" type="checkbox" value="1" <?php checked( '1', $options['allow_comments_pages'] ); ?> /><?php _e( 'Allow comments on pages' ); ?></strong><br />
                    <label for="taskrocket_settings[allow_comments_pages]"><?php _e( 'Allow commenting on custom pages.' ); ?></label>
                  </td>
                </tr>
                <tr>
                  <td><strong><input id="taskrocket_settings[file_uploads]" name="taskrocket_settings[file_uploads]" type="checkbox" value="1" <?php checked( '1', $options['file_uploads'] ); ?> /><?php _e( 'Allow file uploads' ); ?></strong><br />
                    <label for="taskrocket_settings[file_uploads]"><?php _e( 'Let users attach files to tasks.' ); ?></label>
                  </td>
                </tr>
                <tr>
                  <td class="padder"><strong><?php _e( 'Number of recent tasks' ); ?></strong><br /><input id="taskrocket_settings[recent_tasks]" type="text" size="3" maxlength="2" name="taskrocket_settings[recent_tasks]" value="<?php esc_attr_e( $options['recent_tasks'] ); ?>" />
                    <label for="taskrocket_settings[recent_tasks]"><?php _e( 'Show this many recent tasks on the dashboard.' ); ?></label>
                  </td>
                </tr>
                </tbody>
            </table>

            <p class="padder"><input name="submit" class="button button-primary" value="Save Settings" type="submit" /></p>

        </div>


        <div class="tr-floater trcol2">
            <h3>Presentation</h3>
            <table class="form-table">
              <tbody>
				<tr>
                  <td><strong>
                    <input id="taskrocket_settings[show_report_to_all]" name="taskrocket_settings[show_report_to_all]" type="checkbox" value="1" <?php checked( '1', $options['show_report_to_all'] ); ?> />
                    <?php _e( 'Report link in the main nav for all' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[show_report_to_all]">
                      <?php _e( 'Let all project contributors see the report link in the main nav.' ); ?>
                    </label></td>
                </tr>
				<tr>
                  <td><strong>
                    <input id="taskrocket_settings[show_users_in_report]" name="taskrocket_settings[show_users_in_report]" type="checkbox" value="1" <?php checked( '1', $options['show_users_in_report'] ); ?> />
                    <?php _e( 'Include list of project contributors in report' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[show_users_in_report]">
                      <?php _e( 'Include a list of all Task Rocket project contributors on the report page.' ); ?>
                    </label></td>
                </tr>
				<tr>
                  <td><strong>
                    <input id="taskrocket_settings[comments_side]" name="taskrocket_settings[comments_side]" type="checkbox" value="1" <?php checked( '1', $options['comments_side'] ); ?> />
                    <?php _e( 'Comments to the right' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[comments_side]">
                      <?php _e( 'Move comments to a new right pane (suitable for high res screens).' ); ?>
                    </label></td>
                </tr>
				<tr>
                  <td><strong>
                    <input id="taskrocket_settings[disabled_enhanced]" name="taskrocket_settings[disabled_enhanced]" type="checkbox" value="1" <?php checked( '1', $options['disabled_enhanced'] ); ?> />
                    <?php _e( 'Disable enhanced overview' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[disabled_enhanced]">
                      <?php _e( 'Show the simple overview on the dashboard instead.' ); ?>
                    </label></td>
                </tr>

                <tr>
                  <td><strong>
                    <input id="taskrocket_settings[disable_mini_chart]" name="taskrocket_settings[disable_mini_chart]" type="checkbox" value="1" <?php checked( '1', $options['disable_mini_chart'] ); ?> />
                    <?php _e( 'Disable the mini chart' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[disable_mini_chart]">
                      <?php _e( 'Don\'t show the mini chart in the left pane.' ); ?>
                    </label></td>
                </tr>

                <tr>
                  <td><strong>
                    <input id="taskrocket_settings[use_thickbox]" name="taskrocket_settings[use_thickbox]" type="checkbox" value="1" <?php checked( '1', $options['use_thickbox'] ); ?> />
                    <?php _e( 'Thumbnail lightbox' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[use_thickbox]">
                      <?php _e( 'Open attachment images in a lightbox when clicked.' ); ?>
                    </label></td>
                </tr>
                <tr>
                  <td><strong>
                    <input id="taskrocket_settings[hide_chart]" name="taskrocket_settings[hide_chart]" type="checkbox" value="1" <?php checked( '1', $options['hide_chart'] ); ?> />
                    <?php _e( 'Hide dashboard project progress' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[hide_chart]">
                      <?php _e( 'Don\'t show the project progress chart on the dashboard.' ); ?>
                    </label>
                    </td>
                </tr>
                <tr>
                  <td><strong>
                    <input id="taskrocket_settings[show_gravatars]" name="taskrocket_settings[show_gravatars]" type="checkbox" value="1" <?php checked( '1', $options['show_gravatars'] ); ?> />
                    <?php _e( 'Show gravatars' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[show_gravatars]">
                      <?php _e( 'Show user gravatars (will only show if users have a <a href="https://en.gravatar.com/" target="_blank">gravatar</a> account).' ); ?>
                    </label>
                    </td>
                </tr>
                <tr>
                  <td><strong>
                    <input id="taskrocket_settings[show_users_link]" name="taskrocket_settings[show_users_link]" type="checkbox" value="1" <?php checked( '1', $options['show_users_link'] ); ?> />
                    <?php _e( 'Users link in nav' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[show_users_link]">
                      <?php _e( 'Show the users link in the main nav.' ); ?>
                    </label>
                    </td>
                </tr>
                <tr>
                  <td><strong>
                    <input id="taskrocket_settings[disable_tips]" name="taskrocket_settings[disable_tips]" type="checkbox" value="1" <?php checked( '1', $options['disable_tips'] ); ?> />
                    <?php _e( 'Disable tips on dashboard' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[disable_tips]">
                      <?php _e( 'Disable the random tips on the dashboard.' ); ?>
                    </label>
                    </td>
                </tr>
                <tr>
                  <td><strong>
                    <input id="taskrocket_settings[display_date]" name="taskrocket_settings[display_date]" type="checkbox" value="1" <?php checked( '1', $options['display_date'] ); ?> />
                    <?php _e( 'Display the date' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[display_date]">
                      <?php _e( 'Show the current date on every page.' ); ?>
                    </label>
                    </td>
                </tr>
                <tr>
                  <td><strong>
                    <input id="taskrocket_settings[show_ID]" name="taskrocket_settings[show_ID]" type="checkbox" value="1" <?php checked( '1', $options['show_ID'] ); ?> />
                    <?php _e( 'Show the task ID' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[show_ID]">
                      <?php _e( 'Display the ID of tasks next to their title.' ); ?>
                    </label>
                    </td>
                </tr>
                <tr>
                  <td><strong>
                    <input id="taskrocket_settings[page_nav_dropdown]" name="taskrocket_settings[page_nav_dropdown]" type="checkbox" value="1" <?php checked( '1', $options['page_nav_dropdown'] ); ?> />
                    <?php _e( 'Custom page nav as a dropdown' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[page_nav_dropdown]">
                      <?php _e( 'Display custom pages nav as a dropdown menu instead of a list.' ); ?>
                    </label>
                    </td>
                </tr>
                <tr>
                  <td class="padder"><strong>
                  <?php _e( 'Pages nav label' ); ?>
                    </strong> <br />
                    <input id="taskrocket_settings[dropdown_nav_label]" name="taskrocket_settings[dropdown_nav_label]" type="text" value="<?php esc_attr_e( $options['dropdown_nav_label'] ); ?>" />
                    <label for="taskrocket_settings[dropdown_nav_label]">
                      <?php _e( 'The label that appears above the pages nav.' ); ?>
                    </label>
                    </td>
                </tr>
                <tr>
                  <td class="padder"><strong>
                    <?php _e( 'Admin task indicator label' ); ?>
                    </strong> <br />
                    <input id="taskrocket_settings[admin_title]" name="taskrocket_settings[admin_title]" type="text" value="<?php esc_attr_e( $options['admin_title'] ); ?>" />
                    <label for="taskrocket_settings[admin_title]">
                      <?php _e( 'Indicates when a task is owned by an administrator (eg: Project Manager or Team Leader). Leave empty to disable.' ); ?>
                    </label>
                    </td>
                </tr>
              </tbody>
            </table>

        	<p class="padder"><input name="submit" class="button button-primary" value="Save Settings" type="submit" /></p>

        </div>


        <div class="tr-floater trcol3">
            <h3>Other Options</h3>
            <table class="form-table">
  			<tbody>
				<tr>
                  <td><strong>
                    <input id="taskrocket_settings[no_emails]" name="taskrocket_settings[no_emails]" type="checkbox" value="1" <?php checked( '1', $options['no_emails'] ); ?> />
                    <?php _e( 'No email notifications' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[no_emails]">
                      <?php _e( 'Do not send any email notifications.' ); ?>
                    </label></td>
                </tr>
				<tr>
                  <td><strong>
                    <input id="taskrocket_settings[send_plain]" name="taskrocket_settings[send_plain]" type="checkbox" value="1" <?php checked( '1', $options['send_plain'] ); ?> />
                    <?php _e( 'Plain email notifications' ); ?>
                    </strong> <br />
                    <label for="taskrocket_settings[send_plain]">
                      <?php _e( 'Send task notification emails as plain text.' ); ?>
                    </label></td>
                </tr>
                <tr>
                  <td class="padder"><strong>
                  <?php _e( 'Dashboard Message' ); ?>
                    </strong> <br />
                    <textarea id="taskrocket_settings[dash_message]" name="taskrocket_settings[dash_message]" cols="30" rows="5" style="width:calc(100% - 20px); height:100px;"><?php esc_attr_e( $options['dash_message'] ); ?></textarea>
                    <label for="taskrocket_settings[dash_message]">
                      <?php _e( 'Display an important message on the dashboard. Leave empty to disable.' ); ?>
                    </label>

					<div class="dash-bg">
						<strong>
	                  		<?php _e( 'Background Colour' ); ?>
	                    </strong>
						<input type="radio" class="dash_red" name="taskrocket_settings[dash_color]" value="dash_red"<?php checked( 'dash_red' == $options['dash_color'] ); ?> /> Red
						<input type="radio" class="dash_blue" name="taskrocket_settings[dash_color]" value="dash_blue"<?php checked( 'dash_blue' == $options['dash_color'] ); ?> /> Blue
						<input type="radio" class="dash_orange" name="taskrocket_settings[dash_color]" value="dash_orange"<?php checked( 'dash_orange' == $options['dash_color'] ); ?> /> Orange
						<input type="radio" class="dash_green" name="taskrocket_settings[dash_color]" value="dash_green"<?php checked( 'dash_green' == $options['dash_color'] ); ?> /> Green
					</div>

                    </td>
                </tr>
                <tr>
                  <td class="padder"><strong>
                  <?php _e( 'Custom style sheet' ); ?>
                    </strong> <br />
                    <input id="taskrocket_settings[custom_css]" name="taskrocket_settings[custom_css]" type="text" value="<?php esc_attr_e( $options['custom_css'] ); ?>" />
                    <label for="taskrocket_settings[custom_css]">
                      <?php _e( 'The file name of your custom CSS file (eg: my-custom-styles.css).<br />Include your own custom CSS file here if you want to over-ride specific Task Rocket CSS selectors.<br />Your CSS file must be placed in the theme directory at: themes/taskrocket<br />Leave empty to disable.' ); ?>
                    </label>
                    </td>
                </tr>
                <tr>
                	<td class="padder">
                    	 <strong><?php _e( 'Maintenance' ); ?></strong>
                         <label for="taskrocket_settings[dash_message]">
						   <?php _e( 'If you\'re experiencing problems or not seeing new changes after an update, try hitting the button below.' ); ?>
                         </label>
                         <a href="<?php echo admin_url(); ?>themes.php?action=activate&stylesheet=taskrocket&_wpnonce=<?php echo wp_create_nonce("switch-theme_taskrocket");?>&trmaintenance=ran" class="tr-button">Fix/Update The Things!</a>
                    </td>
                </tr>
              </tbody>
            </table>

            <p class="padder"><input name="submit" class="button button-primary" value="Save Settings" type="submit" /></p>


        </div>

    </div>

    <!--/ Lame way to inititiate the new permalink structure without user intervention /-->
    <iframe src="<?php echo home_url(); ?>/wp-admin/options-permalink.php" width="0" height="0" style="display:none;"></iframe>
</form>

</div>

<?php }
//sanitize and validate
function options_validate( $input ) {
    global $select_options, $radio_options;
    if ( ! isset( $input['option1'] ) )
        $input['option1'] = null;
    $input['option1'] = ( $input['option1'] == 1 ? 1 : 0 );
    $input['sometext'] = wp_filter_nohtml_kses( $input['sometext'] );
    if ( ! isset( $input['radioinput'] ) )
        $input['radioinput'] = null;
    if ( ! array_key_exists( $input['radioinput'], $radio_options ) )
        $input['radioinput'] = null;
    $input['sometextarea'] = wp_filter_post_kses( $input['sometextarea'] );
    return $input;
}

// Task Rocket version number derived from style.css
function theme_version_shortcode() {
    $theme_name = 'taskrocket';
    $theme_data = wp_get_theme();
    return $theme_data->get( 'Version' );
}
add_shortcode('theme_version', 'theme_version_shortcode');

function lic() {
	return '50';
}
add_shortcode('licensed_for', 'lic');

// Display custom Task Rocket dashboard widget
function tr_dashboard_widget_function() { ?>

<?php
	if ( current_user_can('publish_posts') ) { ?>

    <?php
    	$xml = @simplexml_load_file('http://taskrocket.info/tr.xml');
		$themeVersion = do_shortcode("[theme_version]");
	?>
    <div class="tr-widget">
    <p>Before you get too comfortable, it's recommended you tweak some settings to get Task Rocket working the way you want.</p>
    <p><a href="admin.php?page=settings" class="tr-tweak">Get Tweaky With It</a></p>

    <div class="support-list">
        <p><span class="tr-list">FAQ:</span> <a href="http://taskrocket.info/faq" target="_blank">taskrocket.info/faq</a></p>
        <p><span class="tr-list">Support:</span> <a href="http://taskrocket.info/support" target="_blank">taskrocket.info/support</a></p>
        <p><span class="tr-list">Twitter:</span> <a href="https://twitter.com/search?f=realtime&q=%23taskrocket" target="_blank">#taskrocket</a></p>
        <p><span class="tr-list">Google+:</span> <a href="https://plus.google.com/u/0/s/taskrocket" target="_blank">#plus.google.com/u/0/s/taskrocket</a></p>
        <p><span class="tr-list">Follow me:</span> <a href="https://twitter.com/mikeyott" target="_blank">twitter.com/mikeyott</a></p>
        <p><span class="tr-list">Version:</span> v<?php echo do_shortcode("[theme_version]"); ?>
		    <?php if($xml->version > $themeVersion ) { ?>
            <span class="tr-outdated">(Outdated - <a href="http://taskrocket.info/downloads" target="_blank" style="color:#88c32b; text-decoration:underline;">Get The Latest Version</a>)</span>
                <?php } else { ?>
            <span class="tr-up-to-date"><strong>(Up to date)</strong></span>
            <?php } ?>
        </p>
        <p><span class="tr-list">Wordpress:</span> v<?php echo get_bloginfo('version'); ?></p>
    </div>


    <?php
    global $wpdb;

	// count admins
	$admins = array(
	'role' => 'administrator' );
	$a_users = get_users($admins);
	$number_of_admins = count($a_users);

	// count editors
	$editors = array(
	'role' => 'editor' );
	$e_users = get_users($editors);
	$number_of_editors = count($e_users);

	// add 'em up!
	$total_of_editors_admins = $number_of_admins + $number_of_editors;

	?>
    <?php if (do_shortcode("[licensed_for]") != 0) { ?>
    <p>You are using <strong style="background:#48cfae;padding:0 3px;color:#fff;"><?php echo $total_of_editors_admins; ?></strong> out of your <strong style="background:#48cfae;padding:0 3px;color:#fff;"><?php echo do_shortcode("[licensed_for]"); ?></strong> licensed users.</p>
    <?php if ($total_of_editors_admins > do_shortcode("[licensed_for]")) { echo "<p class='tr-license-warning'>Oh no! There are more users on this version of Task Rocket than you are licensed for. Please <a href='http://taskrocket.info/contact' target='_blank' style='color:#fff;text-decoration:underline;'>enquire</a> about upgrading.</p>"; } ?>
	<?php } else { ?>
    <p>Your copy of Task Rocket is licensed for <strong style="color:#88c32b;">unlimited</strong> users.</p>
    <?php } ?>

    <?php if($xml->version > $themeVersion ) { ?>
	<?php echo $xml->message; ?>
    <?php } ?>

    <?php if($xml->version > $themeVersion ) { ?>
    <span class="tr-rocket-outdated"></span>
        <?php } else { ?>
    <span class="tr-rocket-up-to-date"></span>
    <?php } ?>

    </div>

<?php } ?>


<?php } function add_tr_dashboard_widgets() {
	wp_add_dashboard_widget('aad_dashboard_widget', 'Task Rocket', 'tr_dashboard_widget_function');
}
add_action('wp_dashboard_setup', 'add_tr_dashboard_widgets' );



// change the title field text
function frl_enter_title_here_filter($label, $post){
    if($post->post_type == 'post')
        $label = __('Enter task title here', 'frl');
    return $label;
}
add_filter('enter_title_here', 'frl_enter_title_here_filter', 2, 2);



// Remove admin bar items
function wps_admin_bar() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_node('wp-logo');
    $wp_admin_bar->remove_node('comments');
    $wp_admin_bar->remove_node('new-content');
}
add_action( 'wp_before_admin_bar_render', 'wps_admin_bar' );



// Add thickbox to front-end
add_action('wp_head', 'thickbox');
function thickbox() {
	add_thickbox();
}



// Remove help tab
add_action('admin_head', 'mytheme_remove_help_tabs');
function mytheme_remove_help_tabs() {
    $screen = get_current_screen();
    $screen->remove_help_tabs();
}

// Delete hello world post
$post = get_page_by_path('hello-world',OBJECT,'post');
if ($post)
wp_delete_post($post->ID,true);

// Delete sample page
$post = get_page_by_path('sample-page',OBJECT,'page');
if ($post)
wp_delete_post($post->ID,true);


// Send mail as HTML
add_filter( 'wp_mail_content_type', 'set_html_content_type' );
function set_html_content_type() {
return 'text/html';
}

// Redirect to Task Rocket options page after theme activation
global $pagenow;
if ( is_admin() && isset( $_GET['activated'] ) && $pagenow == 'themes.php' ) {
  wp_redirect( admin_url( 'admin.php?page=settings' ) );
}

// Allow more filetypes to be uploaded
function yourtheme_more_upload_mimes($mimes=array()) {
	$mimes['psd']='text/psd';
	$mimes['ai']='text/ai';
	$mimes['eps']='text/eps';
	$mimes['indd']='text/indd';
	$mimes['bmp']='text/bmp';
	return $mimes;
}
add_filter("upload_mimes","yourtheme_more_upload_mimes");

// Attachment File Types.
// Usage: echo '<img src="'.get_icon_for_attachment($attachment->ID).'" />';
function get_icon_for_attachment($post_id) {
	$type = get_post_mime_type($post_id);
	switch ($type) {
		case 'image/jpeg':
		case 'image/jpg':
			return "jpg"; break;

		case 'image/gif':
			return "gif"; break;

		case 'image/png':
			return "png"; break;

		case 'text/bmp':
			return "bmp"; break;

		case 'text/ai':
			return "ai"; break;

		case 'text/psd':
			return "psd"; break;

		case 'text/indd':
			return "indd"; break;

		case 'text/eps':
			return "eps"; break;

		case 'application/zip':
			return "zip"; break;

		case 'application/rar':
			return "rar"; break;

		case 'video/mpeg':
		case 'video/mp4':
		case 'video/quicktime':
			return "video"; break;

		case 'application/pdf':
			return "pdf"; break;

		case 'text/plain':
		case 'text/xml':
			return "text"; break;

		case 'text/csv':
			return "csv"; break;

		case 'application/vnd.ms-excel':
			return "xls"; break;

		case 'application/msword':
			return "doc"; break;

		case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
			return "docx"; break;

		default:
			return "file";
	}
}

// Allow Chrome extension to work
remove_action( 'login_init', 'send_frame_options_header' );
remove_action( 'admin_init', 'send_frame_options_header' );
