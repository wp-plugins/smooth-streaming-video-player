<?php

require_once(dirname(__FILE__) . "/" . "../../../wp-load.php");

// Sécurité
if (function_exists("current_user_can") && !current_user_can("manage_options")) 
	wp_die(__("You do not have sufficient permissions to access this page."));

wp_enqueue_style('global');
wp_enqueue_style('wp-admin');
wp_enqueue_style('media');

wp_enqueue_style("svp-post", plugins_url(get_plugin_dirname() . "/styles/post.css"), array(), false, "screen");

wp_enqueue_script("jquery");
wp_enqueue_script("svp-metabox-js", plugins_url(get_plugin_dirname() . "/scripts/metabox.js"));

?>
<html>
	<head>
		<title><?php _e("Smooth Streaming Movie", "svp-translate"); ?></title>
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php print get_option('blog_charset'); ?>" />
		<?php
		do_action('admin_print_styles');
		do_action('admin_print_scripts');
		
		wp_admin_css('colors-fresh', true);
		?>
	</head>
	<body id="media-upload">
		<form class="media-upload-form type-form validate" method="post" action="">
			<h3 class="media-title"><?php _e("Include a smooth streaming movie in your post content"); ?></h3>
			<ul id="svp-movies-items">
				<li class="waiting"><span><?php _e("Loading...", "svp-translate"); ?></span></li>
			</ul>
			<p class="howto"><?php _e("After selection of movie, you could specifiy some options.", "svp-translate"); ?></p>
			
			<input type="hidden" value="" name="svp_selected_movie" id="svp-selected-movie" />
			
		</form>
		
		<!-- SVP JS Metabox Messages -->
		<script type="text/javascript">
		//<![CDATA[
		
		var ajaxurl = "<?php print admin_url('admin-ajax.php'); ?>";
		
		jQuery(document).ready(function($) {
	
			// Messages
			SvpMetaboxManager.messages = [
				"<?php _e("None smooth movie is actually associated with this post. Click on left list to choose one.", "svp-translate"); ?>",
				"<?php _e("You have associated with this post the named smooth movie : [movie]", "svp-translate"); ?>",
				"<?php _e("You must publish the post before to choose the movie to add.", "svp-translate"); ?>"
			];
	
			// Informations de l'article
			SvpMetaboxManager.postID = <?php print (int)$_GET["post"]; ?>;
	
			// Ajax nonces
			SvpMetaboxManager._ajax_nonce_get_movies = "<?php print wp_create_nonce("svp-get-movies"); ?>";
			SvpMetaboxManager._ajax_nonce_add_movie_to_post = "<?php print wp_create_nonce("svp-add-movie-to-post"); ?>";
	
			// Chargemment de la liste des vidéos
			SvpMetaboxManager.Load();
	
		});

		//]]>
		</script>
		
	</body>
</html>