<?php

/**
 * 
 * AJBnet DAO
 * 
 * Provides basic database interaction
 * Currently MySQL only, but maybe in the
 * future that will change.
 * 
 * @package AJBnet
 * @subpackage Core
 * @author Avery Brooks
 * @copyright 2009
 */
class AJBnet_DAO {

	/**
	 * @var MySQLi actual MySQLi object for MySQL interaction.
	 */
	protected $MySQL;
	protected $_Last = array();
	
	protected $Requires = array("MySQL"=>true);

	/**
	 * @var string Primary table for DAO.
	 */
	protected $Table = "";
	/**
	 * @var array Table CREATE SQL for DAO.
	 */
	protected $Tables = array();

	/**
	 * @var DBSafeObject data storage for MySQL object.
	 */
	protected $Data;

	/**
	 * @param AJBnet $AJBnet Reference to the Controller.
	 * @return AJBnet_DAO
	 */
	public function __construct($AJBnet = null) {
		parent::__construct(&$AJBnet);

		// DBSafeObject accepts an array of options
		$this->Data = new DBSafeObject(array(
			"AllowNULL" => TRUE,
			"AllowNOW" => TRUE,
			"AllowNumeric" => TRUE
		));

		$this->Connect(
			$this->AJBnet->GetConfig("db-server"),
			$this->AJBnet->GetConfig("db-user"),
			$this->AJBnet->GetConfig("db-pass"),
			$this->AJBnet->GetConfig("db-name")
		);
	}

	/**
	 * Disconnect from the MySQL server
	 */
	public function __destruct() {
		$this->Disconnect();
	}

	/**
	 * Connect to a MySQL server.
	 * 
	 * @param string $server
	 * @param string $user
	 * @param string $pass
	 * @param string $db
	 */
	public function Connect($server,$user,$pass,$db) {

		// See if we'll just refresh ...
		if ($this->_CheckLast($server,$user,$pass,$db))
			return $this->Refresh();

		$this->Disconnect();

		$this->MySQL = new MySQLi($server,$user,$pass,$db);

		// test for errors here, optionally try reconnect
	}

	/**
	 * If there is a MySQL connection, close it.
	 */
	public function Disconnect() {
		if ($this->MySQL)
			$this->MySQL->close();
	}

	protected function _CheckLast($server,$user,$pass,$db) {
		
		if (!isset($this->_Last['server']) || !isset($this->_Last['user']) || !isset($this->_Last['pass']) || !isset($this->_Last['db']))
			return false;

		$this->_Last = array(
			"server" => $server,
			"user" => $user,
			"pass" => $pass,
			"db" => $db
		);

		// $this->Refresh()
		
		return true;

	}

	/**
	 * Installer - temporarily low tech.  Will be replaced by some
	 * sort of middle tier meta language later.  Planned for 1.0, classes
	 * must have an array called "Tables" which contains perfect, beautiful CREATE SQL.
	 *
	 * @return bool 
	 */
	public function Install() {

		if (!is_array($this->Tables) || !(count($this->Tables) > 0))
			throw new DatabaseException($this->GetClass() . " is missing DB Install information!");

		foreach($this->Tables as $Name => $SQL) {
			if (!false) // !exist $Name (need to write this)
				$this->Query($SQL);
			else
				throw new DatabaseException("One or more tables already exist!");
		}

		return true;
	}

	/**
	 * Insert a row into the DB
	 * 
	 * @param array $Values
	 * @param string $Table
	 * @return integer ID of inserted row.
	 */
	protected function Insert($Values,$Table=null) {

		// If we have a default Table, use it.  Otherwise
		// the class may have overriden for a secondary Table it requires
		
		if (null === $Table) $Table = $this->Table;

		$SQL = "INSERT INTO `{$Table}` SET \n\t";

		foreach($Values as $k => $v)
			$this->Data->$k = $v;
		$this->Data->Created = "NOW()";
		$this->Data->Updated = "NOW()";

		$SQL .= $this->Data->GetSQLPairs("\n\t");
		$SQL .= "\n;";

		$this->Query($SQL);

		return $this->MySQL->insert_id;
		// optionally create UUID after insert

	}

	/**
	 * Not complete yet!
	 * 
	 * @todo make WHERE an assoc array
	 * 
	 * @param array $Values
	 * @param string $Where
	 * @param string $Table
	 * @return bool
	 */
	protected function Update($Values,$Where,$Table=null) {

		if (null === $Table) $Table = $this->Table;

		$this->Data->Reset();

		foreach($Values as $k => $v)
			$this->Data->$k = $v;

		$this->Data->Updated = "NOW()";

		$SQL = "UPDATE `{$Table}` ";
		$SQL .= "\n\tSET (\n";
		$SQL .= $this->Data->GetSQLPairs();
		$SQL .= "\n);";

		// For testing purposes now.
		echo $SQL;
		exit();
		
		return $this->Query($SQL);
					
	}

	/**
	 * 
	 * Delete a row from a table.  The collapsing where clause 
	 * here leaves out all other operators besides "=", so that's
	 * not perfect.  We can do better!
	 * 
	 * @param array $Where Key/Val pairs to create a where clause from.
	 * @param string $Table
	 * @param integer $Limit
	 * @return bool
	 */
	protected function Delete($Where=array(),$Table=null,$Limit=1) {
		if (null === $Table)
			$Table = $this->Table;

		$SQL = "DELETE FROM `{$Table}` WHERE ";
		foreach($Where as $k => $v)
			$SQL .= "`$k` = '{$v}'";

		// Currently we will now allow deletion w/o a limit.
		// You can do a preselect test to determine this number
		// if it will be --crazy huge--.

		$Limit = (int) $Limit;
		$SQL .= "\nLIMIT {$Limit};";

		$this->Query($SQL);

		return ($this->AffectedRows() > 0);
	}

	/**
	 * @param string $SQL
	 * @return bool
	 */
	protected function Query($SQL) {
		return $this->MySQL->query($SQL);
	}

	/**
	 * @param string $SQL
	 * @param string $Table Optional table override (other than DAO's primary).
	 * @return array|bool
	 */
	protected function Select($SQL=null,$Table = null) {

		if (null === $Table) $Table = $this->Table;
		if (null === $SQL) 
			return false; // $SQL = "SELECT * FROM `{$Table}` WHERE 1";
		
		$res = $this->Query($SQL);
		$ret = array();
		
		if ($res->num_rows <= 0)
			return false;

		while ($row = $res->fetch_object()) {
			$r = array();
			foreach($row as $k => $v)
				$r[$k] = $v;
			$ret[] = $r;
		}

		$res->MySQLi->free;

		return $ret;
	}

	/**
	 * @param string $SQL
	 * @param string $Table
	 * @return array|bool
	 */
	protected function SelectOne($SQL=null,$Table = null) {
		$res = $this->Select($SQL,$Table);
		if (false !== $res && is_array($res))
			return $res[0];
		else
			return false;
	}

	/**
	 * @return integer
	 */
	protected function AffectedRows() {
		return $this->MySQL->affected_rows;
	}

	/**
	 * Get MySQL error string, if set.
	 * 
	 * @return integer
	 */
	public function GetError() {
		return $this->MySQL->error;
	}

	protected function Escape($string="") {
		return $this->MySQL->escape_string($string);
	}

	/**
	 * Used to apply a non-rersable encoding
	 * 
	 * @param string $str
	 * @return $str  
	 */
	protected function Encode($str) {
		$hash = $this->AJBnet->GetConfig("db-hash");
		if (null === $hash)
			throw new ApplicationException("Missing db-hash config!",500);
		return md5($str.$hash);
	}

}
