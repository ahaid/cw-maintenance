<li class="nav-dashboard"><a href="<?php echo home_url(); ?>/">Dashboard</a></li>
<li class="nav-projects"><a href="<?php echo home_url(); ?>/projects">All Locations</a>

<ul>
    <?php
    $args = array (
        'echo'			   => 0,
        'hide_empty'       => 0,
        'taxonomy'         => 'category',
        'hierarchical'     => 0,
        'show_count' 	   => 1,
        'title_li' 		   => '',
        'depth' 		   => 1
    );
    $projectnav = wp_list_categories($args);
    $projectnav = str_replace ( "(" , "<span>", $projectnav );
    $projectnav = str_replace ( ")" , "</span>", $projectnav );
    echo $projectnav;
    ?>
</ul>
