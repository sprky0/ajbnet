<?php

/**
 * Buttonpusher DAO
 * 
 * ButtonPusher is an iPhone game that lets you hold a button down.
 * 
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 2009
 * @package AJBnet
 * @subpackage Modules
 */
class Buttonpusher extends AJBnet_DAO {

	protected $Table = "Buttonpusher";
	protected $Tables = array("

		CREATE TABLE  `Buttonpusher` (
			`ButtonpusherID` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
			`R_UserID` INT( 10 ) UNSIGNED NOT NULL,
			`Type` INT( 1 ) UNSIGNED NOT NULL DEFAULT  '0',
			`Duration` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0',
			`Created` DATETIME NOT NULL,
			`Updated` DATETIME NOT NULL,
			PRIMARY KEY (  `ScoreID` ),
			INDEX (  `R_UserID` )
		) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci

	");

	public function SaveScore() {
		
		
		
	}


}

?>