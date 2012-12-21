<?php

/**
 * AJBnet Controller
 * 
 * ToDo:
 * 
 * - multiple levels in config XML for logical groupings / readability
 * 
 * Notes:
 * 
 * 	 We call this group/action but really it's:
 *	 / Class / Function / ID . Format
 *	 translates to Class.class.inc -> x = new Class() -> x->Function(ID,Format)
 *	 or something like that
 *
 *	 Important note on proces here
 *	 
 *	 PATH is /at/at/at/at/
 *	 or anything before the trailing slash
 *	  
 *	 ID targeted is anything after that, broken into ID.format by a period
 *	 so, in
 *	 
 *	 /path/id.format
 *	 
 *	 id is the id
 *	 
 *	 or 
 *	 
 *	 /path/lasttoken
 *	 
 *	 lasttoken is the id
 *	 
 *	 
 *	 i'm sure this will be come a problem some day, but that is i think the best way to deliniate
 *	 dfault action - if not specified is -> public function Run()
 * 
 * 
 * @package AJBnet
 * @subpackage Core
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 2009-2011
 */
class AJBnet {

	// Access Modes
	const API_MODE = 001;
	const NATIVE_MODE = 002;
	const CLI_MODE = 003;

	// Command Modes
	const PATH_AS_DIRECTORIES = 101; // "DIRECTORIES";
	const PATH_AS_FIRSTKEY = 102; // "FIRSTKEY";
	const PATH_AS_FIRSTPARAM = 103; // "FIRSTPARAM";
	
	const JSON = "JSON";
	const XML = "XML";
	
	/*
	We need something readable like this to simplify file access specifications
	
	const READ = 201;
	const WRITE = 202;
	const APPEND = 203;
	*/
	
	/**
	 * User DAO
	 * 
	 * @var User
	 */
	public $User;
	
	private $_Mode = AJBnet::API_MODE;
	private $_AccessMethod = AJBnet::PATH_AS_DIRECTORIES;
	
	private $TargetClass;
	private $TargetFunction;
	private $TargetID = "";
	private $TargetFormat = AJBnet::JSON;
	private $Path = "";

	// Loaded classes
	private $Loaded = array();

	// Classes or files to exclude from automatic loader
	protected $Exclude = array(
		".",
		"..",
		".svn",
		"Exceptions.package.inc",
		"AJBnet.loader.inc",
		"AJBnet.class.inc",
	);

	/**
	 * AJBnet instance configuration.
	 * @var array
	 */
	private $Config = array(
		"default-format" => "json",
		"install-dir" => "",
		"mod-dir" => "module/",
		"lib-dir" => "library/",
		"cache-images" => true
	);

	/**
	 * Timer data used to determine execution time.
	 * @var array
	 */
	private $Timer_Markers = array();

	/**
	 * Responsible for setting up the environment.
	 * This means, starting the session, loading configuration
	 * information, loading libraries and modules, instantiating the
	 * user object if needed.
	 */
	public function __construct() {

		$this->Timer_SetMark("Construct");
		
		@session_start();
		ob_start();

		// Internal deps.

		// @todo abstract this constant out elsewhere ?  (it's defined in the Loader script, but maybe it could be set locally, in the constructor)
		$this->LoadConfig( AJBNET_INSTALL_DIRECTORY . 'conf/config.xml'); // config

		// Set the timezone
		date_default_timezone_set($this->GetConfig("timezone"));

		// @todo remove this!  handled by autoloader
		/*
		$this->LoadLibrary();
		$this->LoadModules();
		*/

		if ($this->GetConfig("db-enabled") === true)
			$this->User = $this->Construct("User");

	}

	public function __destruct() {
		ob_flush();
	}

	/**
	 * Construct is a factory to allow the construction of an
	 * aribrary preloaded class.  This is used internally for
	 * AJBnet required classes.
	 * 
	 * @param string $Class
	 * @return mixed|bool
	 */
	public function Construct($Class) {
		if (!class_exists($Class))
			return false;
		return new $Class(&$this);
	}

	/**
	 * After configuration is complete, process and
	 * handle all requests.
	 * 
	 * @param string $PathOverride
	 * @return mixed
	 */
	public function Run($PathOverride=null) {

		try {

			// Find out what we're up to today
			$this->_ParsePath($PathOverride);

			if (!isset($this->TargetClass) || !class_exists($this->TargetClass))
				throw new ApplicationException("Can not find referenced Class!");

			// method_exists vs function_exists -- function is external, method is a member function of a class

			if (!isset($this->TargetFunction) || FALSE === method_exists($this->TargetClass,$this->TargetFunction))
				throw new ApplicationException("Can not find '".$this->TargetClass."::".$this->TargetFunction."()'.");

			// see if we're allowed to run the target action

			if (!$this->Authorize($this->TargetClass,$this->TargetFunction))
				throw new SecurityException("Not authorized to perform that Action!",403);

			$Executioner = $this->Construct($this->TargetClass);

			if (!empty($this->TargetID))
				$Executioner->SetID($this->TargetID);

			if (!empty($this->Path))
				$Executioner->SetPath($this->Path);

			$Tar = $this->TargetFunction;
			$Res = $Executioner->$Tar();
			$Mes = $Executioner->GetMessages();

			// How about errors that don't stop execution ?  (see below (output))

			return $this->_Output($Res,200,$Mes);

 		} catch (ApplicationException $e) {

			return $this->_OutputException($e);

 		} catch (DatabaseException $e) {

 			return $this->_OutputException($e);

 		} catch (SecurityException $e) {

 			return $this->_OutputException($e);

 		} catch (AJBnetException $e) {

 			return $this->_OutputException($e);

 		}

	}

	/**
	 * Authorize target action.  Used by $this->Run before before class is constructed.
	 * 
	 * @param string $class classname
	 * @param string $function method to authorize
	 * @return boolean
	 */
	private function Authorize($class,$function) {
		$res = false;
		$php = '$res='."{$class}::AJBnet_Authorize('{$function}');";
		eval($php);
		return $res;
	}

	public function SetMode($API_MODE) {
		switch ($API_MODE):

			case AJBnet::API_MODE:
				$this->_Mode = AJBnet::API_MODE;
				break;

			case AJBnet::NATIVE_MODE:
				$this->_Mode = AJBnet::NATIVE_MODE;
				break;

			case AJBnet::CLI_MODE:
				$this->_Mode = AJBnet::CLI_MODE;
				break;

			default:
				throw new AJBnetException("Unknown Access Mode!");
				break;

		endswitch;
	}

	/**
	 * LoadConfig
	 * 
	 * Load the configuration XML.  This contains
	 * local settings and settings needed for
	 * the program to run, including database
	 * configuration and filesystem information.
	 * 
	 * @param string target - file to load
	 */
	public function LoadConfig($target) {

		$ConfigLog = "";

		$xml = $this->Read($target);
		$xml = preg_replace("/>\s+</", "><", $xml);

		$dom = new DomDocument("1.0");
		$dom->LoadXML($xml);

		$element = $dom->documentElement; // <config/>

		foreach ($element->childNodes as $node) {
			switch($node->getAttribute("type")):
				default:
					$this->Config[$node->nodeName] = $node->nodeValue;
				break;
				case "int":
					$this->Config[$node->nodeName] = (int) $node->nodeValue;
				break;
				case "bool":
					$this->Config[$node->nodeName] = (strtolower($node->nodeValue)!=="true") ? false : true;
				break;
			endswitch;
		}

		// Format to default to if not specified in request
		$this->TargetFormat = strtolower( $this->Config['default-format'] );

	}

	/**
	 * GetConfig - pass through private config vars
	 */
	public function GetConfig($key) {
		if (isset($this->Config[$key]))
			return $this->Config[$key];
		else
			return null;
			// throw new ApplicationException("That configuration parameter does not exist.");
	}

	/**
	 * Automatically load AJBnet and 3rd party classes (Module).
	 * 
	 * @param array $exclude Files to exclude from load.
	 */
	public function LoadModules($exclude=array()) {
		$this->LoadClassDirectory($this->Config['mod-dir']);
	}

	/**
	 * Automatically load the available AJBnet classes (Library).
	 * 
	 * @param array $exclude Files to exclude from load.
	 */
	public function LoadLibrary($exclude=array()) {
		return false;

		$this->LoadClassDirectory($this->Config['lib-dir']);
	}

	/**
	 * Automatically load all PHP files in a directory.
	 * 
	 * @param string $cdir
	 * @deprecated
	 * @todo Remove this!  Is handled by the autoloader
	 */
	private function LoadClassDirectory($cdir) {
		return false;
	
		$dir = opendir(getcwd() . "/" . $cdir);
		$include = array();
	    while (false !== ($file = readdir($dir))) {
			if (!in_array($file,$this->Exclude)) {
				require_once($cdir.$file);
				$this->Loaded[] = $file;
			}
	    }
	}

	/**
	 * Read a file off the filesystem, and return it's contents.
	 * 
	 * @param string $filename
	 */
	private function Read($filename) {
		$handle = fopen($filename, 'r');
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		return $contents;
	}

	/**
	 * Write
	 * 
	 * @param string $filename
	 * @param string $contents
	 * @param string $mode
	 * 
	 */
	private function Write($filename,$contents,$mode="w") {
		if (!in_array($mode,array("w","w+","a","a+","x","x+")))
			throw new ApplicationException("Invalid mode for fopen!");
		$handle = fopen($filename, $mode);
		$result = fwrite($handle,$contents);
		fclose($handle);
		return $result;
	}

	/**
	 * Write some random string either to the default log, or custom log with another prefix.
	 * 
	 * @return bool
	 */
	public function WriteLog($entry="",$prefix = "Log") {
		$filename = date("Y-m-d")."_log";
		// prefix isn't used right now, but useful someday
		return $this->Write("storage/log/{$filename}",$entry."\n","a");
	}

	// Installer code could really be moved to a separate installer class 
	
	/**
	 * See if we can install AJBnet 
	 * 
	 * @return array
	 */
	public function SelfTest() {

		$errors = array();
		$warnings = array();
		
		// Give these error and warning numbers
		// So we can reference them with patches and documentation
		// For autofix or additional help
		//
		// We love our clients, really.  We LOVE them.

		if (!is_writable("./"))
			$errors[] = "I can't write to install directory.";

		if (!is_writable("./cache"))
			$errors[] = "I can't write to public cache.";

		if (!is_writable("./storage"))
			$errors[] = "I can't write to internal storage.";

		if (!is_file(".htaccess"))
			$warnings[] = "Missing .htaccess to enable normal API mode operation.";

		return array("errors"=>$errors,"warnings"=>$warnings);

	}

	/**
	 * Install needed config files for full operation.
	 * 
	 * @param array $options
	 * @return null
	 */
	public function Install($options = array()) {
	
		// option here would be used to set vars in config and .htaccess
		
		if (is_file(".htaccess"))
			throw new AJBnetException(".htaccess exists!");

		$source = $this->Read("storage/htaccess/htaccess.sample");
		$base = $_SERVER['SCRIPT_URL'];
		$source = str_replace("{REWRITE_BASE}",$base,$source);
		if (!$this->Write(".htaccess",$source))
			throw new AJBnetException("Couldn't write .htaccess!");

	}

	/**
	 * Get Path wrapper - this is here to optionally create
	 * a path if we ever do another "method" .. eg:
	 * split on & instead of / of
	 * build from separate get params (Grp=x Act=y etc).
	 * 
	 * @return string
	 */
	private function _GetPath() {

		switch ($this->_AccessMethod):

			default:
				throw new ApplicationException("Unknown Access Method!");
				break;
				
			case AJBnet::PATH_AS_DIRECTORIES:
				return $_SERVER['REQUEST_URI'];
				break;

			case AJBnet::PATH_AS_FIRSTPARAM:
				if (is_array($_GET) && count($_GET) > 0)
					return array_shift($_GET);
				return ""; 
				break;

			// FirstKey mode will not support Format.id - this should not be used
			case AJBnet::PATH_AS_FIRSTKEY:
				if (is_array($_GET) && count($_GET) > 0)
					return urldecode(array_shift(array_keys($_GET)));
				return "";
				break;

		endswitch;

	}

	/**
	 * Determines what will be called from RPC execution path.
	 * 
	 * @param string $PathOverride
	 * @param null
	 */
	private function _ParsePath($PathOverride=null) {

		/*
		
			Cases that _should_ be supported here:
		
			/class/function/ID.format
			/class/function/path/
			/class/function/path/ID
			/class/function/path/ID.format
			/class/function/path/.format
			/class/function/.format

			Why are we doing GET in such a crazy way (no real params?)
			Well, this way we can cache anything as a normal file, and avoid PHP
			and MySQL access entirely.  Think about the load!!!! Think about it >:)

		*/

		$dir = $this->Config['install-dir'];

		if ($this->_Mode === AJBnet::API_MODE)
			$path = $this->_GetPath();
		else
			$path = $PathOverride;
			
		// remove query string
		$real_path = explode("?",$path);

		// remove base dir
		if (!empty($dir))
			$real_path = explode($dir,$real_path[0]);

		if (count($real_path) > 1)
			$real_path = $real_path[1];
		else
			$real_path = $real_path[0];

		// Trim an trailing slash
		if (substr($real_path,-1) === "/")
			$real_path = substr($real_path,0,strlen($real_path)-1);

		$opt = explode("/",$real_path);

		$this->Path = $opt;

		// SANITIZE THESE WITH REGEX!!

		$this->TargetClass = array_shift($opt);

		if (!isset($opt) || empty($opt))
			$this->TargetFunction = "Run";
		else
			$this->TargetFunction = array_shift($opt);

		// This can be improved! ... and here's how:
			
		if (count($opt) >= 1) {
			
			// "format" is determined by the string from the last .
			// eg:
			
			// /path/path/path/id.id.id.format
			
			// find the last occurance of . in the last token
			// from the / split, and use that
			// sounds like a job for substr
			
			$tmp = array_pop($opt);
			$tmp = explode(".",$tmp);
			$this->TargetID = $tmp[0];
			$this->TargetFormat = $tmp[1];
		}

	}

	/**
	 * Output in the desired format.  Send HTTP headers.
	 * 
	 * @param mixed $data
	 * @param integer $status Status code to send.
	 * @param array $messages Messages to include in package.
	 */
	private function _Output($data,$status=200,$messages=array()) {

		// Maybe put exceptions in an array in AJBnet ?
		if ($this->_Mode !== AJBnet::NATIVE_MODE)
			return $data;

		$Vom = new DataOutput($this->GetConfig("send-httpstatus"));
		$Vom->SetData($data);
		$Vom->SetMessages($messages);
		$Vom->AddMessage("Executed in " . $this->Timer_GetTotalDuration() . " seconds.");
		// $Vom -> SetErrors ?
		$Vom->SetFormat($this->TargetFormat);
		$Vom->Output();
	}

	/**
	 * Output an exception in the desired format.  Send HTTP headers.
	 * 
	 * @param exception $e
	 * @param integer $status Status code to send.
	 */	
	private function _OutputException($e,$status=null) {

		// This needs to be handled better (returning false is not enough info).
		// Maybe put exceptions in an array in AJBnet ?

		if ($this->_Mode !== AJBnet::API_MODE)
			return false;

		$Vom = new DataOutput($this->GetConfig("send-httpstatus"));

		if (null === $status)
			$status = $e->GetCode() != 0 ? $e->GetCode() : 500;

		$Vom->SetStatus($status);

		$Vom->AddException($e->GetMessage());
		$Vom->SetFormat($this->TargetFormat);
		$Vom->Output();
	}

	/**
	 * Add a labeled mark with microseconds.
	 * 
	 * @param string $Label Label for marker.
	 * @return float Time of mark.
	 */
	private function Timer_SetMark($Label = "") {
		$Marker = array(
			0 => microtime(),
			1 => $Label
		);
		$this->Timer_Markers[] = $Marker;
		return $Marker[0];
	}

	/**
	 * Get total duration (so far) from our marks
	 * 
	 * @return float
	 */
	private function Timer_GetTotalDuration() {
		if (count($this->Timer_Markers) == 0)
			throw new ApplicationException("Can't determine start time!");
		else if (count($this->Timer_Markers) == 1)
			$this->Timer_SetMark("Get Duration");
		return $this->Timer_Markers[count($this->Timer_Markers) - 1][0] - $this->Timer_Markers[0][0];
	}

} // end AJBnet
