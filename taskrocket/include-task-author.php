<div class="author roundness">
	<?php $options = get_option( 'taskrocket_settings' ); ?>
    <?php if ($options['show_gravatars'] == true) { echo get_avatar( get_the_author_meta('ID'), 100 ); } else { echo "<div class='circle'></div>"; } ?>
    <span class="name<?php if ($options['show_gravatars'] == false) { echo " name-margin"; } ?>">
	<?php if (get_the_author_meta( 'first_name') !== "" ) {
    echo get_the_author_meta( 'first_name' ) . " " . get_the_author_meta( 'last_name' );
    } else {
    echo "Captain Noname";
    }
	if ($options['admin_title'] !== "") {
        $user_role = get_the_author_meta('user_level'); if ($user_role == 10 ) { 
    echo "<span class='admin-title roundness' title='" . $options['admin_title'] . "'>" . $options['admin_title'] . "</span>";
      }
	}
    ?></span>
</div>