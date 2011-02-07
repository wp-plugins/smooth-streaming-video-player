<?php

if ( ! class_exists( 'SVP_Post' ) )
{
	class SVP_Post
	{
		// Properties
		var $_ID;
		var $_title;
		var $_date;
		var $_guid;
		var $_content;
		var $_video;
		var $_has_video_entry = false;
		
		// Getters and setters
		function set_ID( $value )
		{
			$this->_id = $value;
		}
		
		function get_ID()
		{
			return $this->_id;	
		}
		
		function set_title( $value )
		{
			$this->_title = $value;
		}
		
		function get_title()
		{
			return $this->_title;	
		}
		
		function set_video( $value )
		{
			$this->_video = $value;
		}
		
		function get_video()
		{
			return $this->_video;
		}
		
		function set_date( $value )
		{
			$this->_date = trim( $value );
		}
		
		function get_date()
		{
			return $this->_date;	
		}
		
		function set_guid( $value )
		{
			$this->_guid = trim( $value );
		}
		
		function get_guid()
		{
			return $this->_guid;	
		}
		
		function set_content( $value )
		{
			$this->_content = trim( $value );
		}
		
		function get_content()
		{
			return $this->_content;
		}
		
		function set_has_video_entry( $value )
		{
			$this->_has_video_entry = (bool) $value;
		}
		
		function get_has_video_entry()
		{
			return $this->_has_video_entry;
		}
		
		/**
		 * Connects a video with a post.
		 *
		 * @param int $id Post ID
		 * @return int Post ID added or updated
		 */
		function attach_video( $data = array() )
		{
			if ( ! array_key_exists( 'post_ID', $data ) || empty( $data['post_ID'] ) )
			{
				wp_die( __( 'Post ID is undefined.', 'svp-translate' ) );
				exit();
			}
			global $wpdb;
			$sql = 'SELECT post_ID FROM ' . $wpdb->prefix . 'svp_post_videos WHERE post_ID = %d';
			$post = $wpdb->get_row( $wpdb->prepare( $sql, $data['post_ID'] ) );
			if ( ! empty( $data['video_ID'] ) )
			{
				if ( empty( $post ) ) // Add
				{
					$wpdb->insert( 
						$wpdb->prefix . 'svp_post_videos', 
						$data, 
						array( '%d', '%d' ) );
				}
				else // Update
				{
					$wpdb->update( 
						$wpdb->prefix . 'svp_post_videos', 
						$data, 
						array( 'post_ID' => (int) $data['post_ID'] ), 
						array( '%d', '%d' ), 
						array( '%d' ) );
				}
			}
			else
			{
				if ( ! empty( $post ) ) // Delete (in case where video_ID is equal to 0)
				{
					$sql = 'DELETE FROM ' . $wpdb->prefix . 'svp_post_videos WHERE post_ID = %d';
					$wpdb->query( $wpdb->prepare( $sql, $data['post_ID'] ) );
				}
			}
			return $data['post_ID'];
		}
		
		/**
		 * Checks if an entry exists in the svp_post_videos table for the current post.
		 * 
		 * @param int $id Post ID
		 * @return bool
		 */
		function has_video_entry( $id = null )
		{
			if ( empty( $id ) )
				return false;
			global $wpdb;
			$query = 'SELECT video_ID FROM ' . $wpdb->prefix . 'svp_post_videos WHERE post_ID = %u';
			$video = $wpdb->get_row( $wpdb->prepare( $query, (int) $id ) );
			if ( ! is_null( $video ) )
				return true;
			return false;
		}
		
		/**
		 * Retrieves data from a post.
		 *
		 * @since 1.5.0
		 * @param int $id Post ID
		 * @return SVP_Post
		 */
		function read( $id = null )
		{
			if ( empty( $id ) )
			{
				wp_die( __( 'Post ID is undefined.', 'svp-translate' ) );
				exit();
			}
			
			global $wpdb;
			
			$sql = 'SELECT ID, post_title, post_date, guid, post_content FROM ' . $wpdb->prefix . 'posts WHERE ID = %d';
			$post = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );
			if ( ! is_null( $post ) )
			{
				$this->set_ID( $post->ID );
				$this->set_title( $post->post_title );
				$this->set_date( $post->post_date );
				$this->set_guid( $post->guid );
				$this->set_content( $post->post_content );
				$this->set_has_video_entry( $this->has_video_entry( $id ) );
				$sql = 'SELECT sv.* FROM ' . $wpdb->prefix . 'svp_post_videos pv INNER JOIN ' . $wpdb->prefix . 'svp_source_videos sv ON pv.video_ID = sv.ID WHERE pv.post_ID = %d';
				$row = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );
				if ( ! is_null( $row ) )
				{
					require_once( 'class-video.php' );
					$svp_video = new SVP_Video();
					$video = $svp_video->factory( $row->type );
					$video->set_ID( $row->ID );
					$video->set_source_ID( $row->source_ID );
					$video->set_filename( $row->filename );
					$video->set_type( $row->type );
					$this->set_video( $video );
				}
			}
			else
			{
				wp_die( sprintf( __( 'This post with ID %s does not exist.', 'svp-translate' ), $id ) );
				exit();
			}
			return $this;
		}
		
		/**
		 * Returns the URL of a video associated with a post.
		 *
		 * @since 1.5.0
		 * @package SVP_Post
		 * @return string URL to the video
		 */
		function get_video_url()
		{		
			if ( ! is_null( $this->get_video() ) && ! is_null( $this->get_video()->get_source_ID() ) )
			{
				// Add some includes
				require_once( 'class-source.php' );
				require_once( 'class-source-base.php' );
				
				$svp_source_base = new SVP_Source_Base();
				$svp_source_base->read( $this->get_video()->get_source_ID() );
				
				$svp_source = new SVP_Source();
				$source = $svp_source->factory( $svp_source_base->get_source_type_code() );
				$source->read( $this->get_video()->get_source_ID() );
				
				return $source->make_video_url( $this->get_video()->get_filename() );
			}
			else
			{
				wp_die( sprintf( __( 'Video source ID is undefined. Please read post data before to call method <em>%s</em>.', 'svp-translate' ), __FUNCTION__ ) );
				exit();
			}
		}
		
		/**
		 * Returns the URL of the video thumbnail.
		 *
		 * @since 1.5.0
		 * @package SVP_Post
		 * @return string URL to the thumbnail
		 */
		function get_thumbnail_url()
		{
			if ( ! is_null( $this->get_video() ) && ! is_null( $this->get_video()->get_source_ID() ) )
			{
				// Add some includes
				require_once( 'class-source.php' );
				require_once( 'class-source-base.php' );
				
				$svp_source_base = new SVP_Source_Base();
				$svp_source_base->read( $this->get_video()->get_source_ID() );
				
				$svp_source = new SVP_Source();
				$source = $svp_source->factory( $svp_source_base->get_source_type_code() );
				$source->read( $this->get_video()->get_source_ID() );
				
				return $source->make_thumbnail_url( $this->get_video()->get_filename() );
			}
			else
			{
				wp_die( sprintf( __( 'Video source ID is undefined. Please read post data before to call method <em>%s</em>.', 'svp-translate' ), __FUNCTION__ ) );
				exit();
			}
		}
	}
}