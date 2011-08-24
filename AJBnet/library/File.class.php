<?php

/**
 * File is a DAO that knows how to operate on the File database, handle uploads via Storage.
 * 
 * @package AJBnet
 * @subpackage Library
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 2009
 */
class File extends AJBnet_DAO {

	// General File types
	
	const IMAGE = 001;
	const VIDEO = 002;
	const AUDIO = 003;
	const TEXT = 004;
	const ARCHIVE = 005;
	const UNKNOWN = 006;
	const THUMBNAIL = 007; // sub type

	// Array relating class constants to human readable text.

	protected $ReadableTypes = array(
		File::IMAGE => "image",
		File::VIDEO => "video",
		File::TEXT => "text",
		File::ARCHIVE => "archive",
		File::UNKNOWN => "unknown type",
		File::THUMBNAIL => "thumbnail"
	);
	
	protected $Table = "File";
	protected $Tables = array(
	"
		CREATE TABLE  `File` (
			`FileID` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
			`R_FileID` INT( 10 ) UNSIGNED NULL,
			`R_UserID` INT( 10 ) UNSIGNED NULL,
			`FileName` VARCHAR( 255 ) NOT NULL,
			`DisplayName` VARCHAR( 255 ) NULL,
			`MimeType` VARCHAR( 50 ) NULL,
			`Notes` TEXT NULL,
			`Public` TINYINT ( 1 ) UNSIGNED NOT NULL DEFAULT 1,
			`Created` DATETIME NOT NULL,
			`Updated` DATETIME NOT NULL,
			PRIMARY KEY ( `UserID` )
		) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci
	"
	);

	// Primary Key
	private $FileID     = 0;
	private $FileName   = "";

	// Possible source file ?
	private $R_FileID   = 0;

	// Storage Area Handler
	private $Storage;
	
	/**
	 * @param AJBnet $AJBnet Reference to the controller.
	 */
	public function __construct($AJBnet = null) {
		parent::__construct(&$AJBnet);
	}

	
	public function Upload() {
		var_dump($_FILE);
		exit();
	}

}
