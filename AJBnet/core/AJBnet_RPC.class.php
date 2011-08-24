<?php

/**
 * Contains the basic functionality we all need
 * to get along and be happy
 * 
 * Need to write an interface that subclasses
 * of AJBnet_DAO implement -- this will
 * make sure that all the required functions
 * are provided by people writing Modules for
 * AJBnet -- eg: AJBnet_Authorize() etc.
 * 
 * @package AJBnet
 * @subpackage Core
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 2009
 */
class AJBnet_RPC {

	protected $MyClass = "";
	protected $ID = -1;
	protected $Path = array();
	protected $Requires = array();
	protected $Messages = array();

	protected $AJBnet;

	public function __construct($AJBnet = null) {
		$this->MyClass = get_class($this);
		if (isset($AJBnet))
			$this->AJBnet = &$AJBnet;
	}

	/**
	 * Run is the default action for an AJBnet class.
	 * This is a placeholder function just for fun.
	 *
	 * @return string
	 */
	public function Run() {
		return "Hello World!";
	}

	/**
	 * This function gives child classes the ability
	 * to provide custom authorization on a per method
	 * basis.
	 * 
	 * One problem with handling things in this way is
	 * that the constructor is still called even before
	 * we know if we can call the requested function. 
	 * 
	 * An option for solving this is to call this function
	 * inside of construct for API mode?  I believe that
	 * at that point we technically know what function we
	 * will be calling.  Anyway, up for debate.
	 * 
	 * -AJB
	 * 
	 * @param string $function
	 * @return boolean
	 * 
	 */
	public function AJBnet_Authorize($function) {

		// Figure this out! :)
		if (false)
			throw new SecurityException("Action not permitted.",403); // Forbidden

		return true;
	}

	/**
	 * This allows us to externally test specific requirements of this class.
	 * Need to more formally define the formats of these, but it might be good to
	 * test these against confirmed "Loaded" by controller, plus edge cases for
	 * MySQL connect, filesystem access etc.
	 * 
	 * @param string $test
	 * @return boolean
	 */
	public function Requires($test) {
		return (isset($this->Requires[$test]) && $this->Requires[$test] === true);
	}

	/**
	 * Alright, it's been a while.  Offset apparently is the key of the Path array?
	 * 
	 * @param string $offset
	 * @return array|boolean
	 */
	public function GetPath($offset=NULL) {
		if (NULL !== $offset && !isset($this->Path[(int)$offset]))
			return false;
		else if (NULL !== $offset && isset($this->Path[(int)$offset]))
			return $this->Path[$offset];
		else if (!empty($this->Path))
			return $this->Path;
		else
			return false;
	}

	/**
	 * @return string
	 */
	public function GetClass() {
		return $this->MyClass;
	}

	/**
	 * @return integer|boolean
	 */
	public function GetID() {
		if (!empty($this->ID) && $this->ID !== -1)
			return $this->ID;
		else
			return false;
	}

	/**
	 * @return array
	 */	
	public function GetMessages() {
		return $this->Messages;
	}

	/**
	 * @return array
	 */
	public function GetErrors() {
		return $this->Errors;
	}

	/**
	 * @param array
	 */
	public function SetPath($Path = array()) {
		$this->Path = $Path;
	}

	/**
	 * @oaram mixed
	 */
	public function SetID($ID = NULL) {
		// if (!$ID) throw new ApplicationException("Missing ID");  (overkill)
		$this->ID = $ID; // could be integer, MD5 or whatever ... not going to cast here yet
	}

	/**
	 * Set an arbitarary value in the session.
	 * 
	 * @param string $key
	 * @param mixed $val
	 */
	protected function SetSession($key,$val) {
		if (!isset($_SESSION['AJBnet']))
		 	$_SESSION['AJBnet'] = array();
		$_SESSION['AJBnet'][$key] = $val;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	protected function GetSession($key) {
		if (isset($_SESSION['AJBnet'][$key]))
			return $_SESSION['AJBnet'][$key];
		else
			return null;
	}

	/**
	 * Use this in AJBnet member functions to ensure proper user authentication / access level.
	 * 
	 * @param integer $level
	 * @return bool
	 */
	protected function RequireLogin($level = 0) {

		if (empty($this->AJBnet))
			throw new AJBnetException("Can not access Controller!",500);

		if (!$this->AJBnet->User->IsLoggedIn())
			throw new SecurityException("Not logged in!",401);

		if ($level > 0 && $this->AJBnet->User->GetAccess() < $level)
			throw new SecurityException("UserLevel not sufficient!",403);

		return true;
	}

	/**
	 * @param string $message
	 * @return integer Total messages currently stored.
	 */
	protected function AddMessage($message) {
		$this->Messages[] = $message;
		return count($this->Messages);
	}

	/**
	 * Alias to write a log method.
	 * 
	 * @param string $entry
	 */
	protected function WriteLog($entry) {
		// This could be a little better
		$this->AJBnet->WriteLog($this->GetClass() . " -> " . $entry);
	}

}
