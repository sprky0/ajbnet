<?php

 /**
  * User DAO
  * 
  * Handles permissions, login logout etc.
  * 
  * @package AJBnet
  * @subpackage Library
  * @author Avery Brooks <avery@ajbnet.com>
  * @copyright 8/2009 
  */
class User extends AJBnet_DAO {

	protected $Table = "User";
	protected $Tables = array(
	"
		CREATE TABLE  `User` (
			`UserID` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
			`R_GroupID` INT( 10 ) UNSIGNED NULL,
			`FirstName` VARCHAR( 50 ) NULL,
			`LastName` VARCHAR( 50 ) NULL,
			`UserName` VARCHAR( 50 ) NOT NULL,
			`EmailAddress` VARCHAR( 255 ) NULL,
			`Password` VARCHAR( 50 ) NOT NULL,
			`Access` INT ( 3 ) UNSIGNED NOT NULL DEFAULT 1,
			`Created` DATETIME NOT NULL,
			`Updated` DATETIME NOT NULL,
			PRIMARY KEY ( `UserID` )
		) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci
	"
	);

	private $UserID     = 0;
	// private $GroupID    = 0; // group
	private $UserAccess = 0;
	private $UserName   = "";

	/**
	 * @param AJBnet $AJBnet Reference to the controller.
	 */
	public function __construct($AJBnet = null) {
		parent::__construct(&$AJBnet);
		// If we already have a session + user login established, use it.
		$this->_RefreshLogin();
	}

	/**
	 * DANGER WILL ROBINSON!
	 * THIS IS JUST FOR TESTING AND MUST BE HACKED OUT BEFORE PRODUCTION USE!
	 * 
	 * @internal
	 * @return Array
	 */
	public function Refresh() {
		
		$this->Query("DROP TABLE `User`;");
		$this->Install();
		$this->Insert(
			array(
				"FirstName" => "John",
				"LastName" => "Doe",
				"UserName" => "john.doe",
				"EmailAddress" => "john@doe.com",
				"Password" => $this->Encode("password"),
				"Access" => 1
			)
		);
		return $this->Select("SELECT * FROM {$this->Table}");

	}

	/**
	 * Authenticate a User.  This should be modified to accept params directly too.
	 * @return boolean
	 */
	public function Login() {
		if (isset($_POST['User']) && isset($_POST['Pass']))
			return $this->_UserAuth($_POST['User'],$_POST['Pass']);
		throw new ApplicationException("Missing required parameters for User::Login",406);
	}

	/**
	 * Log out a User.
	 * 
	 * @return boolean
	 */
	public function Logout() {
		$this->UserID = null;
		$this->UserName = null;
		// kill php sessid cookie too?
		$_SESSION = array();
		session_destroy();
		return true;
	}

	/**
	 * @return integer
	 */
	public function GetUserID() {
		if (!$this->IsLoggedIn())
			throw new SecurityException("Not logged in.",401); // 401 : unauthenticated
		return $this->UserID;
	}

	/**
	 * @return string
	 */
	public function GetUserName() {
		if (!$this->IsLoggedIn())
			throw new SecurityException("Not logged in.",401); // 401 : unauthenticated
		return $this->UserName;
	}

	/**
	 * @return integer
	 */
	public function GetAccess() {
		if (!$this->IsLoggedIn())
			throw new SecurityException("Not logged in.",401); // 401 : unauthenticated
		return $this->UserAccess;
	}

	/**
	 * @return array
	 */
	public function Status() {
		if (!$this->IsLoggedIn())
			throw new SecurityException("Not logged in.",401); // 401 : unauthenticated
		return array("UserID" => $this->UserID,"UserName" => $this->UserName);
	}

	/**
	 * @return boolean
	 */
	public function IsLoggedIn() {
		return (!empty($this->UserName) && $this->UserID > 0);
	}

	/**
	 * The security of this can be improved.
	 * 1 - Improve SQL to limit injection possibilities
	 * 2 - Check into possible session hijacking
	 * 3 - Encrypt password
	 * 4 - Optional hashed version of PW for use by both sides?
	 * ...
	 * 
	 * @internal
	 */
	private function _UserAuth($key,$password,$type="UserName") {

		$key = $this->Escape($key);
		$password = $this->Escape($this->Encode($password));

		$results = $this->SelectOne("SELECT Access, UserName, UserID FROM User WHERE UserName = '{$key}' AND Password = '{$password}' LIMIT 1");

		if (false === $results)
			throw new SecurityException("Login not accepted.",401); // 401 : unauthenticated

		$this->Access = $results['Access'];
		$this->UserID = $results['UserID'];
		$this->UserName = $results['UserName'];

		$this->SetSession("UserAccess",$this->Access);
		$this->SetSession("UserID",$this->UserID);
		$this->SetSession("UserName",$this->UserName);

		$cms_name = $this->AJBnet->GetConfig("name");
		$cms_version = $this->AJBnet->GetConfig("version");

		if (null != $cms_name)
			$this->AddMessage("Welcome to {$cms_name}, powered by AJBnet CMS v{$cms_version}.");
		else
			$this->AddMessage("Welcome to AJBnet v{$cms_version}.");

		$this->AddMessage("Please consider showering, you smell like a lama.");

		return $this->UserID;
	}

	/**
	 * @internal
	 */
	private function _RefreshLogin() {
		$this->Access = $this->GetSession('UserAccess');
		$this->UserID = $this->GetSession('UserID');
		$this->UserName = $this->GetSession('UserName');
	}

} // End of class

?>