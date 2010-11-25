<?php

abstract class SVP_Movie_Abstract {
	
	protected $_id;
	protected $_title;
	protected $_filename;
	protected $_filesize = 0;
	protected $_type;
	protected $_mime;
	
	public function __set ($name, $value) {
		$method = 'set'.ucfirst($name);
		if (!method_exists($this, $method)) {
			throw new Exception('Invalid "'.$name.'" movie property');
		}
		$this->$method($value);
	}
	
	public function __get ($name) {
		$method = 'get'.ucfirst($name);
		if (!method_exists($this, $method)) {
			throw new Exception('Invalid "'.$name.'" movie property');
		}
		return $this->$method();
	}
	
	public function setId ($value) {
		$this->_id = $value;
	}
	
	public function getId () {
		return $this->_id;	
	}
	
	public function setTitle ($value) {
		$this->_title = $value;
	}
	
	public function getTitle () {
		return $this->_title;	
	}
	
	public function setFilename ($value) {
		$this->_filename = $value;	
	}
	
	public function getFilename () {
		return $this->_filename;
	}
	
	public function setFilesize ($value) {
		$this->_filesize = $value;	
	}
	
	public function getFilesize () {
		return $this->_filesize;
	}
		
	public function getMime () {
		if ($this->_mime == null) {
			$this->_mime = 'video/mpeg';
		}
		return $this->_mime;
	}
	
	public function setType ($value) {
		if (!in_array($value, SVP_Movie::$TYPES)) {
			throw new Exception('the type of movie is invalid.');
		}
		$this->_type = $value;
	}
	
	public function getType () {
		if ($this->_type === null) {
			$type = SVP_Movie::detectType($this->_filename);
			$this->setType($type);
		}
		return $this->_type;
	}
}