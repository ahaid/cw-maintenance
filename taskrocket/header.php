<?php
get_currentuserinfo();
global $user_ID;
if ($user_ID == '') {
	header('Location: '.home_url().'/wp-login.php');
	exit();
}
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

$options = get_option( 'taskrocket_settings' );
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8" />
<title><?php 
    if ( is_home() ) { 
        bloginfo('name'); 
    } else {
        wp_title();
    }?>
</title>

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />

<!--link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/style.scss.css" type="text/css" media="screen" /-->

<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/print.css" type="text/css" media="print" />
<?php
if ($options['custom_css'] !== "") { ?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/<?php echo $options['custom_css']; ?>" type="text/css" media="screen" />
<?php } ?>

<!--/ Mobile Viewport Scale /-->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />

<!--/ Icons /-->
<link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/images/system/favicon.png" />
<link rel="apple-touch-icon" sizes="72x72" href="<?php echo get_template_directory_uri(); ?>/images/system/favicon-72x72.png" />
<link rel="apple-touch-icon" sizes="114x114" href="<?php echo get_template_directory_uri(); ?>/images/system/favicon-114x114.png" />
<link rel="apple-touch-icon" sizes="144x144" href="<?php echo get_template_directory_uri(); ?>/images/system/favicon-144x144.png" />

<?php global $userdata; get_currentuserinfo(); ?>

<?php wp_head();?>
</head>

<body>

<?php // If user is a client
	if( !current_user_can('client')) { ?>

<?php require_once('include-message.php'); ?>

<!--/ Start Container /-->
<div id="container">

<div class="current-user roundness">
    <?php if ($options['show_gravatars'] == true) { ?>
    <a href="<?php echo home_url(); ?>/account">
    <?php // Show user avatar
        global $current_user;
        get_currentuserinfo();
        echo get_avatar( $current_user->ID, 170 );
    ?></a>
    <?php } ?>
    <p class="name">
    <?php if ($userdata->first_name !== "") {
    echo $userdata->first_name . " " . $userdata->last_name;
    } else {
    echo "Captain Noname";
    }
    ?>
        <span class="capitalize">
            <?php 
                echo get_currentuser_role();
            ?>
        </span>
    </p>

</div>

<div id="start" class="today">
	<?php
    if ($options['display_date'] == true) {  ?>
    <span class="todays-date"><?php echo date('l jS \of F Y'); ?></span>
    <?php } ?>
</div>

<!--/ Start Left /-->
<div class="left">

	<span class="logo"><a href="<?php echo home_url(); ?>/">TaskRocket <span class="desc">dashboard</span></a></span>

    <ul class="account-admin">
        <li><a href="<?php echo home_url(); ?>/account" class="edit-account-icon" title="Edit profile">edit</a></li>
        <?php if ( current_user_can('manage_options') ) { ?>
        <li><a href="<?php echo home_url(); ?>/wp-admin/admin.php?page=settings" class="go-to-admin" title="Go to admin">admin</a></li>
        <?php } ?>
    </ul>

    <!--/ Start Sticky /-->
	<div class="side-tools">

        <?php if (get_currentuser_role() != 'unknown'){ ?>
            <!--/ Start Search /-->
            <form method="get" id="searchform" action="<?php echo home_url(); ?>/" onsubmit="return validateSearch()">
                <fieldset>
                    <label class="screen-reader-text" for="s">Search:</label>
                    <input type="text" value="" name="s" id="s" title="Search" class="roundness" />
                    <input type="submit" id="searchsubmit" value="Search" />
                    <span class="show-advanced">Search Filter</span>
                </fieldset>

                <!--/ Start Advanced Search Selection /-->
                <fieldset class="advanced-cats roundness">
                	<em></em>
                    <label class="on"><input type="radio" name="cat" value="" checked="checked" /><span>Search Everywhere</span></label>
                    <?php
    					foreach (get_categories('sort_order=asc&style=list&hide_empty=1&children=false&hierarchical=false&title_li=0') as $category){
    					echo '<label><input type="radio" name="cat" value="' . $category->cat_ID . '" />';
    					echo $category->name;
    					echo '</label>';
                    } ?>
                </fieldset>
                <!--/ End Advanced Search Selection /-->
            </form>
            <!--/ End Search /-->
        <?php } ?>

        <div class="new-choice">
        <?php if ($options['users_create_projects'] == true || current_user_can( 'manage_options' )) { ?>
            <a href="<?php echo home_url(); ?>/new-project" class="new-project roundness"><span>New Project</span></a>
        <?php } ?>
        <?php if (get_currentuser_role() != 'unknown'){ ?>
            <a href="<?php echo home_url(); ?>/new-task" class="new-task roundness"><span>New Task</span></a>
        <?php } ?>
        </div>

        <ul class="nav roundness">

			<?php 
			if (get_currentuser_role() != 'unknown') { 
				require_once('include-all-projects-nav.php');
			}?>

			<?php
		    if ($options['show_users_link'] == true) { ?>
            <li class="nav-users"><a href="<?php echo home_url(); ?>/users/">Users</a></li>
            <?php } ?>

            <?php if($options['show_report_to_all'] == true || current_user_can( 'manage_options' )) { ?>
				<li class="nav-report"><a href="<?php echo home_url(); ?>/report/">Report</a></li>
			<?php } ?>


            <li class="nav-logout"><a href="<?php echo wp_logout_url(); ?>">Logout</a></li>
        </ul>

        <?php require_once('inc-extra-nav.php'); ?>

    </div>
    <!--/ End Sticky /-->

    <!--/ Start Mini Chart /-->
    <?php
	$options = get_option( 'taskrocket_settings' );
	if ($options['disable_mini_chart'] == false) { ?>
		<?php if ( !is_home() ) { ?>
        <div class="mini-chart">
            <?php require('include-chart.php'); ?>
        </div>
        <?php } ?>
    <?php } ?>
    <!--/ End Mini Chart /-->

</div>
<!--/ End Left /-->
<?php // If user is a client
} ?>
