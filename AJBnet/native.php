<?php

/**
 * AJBnet CMS - Native (Factory)
 * 
 * This script instantiates the Controller, and
 * leaves the Controller available for "Native" use.
 * 
 * @author Avery Brooks <avery@ajbnet.com>
 * @package AJBnet
 */
try {

	include('core/AJBnet.loader.inc');

	$AJBnet = new AJBnet();

	// Start in NATIVE mode
	$AJBnet->SetMode(AJBnet::NATIVE_MODE);

} catch (exception $e) {

	echo "CRITICAL ERROR.\n";
	echo $e->GetMessage();

}
