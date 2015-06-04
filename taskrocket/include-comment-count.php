<?php 
$options = get_option( 'taskrocket_settings' ); 
if ($options['allow_comments'] == true) { ?>
	<?php $comment_link = get_permalink(); ?>
    <?php if (get_comments_number() > 1) { ?>
    <a href="<?php echo $comment_link; ?>#comment-area" class="comment-num"><?php echo comments_number( '0', '1', '%' ); ?> comments</a>
    <?php } ?>
    
    <?php if (get_comments_number() == 1) { ?>
    <a href="<?php echo $comment_link; ?>#comment-area" class="comment-num"><?php echo comments_number( '0', '1', '%' ); ?> comment</a>
    <?php } ?>
<?php } ?>