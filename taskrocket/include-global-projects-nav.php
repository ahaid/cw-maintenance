<ul class="nav roundness">
    <li class="nav-dashboard"><a href="<?php echo home_url(); ?>">Dashboard</a></li>
    <li class="nav-projects"><a href="<?php echo home_url(); ?>/projects">All Projects <span class="active-projects-count">
    <?php wp_count_terms( 'category'); 
    
    $projectCount = wp_count_terms( 'category', array( 'hide_empty' => TRUE));
    echo "(" . $projectCount . ")"; ?></span></a>
        <ul>
            <?php wp_list_categories('sort_order=asc&style=list&hide_empty=0&children=false&hierarchical=false&title_li=0&show_count=1&show_option_none='); ?>
        </ul>
    </li>
    <li class="nav-logout"><a href="<?php echo wp_logout_url(); ?>">Logout</a></li>
    <?php $options = get_option( 'taskrocket_settings' ); 
    if ($options['support-page'] == true) {  ?>
    <li class="nav-support "><a href="<?php echo home_url(); ?>/support">Support</a></li>
    <?php } ?>
</ul>