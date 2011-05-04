<?php
/**
 * Classe en charge de la gestion des vidéos.
 */

if ( ! class_exists( 'SVP_Videos' ) )
{
	
	// Add some includes
	require_once( 'class-post.php' );
	require_once( 'class-video.php' );
	
	class SVP_Videos
	{
		// Properties
		var $_data;
		
		// Setters et getters
		function set_data( $value )
		{
			$this->_data = trim( $value );
		}
		
		function get_data()
		{
			return $this->_data;
		}
		
		/**
		 * Retrieves the list of available videos.
		 *
		 * @return array Videos objects
		 */
		function get_all_available_videos()
		{
			global $wpdb;
			$query = 'SELECT ID, filename FROM ' . $wpdb->prefix . 'svp_source_videos ORDER BY filename';
			$videos = $wpdb->get_results( $query );
			return $videos;
		}
		
		/**
		 * Retourne la liste des vidéos.
		 *
		 * @return array Tableau d'objets
		 */
		function get_posts_with_video()
		{
			global $wpdb;
			$sql = 'SELECT p.ID, p.post_date, p.post_title, p.post_content, p.guid, v.filename ';
			$sql .= 'FROM ' . $wpdb->prefix . 'posts p ';
			$sql .= 'INNER JOIN  ' . $wpdb->prefix . 'svp_post_videos f ON p.ID = f.post_ID ';
			$sql .= 'INNER JOIN  ' . $wpdb->prefix . 'svp_source_videos v ON f.video_ID = v.ID ';
			$sql .= 'WHERE p.post_type = %s AND p.post_status = %s ';
			$sql .= 'ORDER BY p.post_date DESC';
			
			$options = get_option( 'svp_settings' );
			if ( $options['svp_items_count'] > 0 )
				$sql .= ' LIMIT ' . $options['svp_items_count'];
			
			$rows = $wpdb->get_results( $wpdb->prepare( $sql, 'post', 'publish' ) );
			
			$entries = array();
			foreach ( $rows as $row )
			{
				$entry = new SVP_Post();
				$entry->set_ID( $row->ID );
				$entry->set_title( $row->post_title );
				$entry->set_date( $row->post_date );
				$entry->set_guid( $row->guid );
				$entry->set_content( $row->post_content );
				$svp_video = new SVP_Video();
				$video = $svp_video->factory( $svp_video->types[0] );
				$video->set_filename( $row->filename );
				$entry->set_video( $video );
				$entries[] = $entry;
			}
			
			return $entries;
		}
		
		/**
		 * Filter a list of videos by extensions.
		 * 
		 * @since 1.5.0
		 * @param array Videos list
		 * @param array Authorized extensions
		 * @param string Path before filename to delete
		 * @return array Filtered videos list
		 */
		function filter_by_extensions( $videos = array(), $extensions = array(), $path = '' )
		{
			if ( empty( $extensions ) )
			{
				wp_die( __( 'No extensions defined.', 'svp-translate' ) );
				exit();
			}
			require_once( 'class-utils.php' );
			$svp_utils = new SVP_Utils();
			$filtered_videos = array();
			foreach ( $videos as $video )
			{
				if ( ! empty( $path ) )
					$video = str_replace( $path, '', $video );
				$video = $svp_utils->delete_endurl_slash( $video );
				$extension = substr( $video, strrpos( $video, '.' ) + 1 );
				if ( $extension !== false && in_array( $extension, $extensions ) )
					$filtered_videos[] = $video;
			}
			return $filtered_videos;
		}
	}
}