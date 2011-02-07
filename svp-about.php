<?php

// Sécurité
if ( function_exists( 'current_user_can' ) && ! current_user_can( 'edit_posts' ) ) 
	wp_die( __( 'Cheatin&#8217; uh?') );
if ( ! user_can_access_admin_page() )
	wp_die(__( 'You do not have sufficient permissions to access this page.' ) );

require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
$plugin_data = get_plugin_data(realpath(dirname(__FILE__) ) . '/svp-silverlight.php' );

?>

<div class="wrap">
	<div id="icon-svp-silverlight" class="icon32"><br /></div>
	<h2><?php _e( 'About', 'svp-translate' ); ?></h2>
	<p><?php printf( __( "Le plugin SVP (Smooth Streaming Video Player) a été développé par l'agence <a href='%s'>Adenova</a>.", 'svp-translate' ), $plugin_data['AuthorURI'] ); ?></p>
	<p><a href="<?php print $plugin_data['AuthorURI']; ?>"><img src="<?php print plugins_url( get_plugin_dirname() . '/images/logo_adenova.jpg' ); ?>" alt="Adenova" title="Adenova" /></a></p>
	<p><?php _e( "Il permet la lecture de vidéos encodées en Smooth Streaming avec Silverlight sur un PC ou un Mac et avec le tag HTML 5 &lt;video&gt; sur iPhone et iPad.", 'svp-translate' ); ?></p>
	<p><?php _e( "La première version est sortie en juin 2010.", 'svp-translate' ); ?></p>
	<p><?php printf( __( "Vous pouvez consulter <a href='%s'>la page consacrée</a> à ce plugin sur le site d'Adenova.", 'svp-translate' ), $plugin_data['PluginURI'] ); ?></p>
	<p><?php _e( "Ce plugin a été réalisé en collaboration avec le <a href='http://www.microsoft.com/france/apropos/microsoft-technology-center/'>MTC Paris</a> (Microsoft Technology Center Paris) dans le cadre d'un projet d'implémentation d'un player Silverlight de vidéos lues en Smooth Streaming.", 'svp-translate' ); ?></p>
</div>