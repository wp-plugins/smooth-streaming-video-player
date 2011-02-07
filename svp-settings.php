<?php

// Sécurité
if (function_exists( 'current_user_can' ) && ! current_user_can( 'svp_update_settings' ) ) 
	wp_die( __( 'Cheatin&#8217; uh?' ) );
if ( ! user_can_access_admin_page() )
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	
// Inclusions
require_once( 'includes/class-utils.php' );
require_once( 'includes/class-source.php' );

// Instanciation de la classe des utilitaires
$utils = new SVP_Utils();
	
// Message
$message = '';

$options = get_option( 'svp_settings' );

// Soumission du formulaire
if ( isset( $_POST['submit'] ) )
{
	$message_type = 'updated';
	if ( check_admin_referer( 'svp-update-settings', '_svp_update_settings_nonce' ) )
	{
		$svp_player_width = (int) ( $_POST['svp_player_width'] );
		$svp_player_height = (int) ( $_POST['svp_player_height'] );
		$svp_items_count = (int) ( $_POST['svp_items_count'] );
		$options = array(
			'svp_player_width' => $utils->form_post_check( $options['svp_player_width'], $svp_player_width, 'positive' ), 
			'svp_player_height' => $utils->form_post_check( $options['svp_player_height'], $svp_player_height, 'positive' ),
			'svp_items_count' => $utils->form_post_check( $options['svp_items_count'], $svp_items_count, 'integer' )
			);
		
		// Met à jour la base de données
		update_option( 'svp_settings', $options);
		
		// Affiche un message de confirmation
		$message = __( 'Settings have been correctly updated.', 'svp-translate' );
	}
	else
	{
		$message_type = 'error';
		$message = __( 'A security error occured.', 'svp-translate' );	
	}
}

$source = new SVP_Source();
$sources = $source->get_sources();
?>
<form action="admin.php?page=svp-settings" method="post" name="settings" id="svp-settings-form">
	<?php wp_nonce_field( 'svp-update-settings', '_svp_update_settings_nonce' ); ?>
	<div class="wrap">
		<div id="icon-svp-silverlight" class="icon32"><br /></div>
		<h2><?php _e( 'Settings', 'svp-translate' ); ?></h2>
		<?php
		if ( ! empty($message) )
			print '<div id="message" class="' . $message_type . ' fade"><p>' . $message . '</p></div>';
		?>
		<!-- Main parameters -->
		<h3><?php _e( 'Main parameters', 'svp-translate' ); ?></h3>
		<div class="metabox-holder">
			<div id="post-body">
				<div id="post-body-content">
					<!-- Player configuration -->
					<div class="stuffbox">
						<h3><label for="svp_player_width"><?php _e( 'Default player style', 'svp-translate' ); ?></label></h3>
						<div class="inside">
							<table class="form-table">
								<tr valign="top">
									<th scope="row"><label for="svp_player_width"><?php _e( 'Player width', 'svp-translate' ) ?></label></th>
									<td><input name="svp_player_width" type="text" id="svp_player_width" value="<?php print $options['svp_player_width']; ?>" class="small-text code" /> pixels</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="svp_player_height"><?php _e( 'Player height', 'svp-translate' ) ?></label></th>
									<td><input name="svp_player_height" type="text" id="svp_player_height" value="<?php print $options['svp_player_height']; ?>" class="small-text code" /> pixels</td>
								</tr>
							</table>
							<br />
						</div>
					</div>
					<!-- Media RSS configuration -->
					<div class="stuffbox">
						<h3><label for="svp_player_width"><?php _e( 'Media RSS publication', 'svp-translate' ); ?></label></h3>
						<div class="inside">
							<table class="form-table">
								<tr valign="top">
									<th scope="row"><label for="svp_items_count"><?php _e( 'Items count', 'svp-translate' ) ?></label></th>
									<td>
										<input name="svp_items_count" type="text" id="svp_items_count" value="<?php print $options['svp_items_count']; ?>" class="small-text code" />
										<p><?php _e( 'You can put 0 to indicate an illimited count.', 'svp-translate' ); ?></p>
									</td>
								</tr>
							</table>
							<br />
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Videos sources list -->
		<h3><?php _e( 'Videos sources', 'svp-translate' ); ?></h3>
		<div class="metabox-holder">
			<?php if ( count( $sources ) == 0 ): ?>
			<p><?php _e( 'No videos source defined. Click on "Add source" button below to add a new source.', 'svp-translate' ); ?></p>
			<?php
			else:
			?>
			<table class="widefat post fixed">
				<thead>
					<tr>
						<th class="manage-column column-id" id="id" scope="col"><?php _e( 'ID', 'svp-translate' ); ?></th>
						<th class="manage-column column-name" id="name" scope="col"><?php _e( 'Name', 'svp-translate' ); ?></th>
						<th class="manage-column column-type" id="type" scope="col"><?php _e( 'Source type', 'svp-translate' ); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th class="manage-column column-id" id="id" scope="col"><?php _e( 'ID', 'svp-translate' ); ?></th>
						<th class="manage-column column-name" id="name" scope="col"><?php _e( 'Name', 'svp-translate' ); ?></th>
						<th class="manage-column column-type" id="type" scope="col"><?php _e( 'Source type', 'svp-translate' ); ?></th>
					</tr>
				</tfoot>
				<tbody>
				<?php 
				$i = 0;
				foreach ( $sources as $item ):
					($i % 2 == 0) ? $alternate = 'alternate' : $alternate = '';
				?>
					<tr class="iedit<?php print ' ' . $alternate; ?>" valign="top" id="source-<?php print $item->ID; ?>">
						<td class="source-id column-id">#<?php print $item->ID; ?></td>
						<td class="source-name column-name">
							<strong>
								<?php if ( $item->is_configured == 1 ): ?>
								<a title="<?php _e( 'Edit this source', 'svp-translate' ); ?>" href="" class="row-title"><?php print $item->name; ?></a>
									<?php if ( $item->is_scanned == 0 ): _e( '(this source must be scanned)', 'svp-translate' ); endif; ?>
								<?php else: ?>
								<abbr class="required"><?php _e( 'This configuration must be completed.', 'svp-translate' ); ?></abbr>
								<?php endif; ?>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a title="<?php _e( 'Edit this source', 'svp-translate' ); ?>" href="admin.php?page=svp-source&type=<?php print strtolower( $item->code ); ?>&ID=<?php print $item->ID; ?>"><?php _e( 'Edit', 'svp-translate' ); ?></a>
								</span>
								<?php if ( $item->is_configured == 1 ): ?>
								|
								<span class="scan">
									<a title="<?php _e( 'Scan videos on this source', 'svp-translate' ); ?>" href="admin.php?page=svp-scan-source&type=<?php print strtolower( $item->code ); ?>&ID=<?php print $item->ID; ?>"><?php _e( 'Scan', 'svp-translate' ); ?></a>
								</span>
								<?php endif; ?>
								|
								<span class="trash">
									<a title="<?php _e( 'Delete this source and all attached videos', 'svp-translate' ); ?>" href="admin.php?page=svp-delete-source&type=<?php print strtolower( $item->code ); ?>&ID=<?php print $item->ID; ?>"><?php _e( 'Delete', 'svp-translate' ); ?></a>
								</span>
							</div>
						</td>
						<td class="source-type column-type"><?php print $item->type; ?></td>
					</tr>
				<?php 
					$i++;
				endforeach; 
				?>
				</tbody>
			</table>
			<?php endif; ?>
			<div class="tablenav">
				<div class="alignleft actions">
					<select name="svp_source_type" id="svp-source-type">
						<?php foreach ( $source->get_source_types() as $type ): ?>
						<option value="<?php print strtolower( $type->code ); ?>"><?php print $type->label; ?></option>
						<?php endforeach; ?>
					</select>
					<a class="button" href="javascript:Source.Add();"><?php _e( 'Add source', 'svp-translate' ); ?></a>
				</div>
			</div>
		</div>
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Saves changes', 'svp-translate' ) ?>" /></p>
	</div>
</form>
<script type="text/javascript">
//<![CDATA[
var Source = {
	Add: function() {
		var url = '<?php print 'admin.php?page=svp-source&type='; ?>';
		url += jQuery('#svp-source-type').val();
		document.location.href = url;
	}
}
//]]>
</script>