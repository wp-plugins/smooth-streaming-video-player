<?php

if ( ! class_exists( 'SVP_Video_Progressive' ) )
{
	// Add some includes
	require_once( 'class-video-base.php' );
	
	class SVP_Video_Progressive extends SVP_Video_Base
	{
		function SVP_Video_Progressive()
		{
			$this->__construct();
		}
		
		function __construct()
		{
			$video = new SVP_Video();
			
			// Set type to progressive
			$this->set_type( SVP_VIDEO_TYPE_PROGRESSIVE );
			
			// Set authorized extensions
			$this->set_extensions( array( 'mp4', 'wmv' ) );
		}
	}
}