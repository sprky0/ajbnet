<?php

/**
 * AJBnet CMS
 * 
 * This script instantiates the Controller and runs the CMS.
 * 
 * @author Avery Brooks <avery@ajbnet.com>
 * @package AJBnet
 */

try {

	include('core/AJBnet.loader.inc');

	// $AJBnet = AJBnet::Construct("AJBnet");
	$AJBnet = new AJBnet();

	// Start in API mode
	$AJBnet->SetMode(AJBnet::API_MODE); // this is the default anyway
	$AJBnet->Run();

} catch (exception $e) {

	echo "CRITICAL ERROR.\n";
	echo $e->GetMessage();

}

?>