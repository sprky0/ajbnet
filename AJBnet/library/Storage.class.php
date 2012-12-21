<?php

/**
 * Storage handles interaction with the Filesystem.
 *  
 * @package AJBnet
 * @subpackage Library
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 2009
 */
class Storage {

	/*

		storage will have functions to:
			create and delete directories
			copy / move / delete files
			get file info
			read files

	*/

	protected $_Handle;

	public function __construct() {
		// $this->Handle = fopen();
	}
	
	/**
	 * @param $dest target destination
	 * @param $src option source file, assumed existing handle
	 * @return boolean
	 */
	public function Copy($dest,$src = null) {
		return false;
	}

	/**
	 * @param $dest target destination
	 * @param $src option source file, assumed existing handle
	 * @return boolean
	 */
	public function Move($dest,$src = null) {
		// normal move is probably less expensive, but this
		// is something i can write on the train w/o knowing
		// the actual function call yet
		$this->Copy($dest,$src);
		$this->Delete($src);
		return false;
	}
	
	/**
	 * @param string $target
	 * @return boolean
	 */
	public function Delete($target=null) {
		// unlink handle ... or ...
		// unlink($target);
		return false;
	}

	public function __destruct() {
		if (!empty($this->_Handle))
			fclose($this->_Handle);
	}

}
