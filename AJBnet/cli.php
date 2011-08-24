<?php

/**
 * AJBnet CMS
 * 
 * This script is intended for use interacting with AJBnet from the commandline
 * 
 * @author Avery Brooks <avery@ajbnet.com>
 * @package AJBnet
 */

try {

	include('core/AJBnet_Loader.script.php');

	$AJBnet = new AJBnet();

	// Start in CLI mode
	$AJBnet->SetMode(AJBnet::CLI_MODE); // this is the default anyway
	$AJBnet->Run();

} catch (exception $e) {

	echo "CRITICAL ERROR.\n";
	echo $e->GetMessage();

}
