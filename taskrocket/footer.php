<?php $options = get_option( 'taskrocket_settings' ); ?>
<?php // If user is a client
	if( !current_user_can('client')) { ?>

<div class="clear"></div>

</div>
<!--/ End Container /-->

<?php // If user is a client
} ?>


<script src="<?php echo get_template_directory_uri(); ?>/js/min/scroller.min.js" type="text/javascript"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/min/common.min.js" type="text/javascript"></script>

<?php wp_footer(); ?>

<span class="toggle-menu">
    <span class="bar-01"></span>
    <span class="bar-02"></span>
    <span class="bar-03"></span>
</span>

<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script>
$(function() {
jQuery('#duedate').datepicker({
dateFormat : 'mm/dd/yy'
});
});
</script>

<div class="mask"></div>
</body>
</html>
