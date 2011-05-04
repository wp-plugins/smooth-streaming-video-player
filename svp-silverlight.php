<?php
/*
Plugin Name: Smooth Video Player
Plugin URI: http://www.adenova.fr/smooth-video-player-plugin-wordpress/
Description: A Smooth Streaming Video Player. With this plugin, you can add several sources of videos of different servers and associate them to your blog posts.
Version: 1.5.1
Author: Agence Adenova
Author URI: http://www.adenova.fr
*/

if ( ! class_exists( 'SVP_Smooth_Video_Player' ) )
{
	// Defines some contants
	if ( ! defined( 'SVP_DB_VERSION' ) )
		define( 'SVP_DB_VERSION', '1.1.0' );
	if ( ! defined( 'SVP_PLUGIN_VERSION' ) )
		define( 'SVP_PLUGIN_VERSION', '1.5.1' );
	if ( ! defined( 'SVP_USER_AGENT_IPHONE' ) )
		define( 'SVP_USER_AGENT_IPHONE', 'IPHONE' );
	if ( ! defined( 'SVP_USER_AGENT_IPAD' ) )
		define( 'SVP_USER_AGENT_IPAD', 'IPAD' );
	if ( ! defined( 'SVP_USER_AGENT_OTHER' ) )
		define( 'SVP_USER_AGENT_OTHER', 'OTHER' );
	if ( ! defined( 'SVP_VIDEO_EXT_SMOOTH' ) )
		define( 'SVP_VIDEO_EXT_SMOOTH', 'ism' );
	if ( ! defined( 'SVP_VIDEO_EXT_TS' ) )
		define( 'SVP_VIDEO_EXT_TS', 'm3u8' );
	if ( ! defined( 'SVP_VIDEO_SUFFIX_TS' ) )
		define( 'SVP_VIDEO_SUFFIX_TS', 'm3u8-aapl' );
	if ( ! defined( 'SVP_VIDEO_SUFFIX_THUMB' ) )
		define( 'SVP_VIDEO_SUFFIX_THUMB', 'Thumb' );
	if ( ! defined( 'SVP_VIDEO_EXT_THUMB' ) )
		define( 'SVP_VIDEO_EXT_THUMB', 'jpg' );
	
	/**
		* Ajout d'une fonction en charge de retourner le nom du répertoire courant
		* du plugin.
		*/
	if ( ! function_exists( 'get_plugin_dirname' ) )
	{
		function get_plugin_dirname()
		{
			return str_replace( '/' . basename( __FILE__ ), '', plugin_basename( __FILE__ ) );
		}
	}
	
	// Add some includes
	require_once( 'includes/class-utils.php' );
	
	class SVP_Smooth_Video_Player
	{
		// Propriétés
		var $_admin_pages = array( 'toplevel_page_svp-settings', 'svp_page_svp-about', 'svp_page_svp-source', 'admin_page_svp-delete-source', 'admin_page_svp-scan-source' );
		var $_post_pages = array( 'post.php', 'post-new.php' );
		var $_authorized_params_keys = array( 'width', 'height' );
		var $_authorized_params_values = array();
		var $_params_map = array(
			'width' => 'svp_player_width',
			'height' => 'svp_player_height'
			); // Tableau de map des paramètres
		var $_unauthorized_params = array(
			'svp_player_width', 
			'svp_player_height' ); // Liste des paramètres à ne pas passer au player
		
		var $params = array(); // Paramètres du tag d'affichage de la vidéo
		var $check = false; // Indique si le tag d'affichage de la vidéo est valide
		var $browser = array( 'name' => null, 'version' => null ); // Informations sur le navigateur
		
		// Constructeur
		function SVP_Smooth_Video_Player()
		{
			$this->__construct();
		}
		
		function __construct()
		{
			register_activation_hook( __FILE__, array( & $this, 'install' ) );
			register_deactivation_hook( __FILE__, array( & $this, 'uninstall' ) );
			
			// Traduction
			load_plugin_textdomain( 'svp-translate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			
			// Appelle les hooks d'actions
			$this->add_actions();
		}
		
		// Getters et Setters
		function set_param( $key, $value )
		{
			$this->params[$key] = $value;
		}
		
		function get_param( $key )
		{
			if ( ! array_key_exists( $key, $this->params ) )
				return false;
			return $this->params[$key];
		}
		
		function set_params( $values )
		{
			$this->params = (array) $values;
		}
		
		function get_params()
		{
			return (array) $this->params;
		}
		
		function get_param_map( $key )
		{
			if ( ! array_key_exists( $key, $this->_params_map ) )
				return $key;
			return $this->_params_map[$key];
		}
		
		function get_mapped_params()
		{
			if ( count( $this->get_params() ) > 0 )
			{
				$params = array();
				foreach ($this->get_params() as $key => $value)
					$params[$this->get_param_map( $key )] = $value;
				return $params;
			}
			return false;
		}
		
		// Installation du plugin
		function install()
		{
			// Ajoute les tables
			$this->add_tables();
			
			// Ajoute ou met à jour les options de configurations du plugin
			$this->add_options();
			
			// Ajoute les privilèges pour l'administrateur
			$this->add_roles();			
		}
		
		// Désinstallation du plugin
		function uninstall()
		{
			// Supprime les options de configuration du plugin
			$this->delete_options();
			
			// Supprime les tables du plugin
			$this->delete_tables();
		}
		
		// Ajout des tables à l'installation
		function add_tables()
		{
			global $wpdb;
			
			// Inclut les fichiers nécessaires à la création d'une table
			if ( @is_file( ABSPATH . '/wp-admin/includes/upgrade.php' ) )
				require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
			elseif ( @is_file( ABSPATH . '/wp-admin/upgrade-functions.php' ) )
				require_once( ABSPATH . '/wp-admin/upgrade-functions.php' );
			else
				wp_die( __( 'We have problem finding your &laquo;&nbsp;/wp-admin/upgrade.php&nbsp;&raquo;', 'svp-translate' ) );
			
			$charset_collate = '';
			if ( $wpdb->supports_collation() )
			{
				if ( ! empty( $wpdb->charset ) )
					$charset_collate = 'DEFAULT CHARACTER SET ' . $wpdb->charset;
				if ( ! empty( $wpdb->collate ) )
					$charset_collate .= ' COLLATE ' . $wpdb->collate;
			}
			
			// Ajoute la table contenant la liste des vidéos à associer aux articles
			$post_videos = $wpdb->prefix . 'svp_post_videos';
			$sql = 'CREATE TABLE ' . $post_videos . ' (
					post_ID INT NOT NULL,
					video_ID INT NOT NULL, 
					PRIMARY KEY ( post_ID ), 
					INDEX ( video_ID )
					)' . $charset_collate . ';';
			dbDelta( $sql );
			
			// Ajoute la table contenant la liste des vidéos par source
			$source_videos = $wpdb->prefix . 'svp_source_videos';
			$sql = 'CREATE TABLE ' . $source_videos . ' (
					ID INT NOT NULL AUTO_INCREMENT,
					source_ID INT NOT NULL,
					filename VARCHAR( 255 ) NOT NULL,
					type VARCHAR( 32 ) NOT NULL,
					PRIMARY KEY ( ID ), 
					INDEX ( source_ID ), 
					INDEX ( filename )
					)' . $charset_collate . ';';
			dbDelta( $sql );
			
			// Ajoute la table contenant la liste des sources ajoutées
			$sources = $wpdb->prefix . 'svp_sources';
			$sql = 'CREATE TABLE ' . $sources . ' (
					ID INT NOT NULL AUTO_INCREMENT,
					name VARCHAR( 64 ) DEFAULT NULL,
					source_type_code VARCHAR( 32 ) NOT NULL, 
					is_configured TINYINT( 4 ) NOT NULL DEFAULT 0,
					is_scanned TINYINT( 4 ) NOT NULL DEFAULT 0,
					options TEXT DEFAULT NULL,
					PRIMARY KEY ( ID ), 
					INDEX ( source_type_code )
					)' . $charset_collate . ';';
			dbDelta( $sql );
			
			// Ajoute la table contenant la liste des sources possibles
			$types = $wpdb->prefix . 'svp_source_types';
			$sql = 'CREATE TABLE ' . $types . ' (
					code VARCHAR( 32 ) NOT NULL,
					label VARCHAR( 64 ) NOT NULL,
					PRIMARY KEY ( code )
					)' . $charset_collate . ';';
			dbDelta( $sql );
			
			// Ajoute les données dans la table des sources possibles
			$wpdb->insert( $types, array( 'code' =>  'IIS-SMOOTH', 'label' => 'IIS Smooth Streaming' ) );
			$wpdb->insert( $types, array( 'code' =>  'IIS-LIVE', 'label' => 'IIS Live' ) );
			$wpdb->insert( $types, array( 'code' =>  'AZURE-SMOOTH', 'label' => 'Windows Azure&trade; Smooth Streaming' ) );
			$wpdb->insert( $types, array( 'code' =>  'PROGRESSIVE', 'label' => 'Progressive download' ) );
			$wpdb->insert( $types, array( 'code' =>  'AZURE-PROGRESSIVE', 'label' => 'Windows Azure&trade; progressive download' ) );
		}
		
		// Suppression des tables à la désinstallation
		function delete_tables()
		{
			global $wpdb;
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'svp_post_videos' );
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'svp_sources' );
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'svp_source_types' );
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'svp_source_videos' );
		}
		
		// Ajout des options de configuration
		function add_options()
		{
			update_option( 'svp_db_version', SVP_DB_VERSION, null, 'no' );
			update_option( 'svp_plugin_version', SVP_PLUGIN_VERSION, null, 'no' );
			update_option(
				'svp_settings', 
				array(
						'svp_player_width' => 400, 
						'svp_player_height' => 300,
						'svp_items_count' => 20),
					null, 'no' );
		}
		
		// Supprime les options de configuration
		function delete_options()
		{
			delete_option( 'svp_settings' );
		}
		
		// Ajoute les privilèges pour l'adminstrateur
		function add_roles()
		{
			$role = get_role( 'administrator' );
			$role->add_cap( 'svp_add_source' );
			$role->add_cap( 'svp_edit_source' );
			$role->add_cap( 'svp_delete_source' );
			$role->add_cap( 'svp_list_source' );
			$role->add_cap( 'svp_update_settings' );
		}
		
		// Supprime les privilèges pour l'administrateur
		function delete_roles()
		{
			$role = get_role( 'administrator' );
			$role->remove_cap( 'svp_add_source' );
			$role->remove_cap( 'svp_edit_source' );
			$role->remove_cap( 'svp_delete_source' );
			$role->remove_cap( 'svp_list_source' );
			$role->remove_cap( 'svp_update_settings' );
		}
		
		// Ajoute les hooks d'actions
		function add_actions()
		{
			add_action( 'init', array( & $this, 'initialize' ) );
			add_action( 'admin_init', array( & $this, 'admin_init' ) );
			add_action( 'admin_menu', array( & $this, 'add_admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( & $this, 'load_admin_scripts' ) );
			add_action( 'admin_print_styles', array( & $this, 'load_admin_styles' ) );
			add_action( 'edit_form_advanced', array( & $this, 'add_video_metabox' ) );
			add_action( 'save_post', array( & $this, 'add_video_to_post' ) );
		}
		
		/**
		 * Initializes the plugin.
		 * 
		 * @since 1.0.0
		 * @return void
		 */
		function initialize()
		{
			if ( ! defined( 'SVP_USER_AGENT' ) )
			{
				if ( stristr( $_SERVER['HTTP_USER_AGENT'], 'iphone' ) !== false)
					define( 'SVP_USER_AGENT', SVP_USER_AGENT_IPHONE);
				elseif ( stristr( $_SERVER['HTTP_USER_AGENT'], 'ipad' ) !== false)
					define( 'SVP_USER_AGENT', SVP_USER_AGENT_IPAD);
				else
					define( 'SVP_USER_AGENT', SVP_USER_AGENT_OTHER);
			}
			
			// Calls the hook of feeds
			$this->add_feeds();
		}
		
		/**
		 * Register a CSS stylesheet when admin is initializing
		 *
		 * @since 1.5.0
		 * @return void
		 */
		function admin_init()
		{
			wp_register_style( 
				'svp-admin',
				plugins_url( get_plugin_dirname() . '/styles/svp-admin.css' ), 
				array(), 
				false, 
				'screen' );
			wp_register_script(
				'silverlight',
				plugins_url( get_plugin_dirname() . '/scripts/silverlight.js' ),
				array(), 
				'4.0.50401.0' );
		}
		
		/**
		 * Adds the call to Media RSS feed.
		 * 
		 * @since 1.2.0
		 * @return void
		 */
		function add_feeds()
		{
			add_feed( 'svp-podcast', array( & $this, 'podcast' ) );
		}
		
		/**
		 * Indicates whether the player should be displayed for the current post.
		 * 
		 * @param int $id Post ID
		 * @return bool
		 */
		function show_player_check( $id = null )
		{
			if ( empty( $id ) )
			{
				wp_die( __( 'Post ID is undefined.', 'svp-translate' ) );
				exit();
			}
			require_once( 'includes/class-post.php' );
			$svp_post = new SVP_Post();
			$svp_post->read( $id );
			if ( $svp_post->has_video_entry( $id ) == false )
				return false;
			$video_url = $svp_post->get_video_url();
			if ( $svp_post->has_video_entry( $id ) && is_single() && ! empty( $video_url ) )
				return true;
			return false;
		}
		
		// Ajoute un menu au sein de l'administration
		function add_admin_menu()
		{
			if ( function_exists( 'add_menu_page' ) )
				add_menu_page( 
					__( 'SVP', 'svp-translate' ), 
					__( 'SVP', 'svp-translate' ), 
					'svp_update_settings', 
					'svp-settings', 
					'', 
					plugins_url(get_plugin_dirname() . '/images/svp-settings.png' ) );
			if ( function_exists( 'add_submenu_page' ) )
			{
				add_submenu_page(
					'svp-settings', 
					__( 'SVP Settings', 'svp-translate' ), 
					__( 'Settings', 'svp-translate' ), 
					'svp_update_settings', 
					'svp-settings', 
					array( & $this, 'show_menu' ) );
				add_submenu_page(
					'svp-settings', 
					__( 'SVP Source', 'svp-translate' ), 
					__( 'Add source', 'svp-translate' ), 
					'svp_add_source', 
					'svp-source', 
					array( & $this, 'show_menu' ) );
				add_submenu_page(
					'svp-settings', 
					__( 'SVP About', 'svp-translate' ), 
					__( 'About', 'svp-translate' ), 
					'edit_posts', 
					'svp-about', 
					array( & $this, 'show_menu' ) );
				add_submenu_page(
					'admin', 
					__( 'SVP Source Deletion', 'svp-translate' ), 
					null, 
					'svp_delete_source', 
					'svp-delete-source', 
					array( & $this, 'show_menu' ) );
				add_submenu_page(
					'admin', 
					__( 'SVP Source Scanner', 'svp-translate' ), 
					null, 
					'svp_edit_source', 
					'svp-scan-source', 
					array( & $this, 'show_menu' ) );
			}
		}
		
		// Affiche le menu et ses sous-menus
		function show_menu()
		{
			switch ( $_GET['page'] )
			{
				case 'svp-settings':
					include_once( dirname( __FILE__ ) . '/svp-settings.php' );
					break;
				case 'svp-about':
					include_once( dirname( __FILE__ ) . '/svp-about.php' );
					break;
				case 'svp-source':
					include_once( dirname( __FILE__ ) . '/svp-source.php' );
					break;
				case 'svp-delete-source':
					include_once( dirname( __FILE__ ) . '/svp-delete-source.php' );
					break;
				case 'svp-scan-source':
					include_once( dirname( __FILE__ ) . '/svp-scan-source.php' );
					break;
				default:
					include_once( dirname( __FILE__ ) . '/svp-about.php' );
					break;
			}
		}
		
		/**
		 * Adds CSS styles for the plugin administration space.
		 * 
		 * @since 1.0.0
		 * @return void
		 */
		function load_admin_styles()
		{
			wp_enqueue_style( 'svp-admin' );
		}
		
		/**
		 * Adds JS for the plugin administration space.
		 * 
		 * @since 1.5.0
		 * @return void
		 */
		function load_admin_scripts( $page )
		{
			if ( in_array( $page, $this->_post_pages) || stristr( $page, 'settings' ) !== false )
			{
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'silverlight' );
				wp_enqueue_script( 
					'svp-lightbox-me',
					plugins_url( get_plugin_dirname() . '/scripts/jquery.lightbox_me.js' ),
					array( 'jquery' ), 
					'2.2' );
				wp_enqueue_script( 
					'svp-metabox',
					plugins_url( get_plugin_dirname() . '/scripts/svp-metabox.js' ),
					array( 'jquery', 'svp-lightbox-me', 'silverlight' ), 
					'1.0' );
			}
		}
		
		/**
		 * Retourne le code source HTML du paramètre InitParams du player Silverlight.
		 *
		 * @param int $id Identifiant de l'article
		 * @return string Code source des paramètres
		 */
		function get_silverlight_params( $id )
		{
			// Add some includes
			require_once( 'includes/class-post.php' );
			
			$svp_post = new SVP_Post();
			$svp_post->read( $id );
			
			// Adds video URL
			$params = 'MediaUrl=' . $svp_post->get_video_url();
			
			// Adds thumbnail URL
			$thumbnail = $svp_post->get_thumbnail_url();
			if ( ! empty( $thumbnail ) )
				$params .= ',ThumbnailUrl=' . $thumbnail;
			
			$delivery_method = 'ProgressiveDownload';
			if ( $svp_post->get_video()->get_type() == SVP_VIDEO_TYPE_ADAPTIVE || $svp_post->get_video()->get_type() == SVP_VIDEO_TYPE_LIVE )
				$delivery_method = 'AdaptiveStreaming';
			$params .= ',DeliveryMethod=' . $delivery_method;
			return $params;
		}
		
		// Ajoute la zone d'ajout d'une vidéo dans le formulaire d'édition d'un article
		function add_video_metabox()
		{
			add_meta_box( 
				'smoothvideoplayerdiv', 
				__( 'Smooth Streaming Video Player &#8212; Choose your video', 'svp-translate' ), 
				array( & $this, 'show_metabox' ), 
				'post', 
				'advanced', 
				'high' );
		}
		
		// Retourne le contenu du SVP meta box
		function show_metabox()
		{
			require_once( 'svp-metabox.php' );
		}
		
		/**
		 * Adds a video to a post when a post is saved.
		 *
		 * @since 1.5.0
		 * @param int $id Post ID
		 * @return void
		 */
		function add_video_to_post( $post_ID )
		{
			if ( isset( $_POST ) && wp_is_post_revision( $post_ID ) == false )
			{
				if ( array_key_exists( 'svp_video', $_POST ) )
				{
					require_once( 'includes/class-post.php' );
					$data = array( 'post_ID' => (int) $post_ID, 'video_ID' => (int) $_POST['svp_video'] );
					$svp_post = new SVP_Post();
					$svp_post->attach_video( $data );
				}
			}
		}
		
		/**
		 * Calls the RSS generation file.
		 *
		 * @since 1.2.0
		 * @return void
		 */
		function podcast() { 
			load_template( ABSPATH . PLUGINDIR . '/' . get_plugin_dirname() . '/svp-mrss.php' );
		}
	}
}

// Initialise le plugin
if (class_exists( 'SVP_Smooth_Video_Player' ) )
{
	if ( ! isset( $svp_plugin ) )
		$svp_plugin = new SVP_Smooth_Video_Player();
}

/**
 * Implementation of a Template Tag returning the HTML source code for a video display.
 * 
 * @since 1.0.0
 * @package SVP_Smooth_Video_Player
 * @param $width string Override the width of the player (use these type of values: 100px, 100%)
 * @param $height string Override the height of the player (use these type of values: 100px, 100%)
 * @param $comments bool Indicates whether to display the comment tags in the HTML source
 * @param $container bool Indicates whether to wrap the player of a container in the source HTML
 * @param $before string Customized HTML to add before the player source code
 * @param $after string Customized HTML to add after the player source code
 * @return string Generated HTML source code
 */
if ( ! function_exists( 'the_smooth_video_player' ) && $svp_plugin instanceof SVP_Smooth_Video_Player )
{
	function the_smooth_video_player( $width = '', $height = '', $comments = true, $container = true, $before = '', $after = '' )
	{
		global $svp_plugin;
		
		$show = false; // Indique si un player est affiché
		
		$html = '';
		
		if ( $comments )
			$html .= '<!-- Smooth Video Player Template Tag Start -->' . "\n";
		
		if ( ! empty( $before ) )
			$html .= $before . "\n";
		
		// Add some includes
		require_once( 'includes/class-videos.php' );
		require_once( 'includes/class-post.php' );
		$svp_videos = new SVP_Videos();
		$svp_post = new SVP_Post();
		
		// Récupère l'identifiant courant
		$id = get_the_ID();
		
		if ( is_null( $id ) ) // Retourne un code source vide si aucun identfiant n'a été trouvé
			return '';
		
		// Récupère les données de l'article courant
		$svp_post->read( $id );
		
		// Récupère les options par défaut
		$options = get_option( 'svp_settings' );
		
		// Récupère les informations de la vidéo
		if ( $svp_plugin->show_player_check( $id ) )
		{
			// Début du conteneur
			if ( $container )
				$html .= '<div class="svp-player-container" id="svp-player-container-post-' . $id . '">';
			
			// Prépare les dimensions du player
			( ! empty( $width ) ) ? $player_width = $width : $player_width = $options['svp_player_width'];
			( ! empty( $height ) ) ? $player_height = $height : $player_height = $options['svp_player_height'];
			
			// Construit le code HTML
			$svp_utils = new SVP_Utils();
			$user_agent = $svp_utils->get_user_agent();
			switch ( $user_agent )
			{
				case SVP_USER_AGENT_IPAD: // iPad
				case SVP_USER_AGENT_IPHONE: // iPhone
					$html .= '<video src="' . $svp_post->get_video_url() . '" controls poster="' . $svp_post->get_thumbnail_url() . '" width="' . $player_width . '" height="' . $player_height . '">';
					$html .= '</video>';
					break;
				default: // Silverlight
					$html .= '<div id="silverlightControlHost">';
					$html .= '<object data="data:application/x-silverlight-2," type="application/x-silverlight-2" width="' . $player_width . '" height="' . $player_height . '">';
					$html .= '<param name="source" value="' . plugins_url( '/player/Player.xap', __FILE__ ) . '"/>';
					$html .= '<param name="onError" value="onSilverlightError" />';
					$html .= '<param name="background" value="transparent" />';
					$html .= '<param name="windowless" value="true" />';
					$html .= '<param name="minRuntimeVersion" value="4.0.50401.0" />';
					$html .= '<param name="autoUpgrade" value="true" />';
					$html .= '<param name="InitParams" value="' . $svp_plugin->get_silverlight_params( $id ) . '" />';
					$html .= '<a href="http://go.microsoft.com/fwlink/?LinkID=149156&v=4.0.50401.0" style="text-decoration:none">';
					$html .= '<img src="http://go.microsoft.com/fwlink/?LinkId=161376" alt="Get Microsoft Silverlight" style="border-style:none"/>';
					$html .= '</a>';
					$html .= '</object>';
					$html .= '<iframe id="_sl_historyFrame" style="visibility: hidden; height: 0px; width: 0px; border: 0px"></iframe>';
					$html .= '</div>';
					break;
			}
			
			// Fin du conteneur
			if ( $container )
				$html .= '</div>';
			
			$show = true;
		}
		
		// Affiche le code HTML
		$html .= "\n";
		
		if ( ! empty( $after ) )
			$html .= $after . "\n";
		
		if ( $comments )
			$html .= '<!-- Smooth Video Player Template Tag End -->' . "\n";
		
		print $html;
		
		// Excécute le hook d'action seulement si un player doit être affiché
		if ( $show == true )
			do_action( 'show_smooth_video_player', $id );
	}
}