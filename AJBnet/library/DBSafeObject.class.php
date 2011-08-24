<?php

/**
 * DBSafeObject
 * 
 * Concept here is to create a new generic object wrap
 * that is intended to be used with SQL ... so values are automatically
 * escaped, using PHP magic methods and the class can be serialized for
 * usage w/ MySQL inserts or updates easily.  This is now used as a member
 * of the core AJBnet_DAO class.
 * 
 * @todo Possibly impelemnt MySQLi access to use 'real escape string'
 * 
 * @package AJBnet
 * @subpackage Library
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 2009
 */
class DBSafeObject {

	/**
	 * @var array Central storage of data in the object.
	 */
	private $Data = array();

	/*
	 * Options for SQL Pairs creation:
	 * AllowNULL - String "NULL" becomes `Key` = NULL
	 * AllowNOW - String "NOW()" becomes `Key` = NOW
	 * AllowNumeric - Integer 123 becomes `Key` = 123
	 *
	 */

	private $TableName    = NULL;
	private $AllowNULL    = FALSE;
	private $AllowNOW     = FALSE;
	private $AllowNumeric = FALSE;

	/**
	 * @param array $options Accepts an array of options, bools AllowNULL, AllowNOW, AllowNumeric and string Table for forced table name to be appended to generated SQL.
	 */
	public function __construct($options = array()) {

		foreach($options as $k => $v) {

			switch($k):
				default:
					break;
				case "Table":
					$this->TableName = $v;
					break;
				case "AllowNULL":
					$this->AllowNULL = (bool) $v;
					break;
				case "AllowNOW":
					$this->AllowNOW = (bool) $v;
					break;
				case "AllowNumeric":
					$this->AllowNumeric = (bool) $v;
					break;
			endswitch;

		}
	}

	/**
	 * Return a string of SQL pairs ready for an insert or update statement.
	 * 
	 * @param string $Glue An additional separator to insert between the pairs, eg newline + tag.
	 * @return string Nicely escaped SQL should be generated.
	 */
	public function GetSQLPairs($Glue = null) {

		// In the future it might be great to offer the same output but as a statement w/ bind params:
		// eg:  GetSQL() + GetBindParams()

		if ($this->TableName)
			$Table = $this->TableName;

		if (null !== $Table)
			$Table .= ".";
		$SQL = array();
		foreach($this->Data as $k => $v) {

			if (is_numeric($v) && $this->AllowNumeric === TRUE)
				$SQL[] = "`{$Table}{$k}` = {$v}";
			else if (($v === NULL || $v === "NULL")&& $this->AllowNULL === TRUE)
				$SQL[] = "`{$Table}{$k}` = NULL";
			else if ($v === "NOW()" && $this->AllowNOW === TRUE)
				$SQL[] = "`{$Table}{$k}` = NOW()";
			else
				$SQL[] = "`{$Table}{$k}` = '{$v}'";

		}
		$SQL = implode(", {$Glue}",$SQL);
		return "".$SQL;

	}

	/**
	 * Magic method to get a previously set value.
	 * 
	 * @param string $key
	 * @return mixed
	 * @internal
	 */
	public function __get($key) {
        if (array_key_exists($key, $this->Data))
            return $this->Data[$key];

		/*
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);
		*/

        return null;
	}

	/**
	 * Magic method to set an arbitrary value.
	 * 
	 * @param string $key
	 * @param string $val
	 * @internal
	 */
	public function __set($key, $val) {
		// Sanitize here -- currently just a test to make sure this is possible
		$this->Data[$key] = $val;
	}

	/**
	 * Magic method to test if a particular key is present.
	 * 
	 * @param string $key
	 * @return bool
	 * @internal
	 */
	public function __isset($key) {
		return array_key_exists($key, $this->Data);
	}

	/**
	 * Magic method to test unset a particular key if present.
	 * 
	 * @param string $key
	 */
    public function __unset($key) {
		if (array_key_exists($key, $this->Data))
			unset($this->Data[$key]);
    }

}

?>