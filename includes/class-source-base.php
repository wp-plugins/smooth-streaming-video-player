<?php
/**
 * Base source management class.
 * 
 * This class must be used by all concrete source management
 * classes.
 * 
 * @author Adenova <agence@adenova.fr>
 * @since 1.5.0
 */
if ( ! class_exists ( 'SVP_Source_Base' ) )
{
	// Add some includes
	require_once( 'class-utils.php' );
	require_once( 'class-videos.php' );
	
	class SVP_Source_Base
	{
		
		// Properties
		var $_ID;
		var $_name;
		var $_source_type_code;
		var $_is_configured;
		var $_is_scanned;
		var $_options;
		var $_posts; // List of posts linked to a video of the source
		var $_videos; // List of videos linked to the source
		var $_videos_type; // Type of videos on source managed by the source (ADAPTIVE or PROGRESSIVE)
		
		// Setters and getters
		function set_ID( $value )
		{
			$this->_ID = (int) $value;
			return $this;
		}
		
		function get_ID()
		{
			return (int) $this->_ID;
		}
		
		function set_name( $value )
		{
			$this->_name = (string) $value;
			return $this;
		}
		
		function get_name()
		{
			return (string) $this->_name;
		}
		
		function set_source_type_code( $value )
		{
			$this->_source_type_code = (string) $value;
			return $this;
		}
		
		function get_source_type_code()
		{
			return (string) $this->_source_type_code;
		}
		
		function set_is_configured( $value )
		{
			( (int) $value == 1 ) ? $this->_is_configured = true : $this->_is_configured = false;
			return $this;
		}
		
		function get_is_configured()
		{
			return (bool) $this->_is_configured;
		}
		
		function set_is_scanned( $value )
		{
			( (int) $value == 1 ) ? $this->_is_scanned = true : $this->_is_scanned = false;
			return $this;
		}
		
		function get_is_scanned()
		{
			return (bool) $this->_is_scanned;
		}
		
		function set_options( $value )
		{
			$this->_options = (array) unserialize( $value );
			return $this;
		}
		
		function get_options()
		{
			return (array) $this->_options;
		}
		
		function set_posts( $value )
		{
			$this->_posts = $value;
			return $this;
		}
		
		function get_posts()
		{
			return $this->_posts;
		}
		
		function set_videos( $value )
		{
			$this->_videos = $value;
			return $this;
		}
		
		function get_videos()
		{
			return $this->_videos;
		}
		
		function set_videos_type( $value )
		{
			$svp_video = new SVP_Video();
			if ( ! in_array( $value, $svp_video->types ) )
			{
				wp_die( __( 'Invalid video type.', 'svp-translate' ) );
				exit();
			}
			$this->_videos_type = $value;
		}
		
		function get_videos_type()
		{
			if ( empty( $this->_videos_type ) )
			{
				wp_die( __( 'Type of videos on source must be defined in constructor of herited classes.', 'svp-translate' ) );
				exit();
			}
			return $this->_videos_type;
		}
		
		/**
		 * Default configure method.
		 * To show the configuration form.
		 * 
		 * @since 1.5.0
		 * @param object Data to populate form
		 * @return string Configuration form HTML source code
		 */
		function configure()
		{
			return '<p>' . __( 'Please configure your method to return a correct configuration form.', 'svp-translate' ) . '</p>';
		}
		
		/**
		 * Default save method.
		 * To save (add or update) the source settings.
		 * 
		 * @since 1.5.0
		 * @param int Source ident
		 * @param array Source data (source name on 'name' key, type code on 'source_type_code' key, serialized settings on 'options' key)
		 * @return object Source settings
		 */
		function save( $id = null, $data = array() )
		{
			global $wpdb;
			if ( empty( $id ) ) // Insert
			{
				$wpdb->insert( 
					$wpdb->prefix . 'svp_sources', 
					$data, 
					array( '%s', '%s', '%d', '%d', '%s' ) );
				$id = $wpdb->insert_id;
			}
			else // Update
			{
				$wpdb->update( 
					$wpdb->prefix . 'svp_sources', 
					$data, 
					array( 'ID' => (int) $id ), 
					array( '%s', '%s', '%d', '%d', '%s' ),
					array( '%d' ) );
			}
			$this->read( (int) $id );
		}
		
		/**
		 * Default read method.
		 * To read the source settings.
		 * 
		 * @since 1.5.0
		 * @param int Source ident
		 * @return object Source settings
		 */
		function read( $id = null )
		{
			if ( empty( $id ) )
			{
				wp_die( __( 'Parameter ID is undefined.', 'svp-translate' ) );
				exit();
			}
			
			global $wpdb;
			
			// Add some includes
			require_once( 'class-video-base.php' );
			require_once( 'class-post.php' );
			
			// Récupère les données de la table des sources
			$sql = 'SELECT * FROM ' . $wpdb->prefix . 'svp_sources WHERE ID = %d';
			$source = $wpdb->get_row( $wpdb->prepare( $sql, (int) $id ) );
			
			// Récupère les données annexes de la source
			if ( ! empty( $source ) )
			{
				$this->set_ID( $source->ID );
				$this->set_name( $source->name );
				$this->set_source_type_code( $source->source_type_code );
				$this->set_is_configured( $source->is_configured );
				$this->set_is_scanned( $source->is_scanned );
				$this->set_options( $source->options );
				
				$sql = 'SELECT * FROM ' . $wpdb->prefix . 'svp_source_videos WHERE source_ID = %d';
				$rows = $wpdb->get_results( $wpdb->prepare( $sql, (int) $id ) );
				$videos = array();
				foreach ( $rows as $row )
				{
					$svp_video_base = new SVP_Video_Base();
					$svp_video_base->set_ID( $row->ID );
					$svp_video_base->set_source_ID( $row->source_ID );
					$svp_video_base->set_filename( $row->filename );
					$svp_video_base->set_type( $row->type );
					$videos[] = $svp_video_base;
				}
				$this->set_videos( $videos );
				
				$sql = 'SELECT pv.* FROM ' . $wpdb->prefix . 'svp_source_videos sv 
					RIGHT JOIN ' . $wpdb->prefix . 'svp_post_videos pv 
					ON ( sv.ID = pv.video_ID ) 
					RIGHT JOIN ' . $wpdb->prefix . 'posts p 
					ON ( p.ID = pv.post_ID ) 
					WHERE sv.source_ID = %d';
				$rows = $wpdb->get_results( $wpdb->prepare( $sql, (int) $id ) );
				$posts = array();
				foreach ( $rows as $row )
				{
					$svp_post = new SVP_Post();
					$svp_post->set_ID( $row->ID );
					$svp_post->set_date( $row->post_date );
					$svp_post->set_content( $row->post_content );
					$svp_post->set_title( $row->post_title );
					$svp_post->set_guid( $row->guid );
					$posts[] = $svp_post;
				}
			}
		}
		
		/**
		 * Default delete method.
		 * Delete all datas source : videos attached to source, videos attached to posts and inside data source.
		 * 
		 * @since 1.5.0
		 * @param mixed $id Source ident
		 * @return bool
		 */
		function delete( $id = null )
		{
			if ( empty( $id ) )
			{
				wp_die( __( 'Parameter ID is undefined.', 'svp-translate' ) );
				exit();
			}
			
			global $wpdb;
			
			// Delete videos attached to posts
			$sql = 'SELECT ID FROM ' . $wpdb->prefix . 'svp_source_videos WHERE source_ID = %d';
			$videos = $wpdb->get_results( $wpdb->prepare( $sql, (int) $id ) );
			foreach ( $videos as $video )
			{
				$sql = 'DELETE FROM ' . $wpdb->prefix . 'svp_post_videos WHERE video_ID = %d';
				$result = $wpdb->query( $wpdb->prepare( $sql, (int) $video->ID ) );
				if ( $result === false )
					return false;
			}
			
			// Delete videos attached to source
			$sql = 'DELETE FROM ' . $wpdb->prefix . 'svp_source_videos WHERE source_ID = %d';
			$result = $wpdb->query( $wpdb->prepare( $sql, (int) $id ) );
			if ( $result === false )
				return false;
			
			// Delete data from main sources table
			$sql = 'DELETE FROM ' . $wpdb->prefix . 'svp_sources WHERE ID = %d';
			$result = $wpdb->query( $wpdb->prepare( $sql, (int) $id ) );
			if ( $result === false )
				return false;
				
			return true;
		}
		
		/**
		 * Default scan method.
		 * To list the videos files from the source.
		 *
		 * @since 1.5.0
		 * @param int Source ident
		 * @return array Source videos list
		 */
		function scan( $id = null )
		{
			return array();
		}
		
		/**
		 * Default synchro method.
		 * Adds and removes videos associated with the source.
		 *
		 * @since 1.5.0
		 * @param int Source ident
		 * @param array Filenames of videos to synchronize
		 * @return array Key 'added' contains an array of added videos and key 'deleted' contains an array of deleted videos
		 */
		function synchro( $id = null, $filenames = array() )
		{
			if ( empty( $id ) )
			{
				wp_die( __( 'Parameter ID is undefined.', 'svp-translate' ) );
				exit();
			}
			if ( ! is_array( $filenames ) )
			{
				wp_die( __( 'Filenames parameter is not an array.', 'svp-translate' ) );
				exit();
			}
			global $wpdb;
			
			// Include SVP_Video_Base class
			require_once( 'class-video-base.php' );
			
			// Create SVP_Video_Base instance
			$svp_video_base = new SVP_Video_Base();
			
			// Initialize added, deleted and unchanged array of videos
			$added = array();
			$deleted = array();
			$unchanged = array();
			
			// Retrieves actually associated videos to source
			$sql = 'SELECT ID, filename FROM ' . $wpdb->prefix . 'svp_source_videos WHERE source_ID = %d';
			$result = $wpdb->get_results( $wpdb->prepare( $sql, (int) $id ) );
			if ( ! empty( $result ) )
			{
				foreach ( $result as $item )
				{
					if ( ! in_array( $item->filename, $filenames ) )
					{
						$deleted[] = $item->filename;
						if ( $svp_video_base->delete( $item->ID ) === false )
						{
							wp_die( sprintf( __( 'An error occured deleting the video with ID %d from the source with ID %d.', 'svp-translate' ), $item->ID, $id ) );
							exit();
						}
					}
					else
						$unchanged[] = $item->filename;
				}
				$merged = array_merge( $unchanged, $deleted );
				$added = array_diff( $filenames, $merged );
				foreach ( $added as $filename )
					$svp_video_base->save( null, array( 'source_ID' => $id, 'filename' => $filename, 'type' => $this->get_videos_type() ) );
			}
			else // Adds all videos in filenames array
			{
				foreach ( $filenames as $filename )
				{
					$svp_video_base->save( null, array( 'source_ID' => $id, 'filename' => $filename, 'type' => $this->get_videos_type() ) );
					$added[] = $svp_video_base->get_filename();
				}
			}
			
			// Set the scan done
			$wpdb->update( 
				$wpdb->prefix . 'svp_sources', 
				array( 'is_scanned' => 1 ), 
				array( 'ID' => (int) $id ), 
				array( '%d' ), 
				array( '%d' ) );
			
			// Returns added, deleted and unchanged filenames of videos
			return array( 'added' => $added, 'deleted' => $deleted, 'unchanged' => $unchanged );
		}
		
		/**
		 * Default check method.
		 * To check the form validation.
		 * 
		 * @since 1.5.0
		 * @param array $data Data (key => input name, value => input value)
		 * @return bool
		 */
		function check( $data = array() )
		{
			return false;
		}
		
		/**
		 * Returns input list for form source.
		 * 
		 * @since 1.5.0
		 * @return array
		 */
		function get_input_list()
		{
			return array();
		}
		
		/**
		 * Default method to construct the absolute URL to video.
		 * This method must be called in all herited classes.
		 * 
		 * @since 1.5.0
		 * @package SVP_Source_Base
		 * @param string $filename Video filename
		 * @return void
		 */
		function make_video_url( $filename = '' )
		{
			if ( empty( $filename ) )
			{
				wp_die( 'The video filename can not be empty.' );
				exit();
			}
			else
			{
				// Add some includes
				require_once( 'class-utils.php' );
				
				// Get source options
				$options = $this->get_options();
				if ( empty( $options ) )
				{
					wp_die( sprintf( __( 'Source options are undefined. Please read source data before to call method <em>%s</em>.', 'svp-translate' ), __FUNCTION__ ) );
					exit();
				}
				if ( ! array_key_exists( 'svp_source_url', $options ) || ! array_key_exists( 'svp_source_dirname', $options ) )
				{
					wp_die( __( 'The URL or the name directory of the source is missing.', 'svp-translate' ) );
					exit();
				}
			}
		}
		
		/**
		 * Method to construct the absolute URL to thumbnail.
		 * 
		 * @since 1.5.0
		 * @package SVP_Source_Base
		 * @param string $filename Video filename
		 * @return string Thumbnail URL
		 */
		function make_thumbnail_url( $filename = '' )
		{
			if ( empty( $filename ) )
			{
				wp_die( 'The video filename can not be empty.' );
				exit();
			}
			else
			{
				// Add some includes
				require_once( 'class-utils.php' );
				$svp_utils = new SVP_Utils();
				
				// Get source options
				$options = $this->get_options();
				if ( empty( $options ) )
				{
					wp_die( sprintf( __( 'Source options are undefined. Please read source data before to call method <em>%s</em>.', 'svp-translate' ), __FUNCTION__ ) );
					exit();
				}
				if ( ! array_key_exists( 'svp_source_url', $options ) || ! array_key_exists( 'svp_source_dirname', $options ) )
				{
					wp_die( __( 'The URL or the name directory of the source is missing.', 'svp-translate' ) );
					exit();
				}
				
				// Initialize URL
				$url = '';
				
				// Constructs URL
				$url .= $svp_utils->add_endurl_slash( $options['svp_source_url'] );
				if ( ! empty( $options['svp_source_dirname'] ) )
					$url .= $svp_utils->add_endurl_slash( $options['svp_source_dirname'] );
				$pos = strrpos( $filename, '.' );
				if ( $pos !== false )
				{
					$url .= urlencode( substr( $filename, 0, $pos ) . '_' . 
						SVP_VIDEO_SUFFIX_THUMB . '.' . 
						SVP_VIDEO_EXT_THUMB );
				}
				else
					return '';
				
				$thumbnail_handler = @fopen( $url, 'r' );
				if ( $thumbnail_handler === false )
					return '';
				
				return $url;
			}
		}
	}
}