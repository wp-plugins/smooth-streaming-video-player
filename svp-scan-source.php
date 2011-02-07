<?php

// Sécurité
if ( function_exists( 'current_user_can' ) && ! current_user_can( 'svp_edit_source' ) )
{ 
	wp_die( __( 'Cheatin&#8217; uh?' ) );
	exit();
}
if ( ! user_can_access_admin_page() )
{
	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	exit();
}

// Add some includes
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

// Récupère les données de la source à scanner
$class->read( $params['ID'] );

// Vérifie que la source existe réellement
$id = $class->get_ID();
if ( empty( $id ) )
{
	wp_die( __( 'This source of videos is undefined or does not exist.', 'svp-translate' ) );
	exit();
}

// Soumission du formulaire (suppression de l'ensemble des données de la source)
$videos = null;
$submitted = false;
$button = __( 'Launch scan', 'svp-translate' );
if ( $_POST['svp_source_valid'] )
{
	$submitted = true;
	$videos = $class->scan( $params['ID'] );
	if ( ! empty( $videos['added'] ) || ! empty( $videos['deleted'] ) || ! empty( $videos['unchanged'] ) )
		$button = __( 'Relaunch scan', 'svp-translate' );
}
else // Vidéos actuellement liées à la source
{
	$videos['linked'] = $class->get_videos();
}
?>

<form action="admin.php?page=svp-scan-source&type=<?php print $params['type']; ?>&ID=<?php print $params['ID']; ?>" method="post">
	<?php wp_nonce_field( 'svp-scan-source', '_svp_scan_source_nonce' ); ?>
	<div class="wrap">
		<div id="icon-svp-silverlight" class="icon32"><br /></div>
		<h2><?php printf( __( 'Scan source &laquo;&nbsp;%s&nbsp;&raquo;', 'svp-translate' ), $class->get_name() ); ?></h2>
		<?php
		if ( ! empty( $message ) )
			print '<div id="message" class="' . $message_type . ' fade"><p>' . $message . '</p></div>';
		?>
		<p><a href="admin.php?page=svp-settings"><?php _e( '&larr; Back to Settings' ); ?></a></p>
		<?php if ( ( empty( $videos['added'] ) && empty( $videos['deleted'] ) && empty( $videos['unchanged'] ) ) && ! $submitted ): ?>
		<p><?php _e( 'To get and save in database the videos stocked on your source of videos, click on &laquo;&nbsp;Launch scan&nbsp;&raquo; button.', 'svp-translate' ); ?></p>
			<?php if ( is_array( $videos['linked'] ) && count( $videos['linked'] ) > 0 ): ?>
				<p><?php _e( 'The following videos are currently linked to your source :', 'svp-translate' ); ?></p>
				<ol>
					<?php foreach ( $videos['linked'] as $video ): ?>
						<li><?php print $video->get_filename(); ?></li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		<?php else: ?>
			<?php if ( ! empty( $videos['added'] ) ): ?>
			<p><?php printf( __( 'We have found this (these) <strong>%d video(s)</strong> to add on your source of videos :', 'svp-translate' ), count( $videos['added'] ) ); ?></p>
			<ol>
				<?php foreach ( $videos['added'] as $video ): ?>
					<li><?php print $video; ?></li>
				<?php endforeach; ?>
			</ol>
			<?php endif; ?>
			<?php if ( ! empty( $videos['deleted'] ) ): ?>
			<p><?php printf( __( 'We have found this (these) <strong>%d video(s)</strong> to delete from your source of videos :', 'svp-translate' ), count( $videos['deleted'] ) ); ?></p>
			<ol>
				<?php foreach ( $videos['deleted'] as $video ): ?>
					<li><?php print $video; ?></li>
				<?php endforeach; ?>
			</ol>
			<?php endif; ?>
			<?php if ( ! empty( $videos['unchanged'] ) ): ?>
			<p><?php printf( __( 'This (these) following <strong>%d video(s)</strong> are unchanged :', 'svp-translate' ), count( $videos['unchanged'] ) ); ?></p>
			<ol>
				<?php foreach ( $videos['unchanged'] as $video ): ?>
					<li><?php print $video; ?></li>
				<?php endforeach; ?>
			</ol>
			<?php endif; ?>
			<p><?php _e( 'This (these) video(s) has (have) been correctly updated in database, but if you want, you can relaunch a new scan clicking on &laquo;&nbsp;Relaunch scan&nbsp;&raquo; button.', 'svp-translate' ); ?></p>
			<?php if ( empty( $videos['added'] ) && empty( $videos['deleted'] ) && empty( $videos['unchanged'] ) ): ?>
			<p><?php _e( '<strong>No video</strong> found on this source of videos.', 'svp-translate' ); ?></p>
			<p><?php _e( 'Check your source settings, add some videos on your source and relaunch a scan here.', 'svp-translate' ); ?></p>
			<?php endif; ?>
		<?php endif; ?>
		<input type="hidden" name="svp_source_valid" value="1" />
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php print $button; ?>" />
		</p>
	</div>
</form>