<?php

require_once(dirname(__FILE__).'/svp-movie.php');

class SVP_Post {

	protected $_post;
	
	protected $_id;
	protected $_title;
	protected $_date;
	protected $_guid;
	protected $_content;
	protected $_movie;
	
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
	
	public function setMovie ($value) {
		$this->_movie = $value;
	}
	
	public function getMovie () {
		return $this->_movie;
	}
	
	public function setDate($value) {
		$this->_date = trim($value);
	}
	
	public function getDate() {
		return $this->_date;	
	}
	
	public function setGuid($value) {
		$this->_guid = trim($value);
	}
	
	public function getGuid() {
		return $this->_guid;	
	}
	
	public function setContent($value) {
		$this->_content = trim($value);
	}
	
	public function getContent() {
		return $this->_content;
	}
	
}