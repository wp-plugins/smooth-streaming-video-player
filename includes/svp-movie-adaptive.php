<?php
require_once(dirname(__FILE__).'/svp-movie.php');
require_once(dirname(__FILE__).'/svp-movie-abstract.php');

class SVP_Movie_Adaptive 
	extends SVP_Movie_Abstract {
	
	protected $_type = SVP_Movie::TYPE_ADAPTIVE;
}