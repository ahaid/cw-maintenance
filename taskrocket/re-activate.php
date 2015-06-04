<?php if(isset($_POST['reactiavte'])) {

if(isset($_POST['go'])) {
require ("../../../wp-admin/admin-header.php");
add_action('init', 'reactivate_required_pages');
function reactivate_required_pages() {

$pages = array(
	// Array of Pages and associated Templates
    'Account' => array(
        'Account'=>'page-account.php'),

    'New Project' => array(
        'New Project'=>'page-new-project.php'),
	
	'New Task' => array(
        'New Task'=>'page-new-task.php'),

    'Projects' => array(
        'Projects'=>'page-projects'),
		
	'Testing' => array(
        'Testing'=>'testing'),	
	
	'Support' => array(
        'Support'=>'page-support'),
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

echo "Done";
exit;
}
}

}?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Re-activate</title>
</head>

<body>

<form method="post" action="">
	<input type="button" id="reactiavte" name="reactiavte" value="Re-activate" />
</form>

</body>
</html>

