<!--/ Start Users List /-->
<div class="tr-users">
    <ul class="dash-users">
    <?php $lastnames = $wpdb->get_col("SELECT user_id FROM $wpdb->usermeta WHERE $wpdb->usermeta.meta_key = 'first_name' ORDER BY $wpdb->usermeta.meta_value ASC");
    foreach ($lastnames as $userid) {
    $user = get_userdata($userid);
    $post_count = count_user_posts($user->ID);
    $author_posts_url = get_author_posts_url($user->ID); ?>


        <?php global $current_user; ?>

            <?php if ($user->roles[0] == "administrator") { ?>
                <li title="<?php echo $user->user_firstname; ?> has <?php echo $post_count;?> active tasks">
                    <?php if ($options['show_gravatars'] == true) {
                    echo get_avatar( $user->ID , '100');
                    } else {
                        echo "<span class='gravatar-replace'></span>";
                    }?>
                    <strong>
                    <?php if ($user->user_firstname !== "" ) {
                        echo $user->user_firstname . " " . $user->user_lastname;
                    } else {
                        echo "Captain Noname";
                    }
                    ?></strong>
                    <em>
                    <?php global $current_user;
                    if ($user->roles[0] == "administrator") {
                        if ($options['admin_title'] !== "") {
                            echo $options['admin_title'] . "<span class='admin-icon' title='Administrator'></span>";
                        } else {
                            echo "Administrator<span class='admin-icon' title='Administrator'></span>";
                        }
                    } else {
                    echo "Project Contributor";
                    }
                    ?></em>
                    <span><?php echo $post_count;?> Tasks</span>
                </li>
            <?php } ?>

            <?php if ($user->roles[0] == "editor") { ?>
                <li title="<?php echo $user->user_firstname; ?>">
                    <?php if ($options['show_gravatars'] == true) {
                    echo get_avatar( $user->ID , '100');
                    } else {
                        echo "<span class='gravatar-replace'></span>";
                    }?>
                    <strong><?php if ($user->user_firstname !== "" ) {
                    echo $user->user_firstname . " " . $user->user_lastname;
                    }
                    ?></strong>
                    <em>Project Contributor</em>
                    <span class="pc-icon" title="Project Contrributor">P</span>
                    <span><?php echo $post_count;?> Tasks</span>
                </li>
            <?php } ?>

            <?php if ($user->roles[0] == "client") { ?>
                <li title="<?php echo $user->user_firstname; ?>">
                    <?php  ?>
                    <?php if ($options['show_gravatars'] == true) {
                    echo get_avatar( $user->ID , '100');
                    } else {
                        echo "<span class='gravatar-replace'></span>";
                    }?>
                    <strong><?php if ($user->user_firstname !== "" ) {
                    echo $user->user_firstname . " " . $user->user_lastname;
                    }
                    ?></strong>
                    <em>Client</em>
                    <span class="client-icon" title="Client">C</span>
                    <span>Observer</span>
                </li>
            <?php } ?>

        <?php }
        ?>

        <?php global $current_user;
        if ( current_user_can('manage_options') ) { ?>
        <li title="Add a new user" class="add-new-user">
            <a href="<?php echo get_admin_url(); ?>user-new.php"><img alt='Add New User' src='<?php echo get_template_directory_uri(); ?>/images/icon-add.png' class='avatar' height='100' width='100' />
            <strong>Add a <br />new user</strong>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<!--/ End Users List /-->
