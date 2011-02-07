<?php

include_once("includes/svp-utils.php");

$utils = new SVP_Utils();

// Infos du plugin
$plugin_data = get_plugin_data(realpath(dirname(__FILE__)) . "/svp-silverlight.php");
$base_name = plugin_basename(realpath(dirname(__FILE__)) . "/svp-settings.php");
$base_page = "admin.php?page=" . $base_name;

// Sécurité
if (function_exists("current_user_can") && !current_user_can("manage_options")) 
	wp_die(__("Cheatin&#8217; uh?"));
if (!user_can_access_admin_page())
	wp_die(__("You do not have sufficient permissions to access this page."));
	
$message = "";
$options = get_option("svp_settings");

// Soumission du formulaire
if (isset($_POST["submit"]))
{
	check_admin_referer("svp-update-settings", "_svp_update_settings_nonce");
	$svp_movies_server_url = strip_tags(trim($_POST["svp_movies_server_url"]));
	$svp_movies_dirname = strip_tags(trim($_POST["svp_movies_dirname"]));
	$svp_player_width = (int)($_POST["svp_player_width"]);
	$svp_player_height = (int)($_POST["svp_player_height"]);
	$svp_items_count = (int)($_POST["svp_items_count"]);
	$options = array(
		"svp_movies_server_url" => $utils->form_post_check($options["svp_movies_server_url"], $svp_movies_server_url, "url"), 
		"svp_movies_dirname" => $utils->form_post_check($options["svp_movies_dirname"], $svp_movies_dirname), 
		"svp_player_width" => $utils->form_post_check($options["svp_player_width"], $svp_player_width, "positive"), 
		"svp_player_height" => $utils->form_post_check($options["svp_player_height"], $svp_player_height, "positive"),
		"svp_items_count" => $utils->form_post_check($options["svp_items_count"], $svp_items_count, "integer")
		);
	
	// Met à jour la base de données
	update_option("svp_settings", $options);
	
	// Affiche un message de confirmation
	$message = __("Settings have been correctly updated.", "svp-translate");
}

?>
<form action="<?php print $base_page; ?>" method="post">
	<?php wp_nonce_field("svp-update-settings", "_svp_update_settings_nonce"); ?>
	<div class="wrap">
		<div id="icon-svp-silverlight" class="icon32"><br /></div>
		<h2><?php _e("Settings", "svp-translate"); ?></h2>
		<?php
		if (!empty($message))
			print '<div id="message" class="updated fade"><p>' . $message . '</p></div>';
		?>
		<div class="metabox-holder" id="poststuff">
			<div id="post-body">
				<div id="post-body-content">
					<!-- Videos server configuration -->
					<div class="stuffbox" id="namediv">
						<h3><label for="svp_movies_server_url"><?php _e("Movies source", "svp-translate"); ?></label></h3>
						<div class="inside">
							<table class="form-table">
								<tr valign="top">
									<th scope="row"><label for="svp_movies_server_url"><?php _e("Movies server web address", "svp-translate") ?></label></th>
									<td>
										<input name="svp_movies_server_url" type="text" id="svp_movies_server_url" value="<?php print $options["svp_movies_server_url"]; ?>" class="regular-text code" />
										<p><?php _e("Example: <code>http://wordpress.org/</code> &#8212; don&#8217;t forget the <code>http://</code>"); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="svp_movies_dirname"><?php _e("Movies directory name", "svp-translate") ?></label></th>
									<td>
										<input name="svp_movies_dirname" type="text" id="svp_movies_dirname" value="<?php print $options["svp_movies_dirname"]; ?>" class="regular-text code" />
										<p><?php _e("This is the directory name that contains your movies on the server.", "svp-translate"); ?></p>
									</td>
								</tr>
							</table>
							<br />
						</div>
					</div>
					<!-- Player configuration -->
					<div class="stuffbox">
						<h3><label for="svp_player_width"><?php _e("Default player style", "svp-translate"); ?></label></h3>
						<div class="inside">
							<table class="form-table">
								<tr valign="top">
									<th scope="row"><label for="svp_player_width"><?php _e("Player width", "svp-translate") ?></label></th>
									<td><input name="svp_player_width" type="text" id="svp_player_width" value="<?php print $options["svp_player_width"]; ?>" class="small-text code" /> pixels</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="svp_player_height"><?php _e("Player height", "svp-translate") ?></label></th>
									<td><input name="svp_player_height" type="text" id="svp_player_height" value="<?php print $options["svp_player_height"]; ?>" class="small-text code" /> pixels</td>
								</tr>
							</table>
							<br />
						</div>
					</div>
					<!-- Media RSS configuration -->
					<div class="stuffbox">
						<h3><label for="svp_player_width"><?php _e("Media RSS publication", "svp-translate"); ?></label></h3>
						<div class="inside">
							<table class="form-table">
								<tr valign="top">
									<th scope="row"><label for="svp_items_count"><?php _e("Items count", "svp-translate") ?></label></th>
									<td>
										<input name="svp_items_count" type="text" id="svp_items_count" value="<?php print $options["svp_items_count"]; ?>" class="small-text code" />
										<p><?php _e("You can put 0 to indicate an illimited count.", "svp-translate"); ?></p>
									</td>
								</tr>
							</table>
							<br />
						</div>
					</div>
				</div>
			</div>
		</div>
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php _e("Saves changes", "svp-translate") ?>" /></p>
	</div>
</form>