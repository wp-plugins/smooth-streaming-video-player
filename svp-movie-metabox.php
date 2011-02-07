<?php
include_once ("includes/svp-utils.php");
$utils = new SVP_Utils();

include_once ("includes/svp-movies.php");
$movies = new SVP_Movies();

global $post;

// Options globales
$options = get_option("svp_settings");

$has_movie = $movies->has_movie_file_entry($post->ID);

// Récupère les options locales (surcharge les options globales)
if ($has_movie == true)
{
	$options = array_merge($options, $movies->get_locale_options($post->ID));
	$filename = $movies->get_movie_file_entry($post->ID, SVP_USER_AGENT_OTHER);
}

?>

<div id="svp-movie-metabox-container">
	<div id="svp-movies-list">
		<ul id="svp-movies-items">
			<li class="waiting"><span><?php _e("Loading...", "svp-translate"); ?></span></li>
		</ul>
	</div>

	<div id="svp-movie-current">
		<div class="svp-movies-wrapper">
			<p class="svp-message"><?php 
			if ($has_movie == false) _e("None smooth streaming movie is actually associated with this post. Click on left list to choose one.", "svp-translate"); 
			else printf(__("You have associated with this post the named smooth streaming movie&nbsp;: &laquo;&nbsp;%s&nbsp;&raquo;", "svp-translate"), $filename); ?></p>
			
			<div id="svp-movie-current-options" <?php if ($has_movie == false) print 'style="display: none;"'; ?>>
				<p class="svp-option-title"><strong><?php _e("Player dimensions", "svp-translate"); ?></strong></p>
				<p>
					<label for="svp_player_width"><?php _e("Width in pixels", "svp-translate"); ?></label>&nbsp;<input type="text" value="<?php print $options["svp_player_width"]; ?>" name="svp_player_width" id="svp_player_width" class="small-text code" />
					<label for="svp_player_height"><?php _e("Height in pixels", "svp-translate"); ?></label>&nbsp;<input type="text" value="<?php print $options["svp_player_height"]; ?>" name="svp_player_height" id="svp_player_height" class="small-text code" />
				</p>
				<p class="svp-last"><a class="preview button" href="javascript:void(0);" onclick="SvpMetaboxManager.Save();"><?php _e("Save", "svp-translate"); ?></a></p>
			</div>
		</div>
	</div>
	<div style="clear: both;"></div>
</div>

<input type="hidden" value="<?php print $filename; ?>" name="svp_selected_movie" id="svp-selected-movie" />

<!-- SVP JS Metabox Messages -->
<script type="text/javascript">
//<![CDATA[

jQuery(document).ready(function($) {
	
	// Messages
	SvpMetaboxManager.messages = [
		"<?php _e("None smooth streaming movie is actually associated with this post. Click on left list to choose one.", "svp-translate"); ?>",
		"<?php _e("You have associated with this post the named smooth streaming movie&nbsp;: [movie]", "svp-translate"); ?>",
		"<?php _e("You must publish the post before writing movie datas in database.", "svp-translate"); ?>",
		"<?php _e("An error occurred writing movie datas in database or there is not datas to update.", "svp-translate"); ?>",
		"<?php _e("Movie options has been correctly updated.", "svp-translate"); ?>"
	];
	
	// Informations de l'article
	SvpMetaboxManager.postID = <?php print $post->ID; ?>; // 0 = nouvel article
	
	// Ajax nonces
	SvpMetaboxManager._ajax_nonce_get_movies = "<?php print wp_create_nonce("svp-get-movies"); ?>";
	SvpMetaboxManager._ajax_nonce_add_movie_to_post = "<?php print wp_create_nonce("svp-add-movie-to-post"); ?>";
	SvpMetaboxManager._ajax_nonce_save_movie_to_post = "<?php print wp_create_nonce("svp-save-movie-to-post"); ?>";
	SvpMetaboxManager._ajax_nonce_delete_post_movie = "<?php print wp_create_nonce("svp-delete-post-movie"); ?>";
	
	// Chargemment de la liste des vidéos
	SvpMetaboxManager.Load();
	
});
//]]>
</script>