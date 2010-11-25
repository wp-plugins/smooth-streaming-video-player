<?php
require_once(dirname(__FILE__) . '/svp-post.php');
require_once(dirname(__FILE__) . '/svp-movie.php');

/**
 * Classe en charge de la gestion des vidéos.
 */

if (!class_exists("SVP_Movies"))
{
	class SVP_Movies
	{
		
		var $_data;
		
		const SVP_VIDEO_EXT_SILVERLIGHT = ".ism";
		const SVP_VIDEO_EXT_IPAD = ".ism";
		const SVP_VIDEO_EXT_IPHONE = ".m3u8";
		const SVP_VIDEO_EXT_THUMB = ".jpg";
		const SVP_VIDEO_SUFFIX_IPHONE = "m3u8-aapl";
		const SVP_VIDEOS_DIRECTORY_IPHONE = "TS";
		
		// Setters et getters
		function setData($value)
		{
			$this->_data = trim($value);
		}
		
		function getData()
		{
			return $this->_data;
		}
		
		// Récupère la liste des vidéos (.ism uniquement) placées sur le serveur
		function get_server_movies()
		{
			$settings = get_option("svp_settings");
			
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $settings["svp_movies_server_url"] . get_option("svp_movies_server_page") . "?token=" . get_option("svp_movies_server_token") . "&dir=" . $settings["svp_movies_dirname"]);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
			
			$error = null;
			$output = curl_exec($ch);
			if ($output == false)
				$error = __("An error occurred with the message: ", "svp-translate") . curl_error($ch);
			
			// Ferme la connexion cURL
			curl_close($ch);
			
			// Retourne le résultat
			if (!is_null($error))
				return array("error" => $error);
			
			$this->setData($output);
			
			if ($this->check_data() == false)
				return array("error" => __("Incorrect data source. Please check your URL and directory data source in the plugin settings.", "svp-translate"));
			
			return $output;
		}
		
		// Vérifie la source de données
		function check_data()
		{
			if (strstr($this->getData(), "<movies"))
				return true;
			return false;
		}
		
		// Vérifie si une entrée existe dans la table svpfiles pour l'article courant
		// Return bool
		function has_movie_file_entry($id)
		{
			if ($id == 0)
				return false;
			global $wpdb;
			$query = "SELECT filename FROM " . $wpdb->prefix . "svpfiles WHERE post_ID = %u";
			$movie = $wpdb->get_row($wpdb->prepare($query, (int)$id));
			if (!is_null($movie))
				return true;
			return false;
		}
		
		// Retourne le nom de la vidéo associée à l'article
		// Param $id int Identifiant de la vidéo
		// Param $output string Type de la sortie (iPhone, iPad ou Silverlight)
		// Return string
		function get_movie_file_entry($id, $output = SVP_USER_AGENT_OTHER)
		{
			if ($id == 0)
				return "";
			global $wpdb;
			$query = "SELECT filename FROM " . $wpdb->prefix . "svpfiles WHERE post_ID = %u";
			$movie = $wpdb->get_row($wpdb->prepare($query, (int)$id));
			if (!is_null($movie))
			{
				$pos = strrpos($movie->filename, ".");
				if ($pos !== false)
				{
					switch ($output)
					{
						case SVP_USER_AGENT_IPHONE:
						case SVP_USER_AGENT_IPAD: // iPhone, iPad: ajoute un suffixe, garde l'extension Silverlight, ajoute le manifest
							return substr($movie->filename, 0, $pos) . "-" . 
								self::SVP_VIDEO_SUFFIX_IPHONE . 
								self::SVP_VIDEO_EXT_SILVERLIGHT . 
								"/manifest(format=" . self::SVP_VIDEO_SUFFIX_IPHONE . ")";
							break;
						default:
							return substr($movie->filename, 0, $pos) . self::SVP_VIDEO_EXT_SILVERLIGHT;
							break;
					}
				}
			}
			return "";
		}
		
		// Indique si une vidéo est associé à au moins un article
		// Return bool
		function has_some_movies()
		{
			global $wpdb;
			$query = "SELECT COUNT(post_ID) size FROM " . $wpdb->prefix . "svpfiles";
			$movie = $wpdb->get_row($query);
			if ($movie->size > 0)
				return true;
			return false;
		}
		
		/**
		 * Retourne le nombre d'articles associés à une même vidéo.
		 * 
		 * @param $filename Nom du fichier ism correspondant à la vidéo
		 * @return int Nombre d'articles associés à la vidéo
		 */
		function get_num_posts_by_movie($filename)
		{
			global $wpdb;
			$query = "SELECT COUNT(filename) size FROM " . $wpdb->prefix . "svpfiles";
			$query .= " WHERE filename = %s";
			$posts = $wpdb->get_row($wpdb->prepare($query, (string)$filename));
			if ((int)$posts->size > 0)
				return (int)$posts->size;
			return 0;
		}
		
		// Retourne la vignette associée à une vidéo (cette fonction s'assure que la vignette existe réellement)
		// Param $id int Identifiant de la vidéo
		// Param $base_url string Chemin absolu vers les vignettes
		// Return string
		function get_movie_thumb($id, $base_url)
		{
			if ($id == 0 || empty($base_url))
				return "";
			global $wpdb;
			$query = "SELECT filename FROM " . $wpdb->prefix . "svpfiles WHERE post_ID = %u";
			$movie = $wpdb->get_row($wpdb->prepare($query, (int)$id));
			if (!is_null($movie))
			{
				$pos = strrpos($movie->filename, ".");
				if ($pos !== false)
					return $base_url . substr($movie->filename, 0, $pos) . SVP_Movies::SVP_VIDEO_EXT_THUMB;
			}
			return "";
		}
		
		// Retourne l'identifiant de l'article le plus récent ayant une vidéo associée
		// Return int
		function get_lastupdated_movie_post_id()
		{
			global $wpdb;
			$query = "SELECT ID FROM " . $wpdb->prefix . "posts p ";
			$query .= "INNER JOIN " . $wpdb->prefix . "svpfiles f ON p.ID = f.post_ID ";
			$query .= " ORDER BY post_date DESC LIMIT 1";
			$movie = $wpdb->get_row($wpdb->prepare($query));
			if (is_null($movie))
				return 0;
			return (int)$movie->ID;
		}
		
		/**
		 * Retourne la liste des vidéos.
		 *
		 * @return array Tableau d'objets
		 *
		 */
		function get_posts_with_movie()
		{
			global $wpdb;
			$sql = "SELECT p.ID, p.post_date, p.post_title, p.post_content, p.guid, u.display_name, f.filename ";
			$sql .= "FROM " . $wpdb->prefix . "posts p ";
			$sql .= "INNER JOIN  " . $wpdb->prefix . "svpfiles f ON p.ID = f.post_ID ";
			$sql .= "INNER JOIN  " . $wpdb->prefix . "users u ON p.post_author = u.ID ";
			$sql .= "WHERE p.post_type = %s AND p.post_status = %s ";
			$sql .= "ORDER BY p.post_date DESC";
			
			$options = get_option('svp_settings');
			if ($options['svp_items_count'] > 0)
				$sql .= " LIMIT " . $options['svp_items_count'];
			
			$result = $wpdb->get_results($wpdb->prepare($sql, "post", "publish"));
			
			$entries = array();
			foreach ($result as $row) {
				$entry = new SVP_Post();
				$entry->setId($row->ID);
				$entry->setTitle($row->post_title);
				$entry->setDate($row->post_date);
				$entry->setGuid($row->guid);
				$entry->setContent($row->post_content);
				//movie
				$movie = SVP_Movie::factory(SVP_Movie::TYPE_ADAPTIVE);
				$movie->setFilename($row->filename);
				
				$entry->setMovie($movie);
				$entries[] = $entry;
			}
			
			return $entries;
		}
		
		// Retourne les options locales d'une vidéo
		// Param $id integer identifiant de la vidéo
		// Return array
		function get_locale_options($id)
		{
			global $wpdb;
			$query = "SELECT options FROM " . $wpdb->prefix . "svpfiles WHERE post_ID = %u";
			$options = $wpdb->get_row($wpdb->prepare($query, (int)$id));
			if (!is_null($options->options))
				return unserialize($options->options);
			else
				return array();
		}
		
		// Met à jour les options locales d'une vidéo
		// Param $id integer identifiant de la vidéo
		// Param $values array options locales
		// Return bool
		function set_locale_options($id, $values = array())
		{
			global $wpdb;
			
			// Stocke les options locales
			if (count($values) > 0)
			{
				if ($wpdb->update(
					$wpdb->prefix . "svpfiles", 
					array("options" => (string)serialize($values)),
					array("post_ID" => (int)$id),
					array("%s"),
					array("%u")))
					return true;
				return false;
			}
			else // Supprime les options locales
			{
				if ($wpdb->query($wpdb->prepare("UPDATE " . $wpdb->prefix . "svpfiles SET options = NULL WHERE post_ID = %u", (int)$_POST["postid"])))
					return true;
				return false;
			}
			return true;
		}
		
	}
}