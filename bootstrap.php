<?php

define('AJBNET_DIR', realpath(dirname(__FILE__)));

if (!class_exists('SplClassLoader')) {
	include(AJBNET_DIR . '/loader.php');
}

$AJBnetLoader = new SplClassLoader('AJBnet', AJBNET_DIR);
$AJBnetLoader->register();
