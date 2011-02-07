<?php

// Sécurité
if ( function_exists( 'current_user_can' ) && ! current_user_can( 'svp_delete_source' ) ) 
	wp_die( __( 'Cheatin&#8217; uh?' ) );
if ( ! user_can_access_admin_page() )
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

// Inclusions
require_once( 'includes/class-source.php' );

// Message
$message = '';

// Type du message pour l'affichage
$message_type = 'updated';

// Initialise une liste de paramètres
$params = array( 'type' => null, 'ID' => null );

// Teste et récupère les paramètres
if ( ! isset( $_GET['ID'] ) || empty( $_GET['ID'] ) )
{
	wp_die( __( 'Parameter ID is undefined.', 'svp-translate' ) );
	exit();
}
if ( ! isset( $_GET['type'] ) || empty( $_GET['type'] ) )
{
	wp_die( __( 'Parameter type is undefined.', 'svp-translate' ) );
	exit();
}
$params['ID'] = $_GET['ID'];
$params['type'] = $_GET['type'];

// Instancie la classe de source en fonction du type
$source = new SVP_Source();
$class = $source->factory( $params['type'] );

// Soumission du formulaire (suppression de l'ensemble des données de la source)
if ( $_POST['svp_source_valid'] )
{
	$class->delete( $params['ID'] );
	wp_redirect( 'admin.php?page=svp-settings' );
	exit();
}
else
{
	// Récupère les données de la source à supprimer
	$class->read( $params['ID'] );
	
	// Vérifie que la source existe réellement
	$id = $class->get_ID();
	if ( empty( $id ) )
	{
		wp_die( __( 'This source of videos is undefined or does not exist.', 'svp-translate' ) );
		exit();
	}
	
	// Construit le titre et le label de la source à supprimer
	$name = $class->get_name();
	$title = '';
	$label = '';
	if ( ! empty( $name ) )
	{
		$title = sprintf( __( 'Delete source &laquo;&nbsp;%s&nbsp;&raquo;', 'svp-translate' ), $name );
		$label = sprintf( __( 'ID&nbsp;#%d&nbsp;: %s', 'svp-translate' ), $class->get_ID(), $name );
	}
	else
	{
		$title = __( 'Delete source', 'svp-translate' );
		$label = sprintf( __( 'ID&nbsp;#%d', 'svp-translate' ), $class->get_ID() );
	}
}

?>

<form action="admin.php?page=svp-delete-source&type=<?php print $params['type']; ?>&ID=<?php print $params['ID']; ?>&noheader=true" method="post">
	<?php wp_nonce_field( 'svp-delete-source', '_svp_delete_source_nonce' ); ?>
	<div class="wrap">
		<div id="icon-svp-silverlight" class="icon32"><br /></div>
		<h2><?php print $title; ?></h2>
		<?php
		if ( ! empty( $message ) )
			print '<div id="message" class="' . $message_type . ' fade"><p>' . $message . '</p></div>';
		?>
		<p><a href="admin.php?page=svp-settings"><?php _e( '&larr; Back to Settings' ); ?></a></p>
		<p><?php _e( 'You have specified this source for deletion&nbsp;:', 'svp-translate' ); ?></p>
		<p><strong><?php print $label; ?></strong></p>
		<?php if ( count( $class->get_videos() ) > 0  || count( $class->get_posts() ) > 0 ): ?>
		<p><?php _e( 'Note that the following data will be removed&nbsp:', 'svp-translate' ); ?></p>
	<ul>
			<?php if ( count( $class->get_videos() ) > 0 ): ?><li><?php printf( __( '<strong>%d</strong> video(s) associated with the source', 'svp-translate' ), count( $class->get_videos() ) ); ?></li><?php endif; ?>
			<?php if ( count( $class->get_posts() ) > 0 ): ?><li><?php printf( __( '<strong>%d</strong> source video(s) associated to posts', 'svp-translate' ), count( $class->get_posts() ) ); ?></li><?php endif; ?>
		</ul>
		<?php endif; ?>
		<input type="hidden" name="svp_source_valid" value="1" />
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php _e( 'Confirm deletion', 'svp-translate' ); ?>" /></p>
	</div>
</form>