<?php

define('AJBNET_DIR', realpath(dirname(__FILE__)));

/**
 * If we don't have an SplClassLoader autoloader class
 * already defined elsewhere, create one which will be used
 * as our autoloader internally.
 */
if (!class_exists('SplClassLoader')) {
	include(AJBNET_DIR . '/loader.php');
}

$AJBnetLoader = new SplClassLoader('AJBnet', AJBNET_DIR);
$AJBnetLoader->register();
