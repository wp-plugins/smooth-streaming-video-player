<?php

// Sécurité
if ( function_exists( 'current_user_can' ) && ! current_user_can( 'svp_edit_source' ) ) 
	wp_die( __( 'Cheatin&#8217; uh?' ) );
if ( ! user_can_access_admin_page() )
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

// Inclusions
require_once( 'includes/class-source.php' );

// Message
$message = '';

// Initialise une liste de paramètres
$params = array( 'type' => null, 'ID' => null );

// Récupère le type de la source à éditer (indispensable pour récupérer le formulaire de configuration correspondant)
( $_GET['type'] ) ? $params['type'] = strtolower( $_GET['type'] ) : $params['type'] = strtolower( $_POST['svp_source_type'] );

// Récupère l'identifiant de la source à éditer
( $_GET['ID'] ) ? $params['ID'] = $_GET['ID'] : $params['ID'] = null;

// Instancie la classe de source en fonction du type
$source = new SVP_Source();
$class = null;
if ( ! empty( $params['type'] ) )
	$class = $source->factory( $params['type'] );

// Crée une nouvelle source en fonction du type si le paramètre ID est vide
if ( empty( $params['ID'] ) && ! empty( $params['type'] ) && ! empty( $class ) )
{
	$class->save( 
		null, 
		array( 
				'name' => null, 
				'source_type_code' => strtoupper( $params['type'] ), 
				'is_configured' => 0, 
				'is_scanned' => 0,
				'options' => null ) );
	$params['ID'] = $class->get_ID();
}

// Récupère les données à éditer
if ( ! empty( $params['ID'] ) && ! empty( $class ) )
	$class->read( $params['ID'] );

// Sauvegarde (soumission du formulaire)
if ( $_POST['svp_source_valid'] && ! empty( $class ) && ! empty( $params['ID'] ) && ! empty( $params['type'] ) )
{
	$message_type = 'updated';
	if ( check_admin_referer( 'svp-update-source', '_svp_update_source_nonce' ) )
	{
		$options = array();
		foreach ( $_POST as $key => $value )
		{
			if ( in_array( $key, $class->get_input_list() ) )
			{
				if ( ! empty( $value ) )
					$options[$key] = $value;
			}
		}
		
		if ( $class->check( $_POST ) )
		{
			$class->save( 
				$params['ID'], 
				array( 
						'name' => $name = ( ! empty ( $_POST['svp_source_name'] ) ? $_POST['svp_source_name'] : null ), 
						'source_type_code' => strtoupper( $params['type'] ), 
						'is_configured' => 1, 
						'is_scanned' => 0, 
						'options' => $options = ( sizeof( $options ) > 0 ? serialize( $options ) : null ) ) );
			
			// Affiche un message de confirmation
			$message = __( 'Your configuration settings have been correctly updated.', 'svp-translate' );
		}
		else
		{
			$class->set_name( $_POST['svp_source_name'] );
			$class->set_options( serialize( $options ) );
			$message_type = 'error';
			$message = __( 'Incorrect values&nbsp;: check your form configuration values.', 'svp-translate' );
		}
	}
	else
	{
		$message_type = 'error';
		$message = __( 'A security error occured.', 'svp-translate' );
	}
}

// Construit le titre en fonction des paramètres
$title = __( 'Select source type', 'svp-translate' ); // Default value
$name = '';
$id = 0;
if ( ! is_null( $class ) && ! is_null( $class->get_ID() ) )
{
	$name = $class->get_name();
	$id = $class->get_ID();
}
if ( ! empty( $name ) )
	$title = sprintf( __( 'Edit source &laquo;&nbsp;%s&nbsp;&raquo; (ID #%d)', 'svp-translate' ), $name, $id );
else
{
	if ( ! empty( $params['type'] ) )
	{
		$type = $source->get_name( strtoupper( $params['type'] ) );
		$title = sprintf( __( 'Edit source &laquo;&nbsp;%s&nbsp;&raquo; type (ID #%d)', 'svp-translate' ), $type->label, $id );
	}
}

// Construit le nom du bouton de validation en fonction des paramètres
$button = __( 'Save', 'svp-translate' ); // Default value
if ( empty( $params['type'] ) )
	$button = __( 'Valid', 'svp-translate' );

// Construit l'URL de soumission du formulaire en fonction des paramètres
$url = 'admin.php?page=svp-source';
foreach ( $params as $key => $value )
{
	if ( ! empty( $value ) )
		$url .= '&' . $key . '=' . $value;
}
?>

<form action="<?php print $url; ?>" method="post">
	<?php wp_nonce_field( 'svp-update-source', '_svp_update_source_nonce' ); ?>
	<div class="wrap">
		<div id="icon-svp-silverlight" class="icon32"><br /></div>
		<h2><?php print $title; ?></h2>
		<?php
		if ( ! empty( $message ) )
			print '<div id="message" class="' . $message_type . ' fade"><p>' . $message . '</p></div>';
		?>
		<div class="metabox-holder" id="poststuff">
			<div id="post-body">
				<div id="post-body-content">
					<p><a href="admin.php?page=svp-settings"><?php _e( '&larr; Back to Settings' ); ?></a></p>
					<?php 
					if ( ! empty( $class ) ): // Classe du type de source définie
						print $class->configure();
					?>
					<input type="hidden" name="svp_source_valid" value="1" />
					<?php 
					else: // Classe du type de source non définie: sélection du type de source obligatoire
					?>
					<div class="stuffbox">
						<h3><?php _e( 'Source type selection', 'svp-translate' ); ?></h3>
						<table class="form-table">
						<tr valign="top">
							<th scope="row">
								<label for="svp_source_type"><?php _e( 'Videos source type', 'svp-translate' ) ?></label>
							</th>
							<td>
								<select name="svp_source_type" id="svp-source-type">
								<?php foreach ( $source->get_source_types() as $type ): ?>
									<option value="<?php print strtolower( $type->code ); ?>"><?php print $type->label; ?></option>
								<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
					<br />
				</div>
				<?php
				endif;
				?>
				</div>
			</div>
		</div>
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php print $button; ?>" /></p>
	</div>
</form>