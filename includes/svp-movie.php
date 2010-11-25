<?php

require_once(dirname(__FILE__).'/svp-movie-abstract.php');

class SVP_Movie {

	const TYPE_ADAPTIVE = 'adaptive';
	const TYPE_PROGRESSIVE = 'progressive';
	
	public static $TYPES = array(self::TYPE_ADAPTIVE);
	
	public function factory ($type) {
		if (!in_array($type, self::$TYPES)) {
			throw new Exception('Invalid movie type');
		}
		
		$classname = 'SVP_Movie_'.ucfirst($type);
		$file = str_replace('_', '-', strtolower($classname));
		include_once(dirname(__FILE__).'/'.$file.'.php');
		
		$movie = new $classname();
		return $movie;
	}
	
	public static function detectType ($filename) {
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		if ($extension == 'ism') {
			return self::TYPE_ADAPTIVE;
		}
		return null;
	}
	
	public static function url ($movie) {
		
		if ($movie instanceof SVP_Movie_Abstract == false) {
			throw new Exception('the movie must be an instance of SVP_Movie_Abstract.');
		}
		
		$settings = get_option("svp_settings");
		
		$url = '';
		$url .= $settings['svp_movies_server_url'];
		
		if ($movie->getType() == SVP_Movie::TYPE_ADAPTIVE) {
			$url .= $settings['svp_movies_dirname'].'/'; 
		}
		
		$url .= $movie->getFilename();
		
		if ($movie->getType() == SVP_Movie::TYPE_ADAPTIVE) {
			$url .= '/Manifest'; 
		}
		
		return $url;
	}
}