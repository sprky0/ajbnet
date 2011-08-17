<?php

/**
 * AJBnet CMS Loader
 * 
 * This script explicitly loads the core classes
 * needed to start automatic loading (for config,
 * libraries and modules).  If these classes cannot
 * be found, then the system will not be able to start.
 * Should be loaded in order of dependency.
 * 
 * @package AJBnet
 * @subpackage Core
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 2009
 */

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
	$_curLib = "core/{$_curName}.{$_curType}.inc"; 
	if (!is_file($_curLib))
		throw new Exception("Missing {$_curName} {$_curType}.");
	include($_curLib);
}

?>