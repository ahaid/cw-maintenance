<?php
$options = get_option( 'taskrocket_settings' );
if ($options['file_uploads'] == true) { ?>
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

if ( $attachments ) { ?>
	<!--/ Start Attachments /-->
    <div class="attachments roundness">
    <strong>Attached Files</strong>
    <ul>
    <?php foreach ( $attachments as $attachment ) {
		$filethumb = wp_get_attachment_thumb_url( $attachment->ID);	 // Path to the thumbnail
		$filepath = wp_get_attachment_url( $attachment->ID);			// Path to the original file
		$filename = $attachment->post_title;
		$filesize = @filesize( get_attached_file( $attachment->ID ) );
		$filesize = size_format($filesize, 2);
		$deleteAttachment = wp_nonce_url(home_url() . "/wp-admin/post.php?action=delete&amp;post=".$attachment->ID."", 'delete-post_' . $attachment->ID); ?>
			<?php if ( wp_attachment_is_image( $attachment->ID ) ) { ?>
            <li class="file-image">
                <a href="<?php echo $filepath; ?>?TB_iframe=true" class="<?php $options = get_option( 'taskrocket_settings' ); if ($options['use_thickbox'] == true) { echo "thickbox"; } ?>" title="<?php echo $filename; ?>"><img src="<?php echo $filethumb; ?>" /></a>
                <a class="delete-attachment-button roundness" title="Delete this image">&#215;</a>
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

<?php }
?>
<?php } ?>
