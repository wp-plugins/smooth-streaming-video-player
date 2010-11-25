<?php

/*
Plugin Name: SVP
Plugin URI: http://www.adenova.fr/svp-silverlight-plugin-wordpress/
Description: A Smooth Streaming Video Player.
Version: 1.4.0
Author: Agence Adenova
Author URI: http://www.adenova.fr
*/

if (!class_exists("SVP_Silverlight"))
{
	// Définit quelques constantes
	if (!defined("SVP_DB_VERSION"))
		define("SVP_DB_VERSION", "1.0.0");
	if (!defined("SVP_PLUGIN_VERSION"))
		define("SVP_PLUGIN_VERSION", "1.4.0");
	if (!defined("SVP_USER_AGENT_IPHONE"))
		define("SVP_USER_AGENT_IPHONE", "IPHONE");
	if (!defined("SVP_USER_AGENT_IPAD"))
		define("SVP_USER_AGENT_IPAD", "IPAD");
	if (!defined("SVP_USER_AGENT_OTHER"))
		define("SVP_USER_AGENT_OTHER", "OTHER");
	
	include_once("includes/svp-utils.php");
	
	class SVP_Silverlight
	{
		// Propriétés
		var $_admin_pages = array(
			"svp-silverlight/svp-settings.php",
			"svp-silverlight/svp-about.php");
		var $_post_pages = array("post.php", "post-new.php");
		var $_authorized_params_keys = array("width", "height");
		var $_authorized_params_values = array();
		var $_params_map = array(
			"width" => "svp_player_width",
			"height" => "svp_player_height"
			); // Tableau de map des paramètres
		var $_unauthorized_params = array(
			"svp_player_width", 
			"svp_player_height"); // Liste des paramètres à ne pas passer au player
		
		var $params = array(); // Paramètres du tag d'affichage de la vidéo
		var $check = false; // Indique si le tag d'affichage de la vidéo est valide
		var $browser = array("name" => null, "version" => null); // Informations sur le navigateur
		
		// Constructeur
		function SVP_Silverlight()
		{
			register_activation_hook(__FILE__, array(&$this, "install"));
			register_deactivation_hook(__FILE__, array(&$this, "uninstall"));
			
			// Traduction
			load_plugin_textdomain("svp-translate", false, dirname(plugin_basename(__FILE__)) . "/languages");
			
			// Appelle les hooks d'actions
			$this->add_actions();
		
		}
		
		// Retourne le chemin absolu vers le plugin
		function getAbsolutePathPlugin()
		{
			return realpath(dirname(__FILE__));
		}
		
		// Getters et Setters
		function setParam($key, $value)
		{
			$this->params[$key] = $value;
		}
		
		function getParam($key)
		{
			if (!array_key_exists($key, $this->params))
				return false;
			return $this->params[$key];
		}
		
		function setParams($values)
		{
			$this->params = (array)$values;
		}
		
		function getParams()
		{
			return (array)$this->params;
		}
		
		function getParamMap($key)
		{
			if (!array_key_exists($key, $this->_params_map))
				return $key;
			return $this->_params_map[$key];
		}
		
		function getMappedParams()
		{
			if (count($this->getParams()) > 0)
			{
				$params = array();
				foreach ($this->getParams() as $key => $value)
					$params[$this->getParamMap($key)] = $value;
				return $params;
			}
			return false;
		}
		
		function setCheck($value)
		{
			$this->check = (bool)$value;
		}
		
		function getCheck()
		{
			return (bool)$this->check;
		}
		
		function setBrowser($value)
		{
			$this->browser = (array)$value;
		}
		
		function getBrowser()
		{
			return (array)$this->browser;
		}
		
		// Installation du plugin
		function install()
		{
			// Ajoute les tables
			$this->add_tables();
			
			// Ajoute ou met à jour les options de configurations du plugin
			$this->add_options();
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
			if (@is_file(ABSPATH . '/wp-admin/includes/upgrade.php'))
				include_once(ABSPATH . '/wp-admin/includes/upgrade.php');
			elseif (@is_file(ABSPATH . '/wp-admin/upgrade-functions.php'))
				include_once(ABSPATH . '/wp-admin/upgrade-functions.php' );
			else
				wp_die(__("We have problem finding your &laquo;&nbsp;/wp-admin/upgrade.php&nbsp;&raquo;", "svp-translate"));
			
			$charset_collate = "";
			if ($wpdb->supports_collation())
			{
				if (!empty( $wpdb->charset))
					$charset_collate = "DEFAULT CHARACTER SET " . $wpdb->charset . ";";
				if (!empty($wpdb->collate))
					$charset_collate .= " COLLATE " . $wpdb->collate . ";";
			}
			
			// Ajoute la table contenant la liste des vidéos à associer aux articles
			$files = $wpdb->prefix . "svpfiles";
			$sql = "CREATE TABLE " . $files . " (
					post_ID INT NOT NULL,
					filename VARCHAR(128) NOT NULL,
					options VARCHAR(255) DEFAULT NULL,
					PRIMARY KEY (post_ID)
					)" . $charset_collate;
			dbDelta($sql);
		}
		
		// Suppression des tables à la désinstallation
		function delete_tables()
		{
			global $wpdb;
			$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "svpfiles");
			$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "svpstats");
		}
		
		// Ajout des options de configuration
		function add_options()
		{
			add_option("svp_db_version", SVP_DB_VERSION, null, "no");
			add_option("svp_plugin_version", SVP_PLUGIN_VERSION, null, "no");
			add_option("svp_movies_server_token", "34700ae2-7ff6-4fd6-ba79-713153a886c4", null, "no");
			add_option("svp_movies_server_page", "movies.aspx", null, "no");
			add_option(
				"svp_settings", 
				array(
						"svp_movies_server_url" => "http://www.mymovies.tv/", 
						"svp_movies_dirname" => "movies", 
						"svp_player_width" => 400, 
						"svp_player_height" => 300,
						"svp_items_count" => 20),
					null, "no");
		}
		
		// Supprime les options de configuration
		function delete_options()
		{
			delete_option("svp_db_version");
			delete_option("svp_plugin_version");
			delete_option("svp_movies_server_token");
			delete_option("svp_movies_server_page");
			delete_option("svp_settings");
		}
		
		// Ajoute les hooks d'actions
		function add_actions()
		{
			add_action("init", array(&$this, "initialize"));
			add_action("admin_menu", array(&$this, "add_admin_menu"));
			add_action("wp_ajax_get_movies", array(&$this, "get_movies"));
			add_action("wp_ajax_add_movie_to_post", array(&$this, "add_movie_to_post"));
			add_action("wp_ajax_save_movie_to_post", array(&$this, "save_movie_to_post"));
			add_action("wp_ajax_delete_post_movie", array(&$this, "delete_post_movie"));
			add_action("admin_enqueue_scripts", array(&$this, "load_admin_styles"));
			add_action("wp_head", array(&$this, "add_head_js"));
			add_action("edit_form_advanced", array(&$this, "add_movie_metabox"));
		}
		
		function add_feeds()
		{
			add_feed('svp-podcast', array($this, 'podcast'));
		}
		
		// Initialise le plugin
		function initialize()
		{
			if (!defined("SVP_USER_AGENT"))
			{
				if (stristr($_SERVER["HTTP_USER_AGENT"], "iphone") !== false)
 					define("SVP_USER_AGENT", SVP_USER_AGENT_IPHONE);
				elseif (stristr($_SERVER["HTTP_USER_AGENT"], "ipad") !== false)
					define("SVP_USER_AGENT", SVP_USER_AGENT_IPAD);
 				else
 					define("SVP_USER_AGENT", SVP_USER_AGENT_OTHER);
			}
			
			// Stocke la version de IE
			$browser = array();
			if (preg_match("/msie ([0-9\.])/i", $_SERVER["HTTP_USER_AGENT"], $matches) > 0)
			{
				$browser["name"] = "msie";
				$browser["version"] = $matches[1];
				$this->setBrowser($browser);
			}
			
			// Appelle les hooks de feeds
			$this->add_feeds();
		}
		
		// Indique si le player doit être affiché pour l'article courant
		// Sur la page d'accueil, le player doit être systématiquement affiché
		function show_player_check()
		{
			global $wp_query;
			include_once ("includes/svp-movies.php");
			$svp_movies = new SVP_Movies();
			if (($svp_movies->has_movie_file_entry($wp_query->get_queried_object_id())
				&& is_single()))
				return true;
			return false;
		}
		
		// Ajoute une menu au sein de l'administration
		function add_admin_menu()
		{
			if (function_exists("add_menu_page"))
				add_menu_page(__("SVP", "svp-translate"), __("SVP", "svp-translate"), "manage_options", "svp-silverlight/svp-settings.php", "", plugins_url("svp-silverlight/images/svp-settings.png"));
			if (function_exists("add_submenu_page"))
			{
				add_submenu_page("svp-silverlight/svp-settings.php", __("SVP Settings", "svp-translate"), __("Settings", "svp-translate"), "manage_options", "svp-silverlight/svp-settings.php");
				add_submenu_page("svp-silverlight/svp-settings.php", __("SVP About", "svp-translate"), __("About", "svp-translate"), "manage_options", "svp-silverlight/svp-about.php");
			}
		}
		
		// Ajoute les CSS pour l'administration du plugin
		function load_admin_styles($page)
		{
			// CSS pour les pages d'administration du plugin
			if (in_array($page, $this->_admin_pages)
				|| in_array($page, $this->_post_pages))
				wp_enqueue_style("svp-admin", plugins_url("svp-silverlight/styles/admin.css"), array(), false, "screen");
			
			// CSS et JS pour les pages d'ajout et d'édition d'un article
			if (in_array($page, $this->_post_pages))
			{
				$browser = $this->getBrowser();
				wp_enqueue_style("svp-post", plugins_url("svp-silverlight/styles/post.css"), array(), false, "screen");
				if ($browser["name"] == "msie" && $browser["version"] == 8)
				{
					wp_enqueue_style("svp-post-ie", plugins_url("svp-silverlight/styles/post-ie.css"), array(), false, "screen");
					global $wp_styles;
					$wp_styles->add_data("svp-post-ie", "conditional", "gt IE 7");
				}
				if ($browser["name"] == "msie" && $browser["version"] == 7)
				{
					wp_enqueue_style("svp-post-ie7", plugins_url("svp-silverlight/styles/post-ie7.css"), array(), false, "screen");
					global $wp_styles;
					$wp_styles->add_data("svp-post-ie7", "conditional", "lte IE 7");
				}
				wp_enqueue_script("jquery");
				wp_enqueue_script("svp-metabox-js", plugins_url("svp-silverlight/scripts/metabox.js"));
			}
			
			// Styles et JS spécifiques
			if (stristr($page, "settings") !== false)
			{
				wp_enqueue_script("jquery");
			}
		}
		
		// Ajoute le javascript dans le <head> des articles
		function add_head_js()
		{
			if ($this->show_player_check()
				&& $this->getUserAgent() == SVP_USER_AGENT_OTHER) // Uniquement pour Silverlight
			{
				?>
				<script type="text/javascript">
        function onSilverlightError(sender, args) {
            var appSource = "";
            if (sender != null && sender != 0) {
              appSource = sender.getHost().Source;
            }
            
            var errorType = args.ErrorType;
            var iErrorCode = args.ErrorCode;
 
            if (errorType == "ImageError" || errorType == "MediaError") {
              return;
            }
 
            var errMsg = "Unhandled Error in Silverlight Application " +  appSource + "\n" ;
 
            errMsg += "Code: "+ iErrorCode + "    \n";
            errMsg += "Category: " + errorType + "       \n";
            errMsg += "Message: " + args.ErrorMessage + "     \n";
 
            if (errorType == "ParserError") {
                errMsg += "File: " + args.xamlFile + "     \n";
                errMsg += "Line: " + args.lineNumber + "     \n";
                errMsg += "Position: " + args.charPosition + "     \n";
            }
            else if (errorType == "RuntimeError") {           
                if (args.lineNumber != 0) {
                    errMsg += "Line: " + args.lineNumber + "     \n";
                    errMsg += "Position: " +  args.charPosition + "     \n";
                }
                errMsg += "MethodName: " + args.methodName + "     \n";
            }
 
            throw new Error(errMsg);
        }
				</script>
				<?php
			}
		}
		
		// AJAX: retourne la liste <ul> des vidéos disponibles sur le serveur
		function get_movies()
		{
			// Vérifie l'autorisation d'accès (Ajax Nonce)
			check_ajax_referer("svp-get-movies");
			
			// Récupère la liste des vidéos déposées sur le serveur de vidéos
			include_once("includes/svp-movies.php");
			$svp_movies = new SVP_Movies();
			$data = $svp_movies->get_server_movies();
			
			// Vérifie si une erreur est retournée
			$error = false;
			if (is_array($data) && array_key_exists("error", $data))
				$error = true;
			
			// Vérifie le nombre de vidéos retournées
			// et récupère les données du XML
			if ($error == false)
			{
				$xml = new SimpleXMLElement($data);
				$result = $xml->xpath("/movies/@count");
				$count = (int)$result[0]["count"];
				$elements = $xml->xpath("//movie");
			}
			
			// Effectue la sortie
			header("Content-Type: text/plain");
			
			if ($error == true)
			{
				print '<li class="error"><span>' . $data["error"] . '</span></li>';
				die;
			}
			
			if ($count == 0)
			{
				print '<li class="error"><span>' . __("None movie found on the server", "svp-translate") . '</span></li>';
				die;
			}
			
			$output = "";
			foreach ($elements as $element)
			{	
				if ($svp_movies->get_movie_file_entry((int)$_POST["postid"], SVP_USER_AGENT_OTHER) == (string)$element)
					$output .= '<li class="selected"><span>' . (string)$element . '</span>';
				else
					$output .= '<li class="unselected"><span>' . (string)$element . '</span>';
				
				// Nombre d'articles attachés à la vidéo
				$posts = $svp_movies->get_num_posts_by_movie((string)$element);
				if ($posts > 0)
					$output .= ' <span class="attached">' . sprintf(__("(attached to %u post(s))", "svp-translate"), $posts) . '</span>';
				
				$output	.= '</li>';
			}
			print $output;
			die;
		}
		
		// AJAX: associe une vidéo à un article
		function add_movie_to_post()
		{
			// Vérifie l'autorisation d'accès (Ajax Nonce)
			check_ajax_referer("svp-add-movie-to-post");
			
			include_once ("includes/svp-movies.php");
			$svp_movies = new SVP_Movies();
			$has_movie = $svp_movies->has_movie_file_entry((int)$_POST["postid"]);
			
			global $wpdb;
			
			// Effectue la sortie
			header("Content-Type: text/plain");
			if ($_POST["postid"])
			{
				if ($has_movie == false) // Ajout
				{
					if ($wpdb->insert(
						$wpdb->prefix . "svpfiles", 
						array("post_ID" => (int)$_POST["postid"], "filename" => (string)$_POST["filename"]),
						array("%u", "%s")))
						print 1;
					else
						print 3; // Erreur lors de l'insertion en base de données
				}
				else // Mise à jour
				{
					if ($wpdb->update(
						$wpdb->prefix . "svpfiles", 
						array("filename" => (string)$_POST["filename"]),
						array("post_ID" => (int)$_POST["postid"]),
						array("%s"),
						array("%u")))
						print 1;
					else
						print 3; // Erreur lors de l'insertion en base de données
				}
			}
			else
				print 0; // Identifiant non trouvé
			die;
		}
		
		// AJAX: supprime une vidéo associée à un article
		function delete_post_movie()
		{
			// Vérifie l'autorisation d'accès (Ajax Nonce)
			check_ajax_referer("svp-delete-post-movie");
			
			global $wpdb;
			
			// Effectue la sortie
			header("Content-Type: text/plain");
			if ($_POST["postid"])
			{
				if ($wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "svpfiles WHERE post_ID = %u", (int)$_POST["postid"])))
					print 1;
				else
					print 3; // Erreur lors de la suppression
			}
			else
				print 0; // Identifiant non trouvé
			die;
		}
		
		// AJAX: met à jour les données vidéo d'un article
		function save_movie_to_post()
		{
			// Vérifie l'autorisation d'accès (Ajax Nonce)
			check_ajax_referer("svp-save-movie-to-post");
			
			include_once ("includes/svp-movies.php");
			$svp_movies = new SVP_Movies();
			
			// Effectue la sortie
			header("Content-Type: text/plain");
			if ($_POST["postid"])
			{
				// Met à jour les données de la vidéo associée
				$values = array(
					"svp_player_width" => $_POST["width"],
					"svp_player_height" => $_POST["height"]
					);
				
				// Ne stocke que les données différentes de la configuration globale
				$options = get_option("svp_settings");
				$locale = array();
				foreach ($values as $key => $value)
				{
					if ($values[$key] != $options[$key])
						$locale[$key] = $value;
				}
				
				if ($svp_movies->set_locale_options($_POST["postid"], $locale))
					print 1;
				else
					print 3; // Erreur lors de l'insertion en base de données
			}
			else
				print 0; // Identifiant non trouvé
			die;
		}
		
		// Retourne le User Agent courant
		function getUserAgent()
		{
			if (!defined("SVP_USER_AGENT"))
				define("SVP_USER_AGENT", SVP_USER_AGENT_OTHER);
			return SVP_USER_AGENT;
		}
		
		// Retourne l'URL complète d'accès aux vidéos
		function getURLMovies()
		{
			$utils = new SVP_Utils();
			return $utils->addEndUrlSlash($this->getOption("svp_movies_server_url") . $this->getOption("svp_movies_dirname"));
		}
		
		// Retourne la largeur du player
		function getWidth()
		{
			if (!$this->getParam($this->getParamMap("width")))
				return $this->getOption($this->getParamMap("width"));
			if (!$this->getParam($this->getParamMap("width")))
				return "100%";
			else
				return $this->getParam($this->getParamMap("width"));
		}
		
		// Retourne la hauteur du player
		function getHeight()
		{
			if (!$this->getParam($this->getParamMap("height")))
				return $this->getOption($this->getParamMap("height"));
			if (!$this->getParam($this->getParamMap("height")))
				return "100%";
			else
				return $this->getParam($this->getParamMap("height"));
		}
		
		// Récupère une option de configuration
		function getOption($key)
		{
			$options = get_option("svp_settings");
			if (array_key_exists($key, $options))
				return $options[$key];
			return false;
		}
		
		/**
-		 * Retourne le code source HTML des paramètres locaux du player Silverlight.
-		 *
-		 * @param array $values Liste des paramètres à passer en initParams au player Silverlight
-		 * @param int $id Identifiant de l'article
-		 * @return string Code source généré
-		 *
-		 */
		function getSilverlightParams($values = array(), $id)
		{
			include_once ("includes/svp-movies.php");
			$svp_movies = new SVP_Movies();
			
			// Paramètres obligatoires (nom du fichier de la vidéo et url absolue d'accès à la vidéo)
			$params .= "mediaurl=" . $this->getURLMovies() . $svp_movies->get_movie_file_entry($id, SVP_USER_AGENT_OTHER) . "/manifest";
			
			if (count($values) > 0 || $values = $this->getMappedParams())
			{
				if (!is_array($values))
					return "";
				foreach ($values as $key => $value)
				{
					if (!in_array($key, $this->_unauthorized_params))
						$params .= "," . $key . '=' . trim($value);
				}
			}
			return $params;
		}
		
		// Ajoute la zone d'ajout d'une vidéo dans le formulaire d'édition d'un article
		function add_movie_metabox()
		{
			add_meta_box("svpsmoothvideodiv", __("Smooth Streaming Movie", "svp-translate"), array(&$this, "show_movie_metabox"), "post", "advanced", "high");
		}
		
		// Retourne le contenu du SVP meta box
		function show_movie_metabox()
		{
			include_once("svp-movie-metabox.php");
		}
		
		function podcast() { 
			load_template(ABSPATH . PLUGINDIR . '/svp-silverlight/svp-mrss.php');
		}
	}
}

// Initialise le plugin
if (class_exists("SVP_Silverlight"))
{
	if (!isset($svp_plugin))
		$svp_plugin = new SVP_Silverlight();
}

/**
 * Implémentation d'un Template Tag retournant le code source HTML permettant l'affichage
 * d'une vidéo en smooth streaming Silverlight ou iPhone.
 * 
 * @param $width string Surcharge la largeur du player (Ex: 100px ou 100%)
 * @param $height string Surcharge la hauteur du player (Ex: 100px ou 100%)
 * @param $comments bool Indique s'il faut afficher les commentaires de tag dans le source HTML
 * @param $container bool Indique s'il faut envelopper le player d'un conteneur dans le source HTML
 * @param $before string Code source HTML à ajouter avant le player
 * @param $after string Code source HTML à ajouter après le player
 * @return string Code source HTML généré
 */

if (!function_exists("the_smooth_video_player") && $svp_plugin instanceof SVP_Silverlight)
{
	function the_smooth_video_player($width = "", $height = "", $comments = true, $container = true, $before = "", $after = "")
	{
		global $svp_plugin, $wpdb, $wp_query;
		
		$show = false; // Indique si un player est affiché
		
		$html = "";
		
		if ($comments)
			$html .= "<!-- Smooth Video Player Template Tag Start -->" . "\n";
		
		if (!empty($before))
			$html .= $before . "\n";
		
		include_once("includes/svp-movies.php");
		$svp_movies = new SVP_Movies();
		
		// Récupère l'identifiant courant ou celui du dernier article publié possédant une vidéo
		$id = $wp_query->get_queried_object_id();
		if ($id == 0)
			$id = $svp_movies->get_lastupdated_movie_post_id();
		
		if (is_null($id)) // Retourne un code source vide si aucun identfiant n'a été trouvé
			return "";
		
		// Récupère les options par défaut
		$options = get_option("svp_settings");
		
		// Récupère les informations de la vidéo
		if ($svp_movies->has_movie_file_entry($id))
		{
			// Début du conteneur
			if ($container)
				$html .= '<div class="svp-player-container" id="svp-player-container-post-' . $id . '">';
			
			// Fusionne les options de configuration locale et globale
			$options = array_merge($options, $svp_movies->get_locale_options($id));
			
			// Prépare les dimensions du player
			(!empty($width)) ? $player_width = $width : $player_width = $options["svp_player_width"];
			(!empty($height)) ? $player_height = $height : $player_height = $options["svp_player_height"];
			
			// Construit le code HTML
			$user_agent = $svp_plugin->getUserAgent();
			switch ($user_agent)
			{
				case SVP_USER_AGENT_IPAD: // iPad
				case SVP_USER_AGENT_IPHONE: // iPhone
					$html .= '<video src="' . $svp_plugin->getURLMovies() .
						SVP_Movies::SVP_VIDEOS_DIRECTORY_IPHONE . "/";
					if ($user_agent == SVP_USER_AGENT_IPAD)
						$html .= $svp_movies->get_movie_file_entry($id, SVP_USER_AGENT_IPAD);
					if ($user_agent == SVP_USER_AGENT_IPHONE)
						$html .= $svp_movies->get_movie_file_entry($id, SVP_USER_AGENT_IPHONE);
					$html .= '" controls';
					// Ajoute la vignette
					$thumb = $svp_movies->get_movie_thumb($id, $svp_plugin->getURLMovies());
					if (!empty($thumb))
						$html .= ' poster="' . $thumb . '"';
					$html .= ' width="' . $player_width . '" height="' . $player_height . '"></video>';
					break;
				default: // Silverlight
					$html .= '<div id="silverlightControlHost">';
					$html .= '<object data="data:application/x-silverlight-2," type="application/x-silverlight-2" width="' . $player_width . '" height="' . $player_height . '">';
					$html .= '<param name="source" value="' . plugins_url('/player/Player.xap', __FILE__) . '"/>';
					$html .= '<param name="onError" value="onSilverlightError" />';
					$html .= '<param name="background" value="black" />';
					$html .= '<param name="minRuntimeVersion" value="4.0.50401.0" />';
					$html .= '<param name="autoUpgrade" value="true" />';
					if ($params = $svp_plugin->getSilverlightParams($svp_movies->get_locale_options($id), $id)) 
						$html .= '<param name="InitParams" value="' . $params . '" />';
					$html .= '<a href="http://go.microsoft.com/fwlink/?LinkID=149156&v=4.0.50401.0" style="text-decoration:none">';
 					$html .= '<img src="http://go.microsoft.com/fwlink/?LinkId=161376" alt="Get Microsoft Silverlight" style="border-style:none"/>';
					$html .= '</a>';
					$html .= '</object>';
					$html .= '<iframe id="_sl_historyFrame" style="visibility:hidden;height:0px;width:0px;border:0px"></iframe>';
					$html .= '</div>';
					break;
			}
			
			// Fin du conteneur
			if ($container)
				$html .= '</div>';
			
			$show = true;
		}
		
		// Affiche le code HTML
		
		$html .= "\n";
		
		if (!empty($after))
			$html .= $after . "\n";
		
		if ($comments)
			$html .= "<!-- Smooth Video Player Template Tag End -->" . "\n";
		
		print $html;
		
		// Excécute le hook d'action seulement si un player doit être affiché
		if ($show == true)
			do_action("show_smooth_video_player", $id);
	}
}
