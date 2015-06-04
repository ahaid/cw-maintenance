<?php
/*
Template Name: Stream
*/
get_header(); ?>


<?php
    // Task Rocket Custom Plugin for Vivo Energy UK Services Ltd.
    // This plugin will only work with Task Rocket (http://taskrocket.info/)
    // Author: Michael Ott
    // @mikeyott | hello@michaelott.id.au | www.michaelott.id.au

    // Instructions:
    // 1) Copy page-stream.php into the taskrocket theme directory.
    // 2) Copy this plugin into the plugins directory.
    // 3) Activate the plugin.
    // 4) Go to http://yourdomain.com/stream to view the pledge stream.

    // Feel free to get tweaky below except where commented "Do not touch".

    // SETTINGS ------------------------------------------------------------//
    $pledgesdisplayed = 2;      // How many pledges to show at any one time (must be 1, 2, or 3).
    $secs = 4;                  // Length of the pause after cross-fade (seconds).
    $timer = $secs * 1000;      // Do not touch.
    $fadetime = 1000;           // Duration of the cross-fade (milliseconds).
    $numberpledges = 10;        // Number of pledges to output per column.
    $mins = 1;                  // Reload the page after this many minutes.
    $reload = $mins * 60000;    // Do not touch.
    // ---------------------------------------------------------------------//


    // DOCS ----------------------------------------------------------------//

    // *** Pledges Displayed at any given time (AKA $pledgesdisplayed)
    // Exactly how it sounds. I've limited it to 3 for presentation purposes.

    // *** Secs (AKA $secs)
    // How long to display a pledge (pledges) before cycling to the next.

    // *** Fade Time (AKA $fadetime)
    // Exactly how it sounds. The durarion of the cross-fade animation.

    // *** Number of Pledges (AKA $numberpledges)
    // This is how many pledges will be cycled in each column before startign again.

    // *** Mins (AKA $mins)
    // This is how often the page will reload. Each time the page loads the pledges
    // specified in $numberpledges are loaded in random order. This might be helpful
    // so that you don't have to load all pledges at once (which depending on how many
    // there are could make the database work too hard), and instead you could just
    // load a couple of hundred pledges and have the page reload every 10 minutes, which
    // would load 100 new random pledges.
?>

<!--/ Start Stream /-->
<?php if($pledgesdisplayed > 0 && $pledgesdisplayed < 4 ) { ?>
<div class="stream">

<script>
    $(function() {

        <?php if($pledgesdisplayed == 1 || $pledgesdisplayed == 2 || $pledgesdisplayed == 3) { ?>
        $(".slides1 > div:gt(0)").hide();
        setInterval(function() {
          $('.slides1 > div:first')
            .fadeOut(<?php echo $fadetime; ?>)
            .next()
            .fadeIn(<?php echo $fadetime; ?>)
            .end()
            .appendTo('.slides1');
        },  <?php echo $timer; ?>);
        <?php } ?>

        <?php if($pledgesdisplayed == 2 || $pledgesdisplayed == 3) { ?>
        $(".slides2 > div:gt(0)").hide();
        setInterval(function() {
          $('.slides2 > div:first')
            .fadeOut(<?php echo $fadetime; ?>)
            .next()
            .fadeIn(<?php echo $fadetime; ?>)
            .end()
            .appendTo('.slides2');
        },  <?php echo $timer; ?>);
        <?php } ?>

        <?php if($pledgesdisplayed == 3) { ?>
        $(".slides3 > div:gt(0)").hide();
        setInterval(function() {
          $('.slides3 > div:first')
            .fadeOut(<?php echo $fadetime; ?>)
            .next()
            .fadeIn(<?php echo $fadetime; ?>)
            .end()
            .appendTo('.slides3');
        },  <?php echo $timer; ?>);
        <?php } ?>

        // Reload page
        setTimeout(function(){
           window.location.reload(1);
        }, <?php echo $reload; ?>);

    });
</script>

    <?php if($pledgesdisplayed == 1 || $pledgesdisplayed == 2 || $pledgesdisplayed == 3) { ?>
	<!--/ Start Slides 01 /-->
    <div class="slides1<?php if($pledgesdisplayed == 1) { echo " full-width"; } else if($pledgesdisplayed == 2) { echo " half-width";  } else { echo " one-third-width"; } ?>">
		<?php
			$cat_posts = get_posts('numberposts=' . $numberpledges . '&orderby=rand');
			foreach($cat_posts as $post) : setup_postdata($post);
		?>
            <div class="slide">
                <p>
                    <span class="pledge"><?php the_title(); ?></span>
                    <span class="name"><?php echo get_the_author(); ?></span>
                </p>
            </div>

		<?php endforeach; wp_reset_postdata(); ?>
    </div>
    <!--/ End Slides 01 /-->
    <?php } ?>


    <?php if($pledgesdisplayed == 2 || $pledgesdisplayed == 3) { ?>
    <!--/ Start Slides 02 /-->
    <div class="slides2<?php if($pledgesdisplayed == 1) { echo " full-width"; } else if($pledgesdisplayed == 2) { echo " half-width";  } else { echo " one-third-width"; } ?>">
        <?php
			$cat_posts = get_posts('numberposts=' . $numberpledges . '&orderby=rand');
			foreach($cat_posts as $post) : setup_postdata($post);
		?>
            <div class="slide">
                <p>
                    <span class="pledge"><?php the_title(); ?></span>
                    <span class="name"><?php echo get_the_author(); ?></span>
                </p>
            </div>

		<?php endforeach; wp_reset_postdata(); ?>
    </div>
    <!--/ End Slides 02 /-->
    <?php } ?>


    <?php if($pledgesdisplayed == 3) { ?>
    <!--/ Start Slides 03 /-->
    <div class="slides3<?php if($pledgesdisplayed == 1) { echo " full-width"; } else if($pledgesdisplayed == 2) { echo " half-width";  } else { echo " one-third-width"; } ?>">
        <?php
			$cat_posts = get_posts('numberposts=' . $numberpledges . '&orderby=rand');
			foreach($cat_posts as $post) : setup_postdata($post);
		?>
            <div class="slide">
                <p>
                    <span class="pledge"><?php the_title(); ?></span>
                    <span class="name"><?php echo get_the_author(); ?></span>
                </p>
            </div>

		<?php endforeach; wp_reset_postdata(); ?>
    </div>
    <!--/ End Slides 03 /-->
    <?php } ?>


</div>
<?php } else { ?>
    <h1>The $pledgesdisplayed setting can only be 1, 2, or 3.</h1>
<?php } ?>
<!--/ End Stream /-->

<?php get_footer(); ?>
