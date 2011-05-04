<?php

if ( ! class_exists( 'SVP_Video' ) )
{
	
	// Defines some contants for differents types of videos
	if ( ! defined( 'SVP_VIDEO_TYPE_ADAPTIVE' ) )
		define( 'SVP_VIDEO_TYPE_ADAPTIVE', 'ADAPTIVE' );
	if ( ! defined( 'SVP_VIDEO_TYPE_LIVE' ) )
		define( 'SVP_VIDEO_TYPE_LIVE', 'LIVE' );
	if ( ! defined( 'SVP_VIDEO_TYPE_PROGRESSIVE' ) )
		define( 'SVP_VIDEO_TYPE_PROGRESSIVE', 'PROGRESSIVE' );
	
	class SVP_Video
	{
		// Properties
		var $types = array(
			SVP_VIDEO_TYPE_ADAPTIVE, 
			SVP_VIDEO_TYPE_PROGRESSIVE, 
			SVP_VIDEO_TYPE_LIVE );
		var $authorized_extensions = array( 'ism', 'isml', 'csm', 'wmv', 'mp4' );
		
		/**
		 * Returns a typed video class instance.
		 *
		 * @param mixed $type Type of the video class to instanciate
		 * @return mixed Instance of video class
		 */
		function factory( $type )
		{
			if ( ! in_array( $type, $this->types ) )
			{
				wp_die( __( 'Invalid video type.', 'svp-translate' ) );
				exit();
			}
			$classname = 'SVP_Video_' . ucfirst( $type );
			$file = str_replace( array( '_', 'svp' ), array( '-', 'class' ), strtolower( $classname ) );
			include_once( dirname( __FILE__ ) . '/' . $file . '.php' );
			$video = new $classname();
			return $video;
		}
		
		/**
		 * Returns the type of a video from his filename.
		 * 
		 * @param string $filename Video filename
		 * @return string Type of the video (ADAPTIVE or PROGRESSIVE)
		 */
		function detect_type( $filename )
		{
			$extension = pathinfo( $filename, PATHINFO_EXTENSION );
			
			// Check if extension is authorized
			if ( ! in_array( $extension, $this->authorized_extensions ) )
			{
				wp_die( sprintf( __( 'Extension &laquo;&nbsp;%s&nbsp;&raquo; is unauthorized.', 'svp-translate' ), $extension ) );
				exit();
			}
			
			switch ( $extension )
			{
				case 'ism':
					return SVP_VIDEO_TYPE_ADAPTIVE; // Adaptive type
				case 'isml':
					return SVP_VIDEO_TYPE_LIVE; // Live type
				default:
					return SVP_VIDEO_TYPE_PROGRESSIVE; // Progressive type
			}
		}
	}
}