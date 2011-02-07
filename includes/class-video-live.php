<?php

if ( ! class_exists( 'SVP_Video_Live' ) )
{
	// Add some includes
	require_once( 'class-video-base.php' );
	
	class SVP_Video_Live extends SVP_Video_Base
	{
		function SVP_Video_Live()
		{
			$this->__construct();
		}
		
		function __construct()
		{
			$video = new SVP_Video();
			
			// Set type to adaptive
			$this->set_type( SVP_VIDEO_TYPE_LIVE );
			
			// Set authorized extensions
			$this->set_extensions( array( 'isml' ) );
		}
	}
}