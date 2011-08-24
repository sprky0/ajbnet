<?php

/**
 * AJBnet CMS Loader
 * 
 * This script explicitly loads the core classes needed to start automatic loading (for config,
 * libraries and modules).  If these classes cannot be found, then the system will not be able to start.
 * It also defines a base autoloader, for use with AJBnet library and modules.
 * 
 * Specified in order of dependency.
 * 
 * @package AJBnet
 * @subpackage Core
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 2011
 */

define( AJBNET_INSTALL_DIRECTORY, dirname(dirname(__FILE__)."../") . "/" );

$_Libs = array(
	array('Exceptions','package'),
	array('AJBnet','class'),
	array('AJBnet_RPC','class'),
	array('AJBnet_DAO','class')
);

$_Lib = "";

for($a=0;$a<count($_Libs);$a++) {
	$_curName = $_Libs[$a][0];
	$_curType = $_Libs[$a][1];
	$_curLib = AJBNET_INSTALL_DIRECTORY . "core/{$_curName}.{$_curType}.php";
	if (!is_file($_curLib))
		throw new Exception("Missing {$_curName} {$_curType}.");
	include($_curLib);
}

/**
 * Prepare base autoloader
 */
function __autoload($class_name) {

	// Class is found in the library directory!

	if (is_file( AJBNET_INSTALL_DIRECTORY . "library/{$class_name}.class.php")) {
		include( AJBNET_INSTALL_DIRECTORY."library/{$class_name}.class.php" );
	}

	// Class is found in the module directory

	else if (is_file( AJBNET_INSTALL_DIRECTORY . "module/{$class_name}.class.php")) {
		include( AJBNET_INSTALL_DIRECTORY."module/{$class_name}.class.php" );
		return;
	}


	
}
