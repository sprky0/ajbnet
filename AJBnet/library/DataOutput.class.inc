<?php

/**
 * DataOutput
 * Handles outputting data
 * in various text formats.
 * 
 * @author Avery Brooks
 * @package AJBnet
 * @subpackage Library
 * @copyright 2009
 * 
 */
class DataOutput {

	private $Data;
	private $Exceptions = array();
	private $Messages = array();
	private $Status = "200";

	private $Charset = "utf-8";
	private $Encoding = "";

	private $Format = "json";
	private $Supported = array("html","xml","json","csv");

	private $SendHeaders = true;
	private $SendStatus = true;

	/**
	 * @param boolean $send_http_status
	 */
	public function __construct($send_http_status = true) {
		$this->SendStatus = (boolean) $send_http_status;
	}

	/**
	 * Output the current dataset in the desired format.
	 */
	public function Output() {
		
		$this->AddMessage("Generated " . date("Y-m-d h:i:s"));

		if ($this->SendStatus === true)
			$this->SendHTTPStatus($this->Status);

		switch($this->Format):

			case "html":
			header("Content-Type: " . MimeTypes::HTML . "; charset={$this->Charset}");
			header("Content-Encoding: {$this->Encoding}");
			$output = $this->_ToHTML();
			break;

			case "xml":
			header("Content-type: " . MimeTypes::XML . "; charset={$this->Charset}");
			header("Content-Encoding: {$this->Encoding}");
			$output = $this->_ToXML();
			break;

			case "json":
			// header("Content-type: " . MimeTypes::JSON . "; charset={$this->Charset}");
			header("Content-Encoding: {$this->Encoding}");
			$output = $this->_ToJson();
			break;

			case "csv":
			header("Content-type: " . MimeTypes::CSV . "; charset={$this->Charset}");
			header("Content-Encoding: {$this->Encoding}");
			$output = $this->_ToCSV();
			break;

		endswitch;

		// echo gzip($output);
		echo utf8_encode($output);

	}


	/**
	 * @param mixed $data
	 */
	public function SetData($data) {
		$this->Data = $data;
	}

	/**
	 * @param mixed format
	 * @return boolean
	 */
	public function SetFormat($format) {
		if (!in_array(strtolower($format),$this->Supported))
			return false;
		$this->Format = $format;
		return true;
	}

	/**
	 * Set the HTTP status code that will be sent.
	 * 
	 * @param integer $status
	 */
	public function SetStatus($status) {
		$this->Status = $status;
	}

	/**
	 * Add the text representation of an exception.
	 * 
	 * @param string exception
	 */
	public function AddException($exception) {
		$this->Exceptions[] = $exception;
	}

	/**
	 * Add an array of messages.
	 * 
	 * @param array messages
	 */
	public function SetMessages($messages) {
		$this->Messages = array_merge($messages,$this->Messages);
	}

	/**
	 * Add a single message.
	 * 
	 * @param string $message
	 */
	public function AddMessage($message) {
		$this->Messages[] = $message;
	}

	/**
	 * Helper to prepare our data and meta information for output.
	 * 
	 * @return array
	 * @internal
	 */
	private function _Pack() {
		return array(
			"Exceptions" => $this->Exceptions,
			"Messages" => $this->Messages,
			"Status" => $this->Status,
			"Data" => $this->Data,
		);
	}

	/**
	 * @return array
	 * @internal
	 */
	private function _ToCSV() {

		// Data only, for now.
		// This can be greatly improved.

		$ret = "";

		if (is_array($this->Data) && isset($this->Data[0])) {
			$headers = array_keys($this->Data[0]);
			$ret .= implode(",",$headers) . "\n";
		}

		if (is_array($this->Data)) {
			foreach($this->Data as $D) {
				if (is_array($D)) {
					foreach($D as &$d)
						$d = "{$d}";
					$ret .= implode(",",$D) . "\n";
				} else {
					$ret .= "{$D}\n";					
				}
			}
		} else {
			$ret .= "{$this->Data}\n";
		}

		return $ret;

	}


	/**
	 * @return string
	 * @internal
	 */
	private function _ToJson() {
		$data = $this->_Pack();
		return json_encode($data);
	}


	/**
	 * @return string
	 * @internal
	 */
	private function _ToXML() {

		$data = $this->_Pack();

		$d = new DomDocument("1.0","utf-8");
		$e = $d->createElement("Response");
		$d->appendChild($e);
		
		$this->_Array2XML($data,$e,$d);

		return $d->saveXML();
	}


	/**
	 * Recursive XML writer function.
	 * 
	 * @return array
	 * @internal used by DataOutput::_ToXML()
	 */
	private function _Array2XML($data,$parent,$d) {

		if (!is_array($data)) return false;

		foreach($data as $k => $v) {

			$el = (!is_numeric($k)) ? $k : "E" . $k;

			$x = $d->createElement($el);
		
			if (is_array($v))
				$this->_Array2XML($v,$x,$d);
			else if (is_object($v))
				$this->_Array2XML($v,$x,$d); // no idea here
			else if (is_numeric($v))
				$x->appendChild($d->createTextNode($v));
			else
				$x->appendChild($d->createCDATASection($v));

			$parent->appendChild($x);

		}

	}
	
	/**
	 * HTML output option - primarily for debugging purposes.  This CMS doesn't speak `HTML`.
	 * 
	 * @return string
	 * @internal
	 */
	private function _ToHTML() {
		$data = $this->_Pack();
		$out = "<html><body><pre>".var_export($data,true)."</pre></body></html>";
		return $out;
	}

	/**
	 * @return integer
 	 * @internal
	 */
	private function SendHTTPStatus($code = 200, $header = true) {

		// Original list from:
		// Source: http://en.wikipedia.org/wiki/List_of_HTTP_status_codes

		// Elements borrowed from:
		// http://www.compiledweekly.com/2008/12/31/php-function-http-status-code-value-as-string/

		$code = (int) $code;
		$res = "";

		switch( $code ) {

			// 1xx Informational
			case 100: $res = 'Continue'; break;
			case 101: $res = 'Switching Protocols'; break;
			case 102: $res = 'Processing'; break; // WebDAV
			case 122: $res = 'Request-URI too long'; break; // Microsoft

			// 2xx Success
			case 200: $res = 'OK'; break;
			case 201: $res = 'Created'; break;
			case 202: $res = 'Accepted'; break;
			case 203: $res = 'Non-Authoritative Information'; break; // HTTP/1.1
			case 204: $res = 'No Content'; break;
			case 205: $res = 'Reset Content'; break;
			case 206: $res = 'Partial Content'; break;
			case 207: $res = 'Multi-Status'; break; // WebDAV
	
			// 3xx Redirection
			case 300: $res = 'Multiple Choices'; break;
			case 301: $res = 'Moved Permanently'; break;
			case 302: $res = 'Found'; break;
			case 303: $res = 'See Other'; break; //HTTP/1.1
			case 304: $res = 'Not Modified'; break;
			case 305: $res = 'Use Proxy'; break; // HTTP/1.1
			case 306: $res = 'Switch Proxy'; break; // Depreciated
			case 307: $res = 'Temporary Redirect'; break; // HTTP/1.1

			// 4xx Client Error
			case 400: $res = 'Bad Request'; break;
			case 401: $res = 'Unauthorized'; break;
			case 402: $res = 'Payment Required'; break;
			case 403: $res = 'Forbidden'; break;
			case 404: $res = 'Not Found'; break;
			case 405: $res = 'Method Not Allowed'; break;
			case 406: $res = 'Not Acceptable'; break;
			case 407: $res = 'Proxy Authentication Required'; break;
			case 408: $res = 'Request Timeout'; break;
			case 409: $res = 'Conflict'; break;
			case 410: $res = 'Gone'; break;
			case 411: $res = 'Length Required'; break;
			case 412: $res = 'Precondition Failed'; break;
			case 413: $res = 'Request Entity Too Large'; break;
			case 414: $res = 'Request-URI Too Long'; break;
			case 415: $res = 'Unsupported Media Type'; break;
			case 416: $res = 'Requested Range Not Satisfiable'; break;
			case 417: $res = 'Expectation Failed'; break;

			case 418: $res = "I'm a teapot."; break;

			case 422: $res = 'Unprocessable Entity'; break; // WebDAV
			case 423: $res = 'Locked'; break; // WebDAV
			case 424: $res = 'Failed Dependency'; break; // WebDAV
			case 425: $res = 'Unordered Collection'; break; // WebDAV
			case 426: $res = 'Upgrade Required'; break;
			case 449: $res = 'Retry With'; break; // Microsoft
			case 450: $res = 'Blocked'; break; // Microsoft

			// 5xx Server Error
			case 500: $res = 'Internal Server Error'; break;
			case 501: $res = 'Not Implemented'; break;
			case 502: $res = 'Bad Gateway'; break;
			case 503: $res = 'Service Unavailable'; break;
			case 504: $res = 'Gateway Timeout'; break;
			case 505: $res = 'HTTP Version Not Supported'; break;
			case 506: $res = 'Variant Also Negotiates'; break;
			case 507: $res = 'Insufficient Storage'; break; // WebDAV
			case 509: $res = 'Bandwidth Limit Exceeded'; break; // Apache
			case 510: $res = 'Not Extended'; break;
	
			// Unknown code:
			default: $res = 'Unknown';  break;
		}

		if (true === $header)
			header("HTTP/1.0 {$code} {$res}");

		return $res;
	}

}
