<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header();

// If the user role is 'client' redirect to the client page.
if( current_user_can('client')) { 
	header('Location: '.home_url().'/client');
	exit();
}

?>
    <div class="content">
    
        <!--/ Start 404 Notice /-->
        <div class="notfound">
        	<h1>404: Don't Freak Out</span></h1>
            <p>The page may have existed at some time, but it doesn't now anyway. The search field in the left pane is your best bet to find what you're looking for.</p>
        </div>
        <!--/ End 404 Notice /-->
    </div>

<?php get_footer(); ?>