<?php

/**
 * HeaderUtility allows more complex debugging of headers. 
 * 
 * In case your HTTP client is behaving badly, this is very usefull.  Based on a posting describing
 * this process in detail on the Fiji Web Design website.
 * 
 * @package AJBnet
 * @subpackage Library
 * @author Fiji Web Design, Avery Brooks <avery@ajbnet.com>
 * @link http://www.fijiwebdesign.com/fiji-web-design-blog/acess-the-http-request-headers-and-body-via-php.html
 * @copyright 2009
 */
class HeaderUtility extends AJBnet_RPC {

	/** additional HTTP headers not prefixed with HTTP_ in $_SERVER superglobal */
	private $_AddHeaders = array('CONTENT_TYPE', 'CONTENT_LENGTH');

	private $_Raw;
	private $_Method;
	private $_RequestMethod;
	private $_Headers;
	private $_Body;

	private $_Protocol;

	/**
	* Construtor
	* Retrieve HTTP Body
	* @param Array Additional Headers to retrieve
	*/
	public function __construct($dumped) {
		$add_headers = false;
		$this->RetrieveHeaders($add_headers);
		$this->_Body = @file_get_contents('php://input');
	}

	/**
	* Retrieve the HTTP request headers from the $_SERVER superglobal
	* @param Array Additional Headers to retrieve
	*/
	private function RetrieveHeaders($add_headers = false) {

		if ($add_headers)
			$this->_AddHeaders = array_merge($this->_AddHeaders, $add_headers);
	
		if (isset($_SERVER['HTTP_METHOD'])) {
			$this->_Method = $_SERVER['HTTP_METHOD'];
			unset($_SERVER['HTTP_METHOD']);
		} else {
			$this->_Method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;
		}
		$this->_Protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : false;
		$this->_RequestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;
		
		$this->_Headers = array();
		foreach($_SERVER as $i=>$val) {
			if (strpos($i, 'HTTP_') === 0 || in_array($i, $this->_AddHeaders)) {
				$name = str_replace(array('HTTP_', '_'), array('', '-'), $i);
				$this->_Headers[$name] = $val;
			}
		}
	}
	
	/** 
	* Retrieve HTTP Method
	*/
	public function GetMethod() {
		return $this->_Method;
	}
	
	/** 
	* Retrieve HTTP Body
	*/
	public function GetBody() {
		return $this->_Body;
	}
	
	/** 
	* Retrieve an HTTP Header
	* @param string Case-Insensitive HTTP Header Name (eg: "User-Agent")
	*/
	public function GetHeader($name) {
		$name = strtoupper($name);
		return isset($this->_Headers[$name]) ? $this->_Headers[$name] : false;
	}
	
	/**
	* Retrieve all HTTP Headers 
	* @return array HTTP Headers
	*/
	private function GetHeaders() {
		return $this->_Headers;
	}
	
	/**
	* Return Raw HTTP Request (note: This is incomplete)
	* @param bool ReBuild the Raw HTTP Request
	*/
	public function GetRaw($refresh = false) {

		if (isset($this->_Raw) && !$refresh)
			return $this->_Raw; // return cached

		$headers = $this->GetHeaders();
		$this->_Raw = "{$this->_Method}\r\n";

		foreach($headers as $i=>$header)
			$this->_Raw .= "$i: $header\r\n";

		$this->_Raw .= "\r\n{$this->body}";

		return $this->_Raw;
	}

} // end of class HeaderUtility

?>