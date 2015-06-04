<?php
if (do_shortcode("[licensed_for]") != 0) {
	$lic = do_shortcode("[licensed_for]");
	if ($total_of_editors_admins > $lic) { ?>
	<div class="alert">
		You have more users (<?php echo $total_of_editors_admins; ?>) than you are licensed for (<?php echo do_shortcode("[licensed_for]"); ?>). Please <a href="http://taskrocket.info/contact" target="_blank">enquire</a> about upgrading.
	</div>
<?php } 
} ?>