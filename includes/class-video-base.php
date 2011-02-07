<?php

if ( ! class_exists ( 'SVP_Video_Base') )
{
	
	// Add some includes
	require_once( 'class-video.php' );
	
	class SVP_Video_Base
	{	
		var $_ID;
		var $_source_ID;
		var $_filename;
		var $_type;
		var $_posts = array();
		var $_extensions = array();
		
		// Getters and setters
		function set_ID( $value )
		{
			$this->_ID = $value;
			return $this;
		}
		
		function get_ID()
		{
			return $this->_ID;
		}
		
		function set_source_ID( $value )
		{
			$this->_source_ID = $value;
			return $this;
		}
		
		function get_source_ID()
		{
			return $this->_source_ID;	
		}
		
		function set_filename( $value )
		{
			$this->_filename = $value;
			return $this;
		}
		
		function get_filename()
		{
			return $this->_filename;
		}
		
		function set_type( $value )
		{
			$video = new SVP_Video();
			if ( ! in_array( $value, $video->types ) )
			{
				wp_die( __( 'Video type is invalid.', 'svp-translate') );
				exit();
			}
			$this->_type = $value;
			return $this;
		}
		
		function get_type()
		{
			if ( $this->_type === null )
			{
				$svp_video = new SVP_Video();
				$type = $svp_video->detect_type( $this->_filename );
				$this->set_type( $type );
			}
			return $this->_type;
		}
		
		function set_posts( $value )
		{
			if ( ! is_array( $value ) )
			{
				wp_die( __( 'The list of posts must be an array.', 'svp-translate' ) );
				exit();
			}
			$this->_posts = $value;
			return $this;
		}
		
		function get_posts()
		{
			return $this->_posts;
		}
		
		function set_extensions( $extensions )
		{
			if ( ! is_array( $extensions ) )
			{
				wp_die( __( 'Value for extensions must be an array.', 'svp-translate') );
				exit();
			}
			// Check if passed extensions are authorized
			$video = new SVP_Video();
			foreach ( $extensions as $extension )
			{
				if ( ! in_array( $extension, $video->authorized_extensions ) )
				{
					wp_die( __( 'Extension is invalid or unauthorized.', 'svp-translate') );
					exit();
				}
			}
			$this->_extensions = $extensions;
			return $this;
		}
		
		function get_extensions()
		{
			return (array) $this->_extensions;
		}
				
		/**
		 * Default save method.
		 * To add or update video data.
		 *
		 * @since 1.5.0
		 * @param int $id Video ident
		 * @param array $data Video data to save (based on key as field name and value as field value)
		 * @return object Video
		 */
		function save( $id = null, $data = array() )
		{
			global $wpdb;
			if ( empty( $id ) ) // Add
			{
				$wpdb->insert( 
					$wpdb->prefix . 'svp_source_videos', 
					$data, 
					array( '%d', '%s', '%s' ) );
				$id = $wpdb->insert_id;
			}
			else // Update
			{
				$wpdb->update( 
					$wpdb->prefix . 'svp_source_videos', 
					$data, 
					array( 'ID' => (int) $id ), 
					array( '%d', '%s', '%s' ), 
					array( '%d' ) );
			}
			$this->read( (int) $id );
		}
		
		/**
		 * Default read method.
		 * To read video data.
		 * 
		 * @since 1.5.0
		 * @param int Video ident
		 * @return object Video data
		 */
		function read( $id = null )
		{
			if ( empty( $id ) )
			{
				wp_die( __( 'Parameter ID is undefined.', 'svp-translate' ) );
				exit();
			}
			
			global $wpdb;
			
			// Get video data from videos table
			$sql = 'SELECT * FROM ' . $wpdb->prefix . 'svp_source_videos WHERE ID = %d';
			$row = $wpdb->get_row( $wpdb->prepare( $sql, (int) $id ) );
			
			// Retrieves other data from the video
			if ( ! empty( $row ) )
			{
				$sql = 'SELECT * FROM ' . $wpdb->prefix . 'svp_post_videos WHERE video_ID = %d';
				$this->set_posts( $wpdb->get_results( $wpdb->prepare( $sql, (int) $id ) ) );
			}
			
			// Sets properties
			$this->set_ID( $row->ID );
			$this->set_source_ID( $row->source_ID );
			$this->set_filename( $row->filename );
			$this->set_type( $row->type );
		}
		
		/**
		 * Default delete method.
		 * To delete video data.
		 * Delete also the association beetween a video and a post.
		 *
		 * @since 1.5.0
		 * @param int $id Video ident
		 * @return bool
		 */
		function delete( $id = null )
		{
			if ( is_null( $id ) )
			{
				wp_die( __( 'Parameter ID is undefined.', 'svp-translate' ) );
				exit();
			}
			global $wpdb;
			
			// Delete video associated to a post
			$sql = 'DELETE FROM ' . $wpdb->prefix . 'svp_post_videos WHERE video_ID = %d';
			$result = $wpdb->query( $wpdb->prepare( $sql, $id ) );
			if ( $result === false )
				return false;
			
			// Delete video inside the source
			$sql = 'DELETE FROM ' . $wpdb->prefix . 'svp_source_videos WHERE ID = %d';
			$result = $wpdb->query( $wpdb->prepare( $sql, $id ) );
			if ( $result === false )
				return false;
			
			return true;
		}
	}
}