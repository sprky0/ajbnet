<?php

/**
 * Installation controller for AJBnet CMS
 * 
 * @author Avery Brooks <avery@ajbnet.com>
 * @package AJBnet
 */
try {

	include('core/AJBnet.loader.inc');

	$AJBnet = new AJBnet();
	$AJBnet->SetMode(AJBnet::API_MODE);

	// form interaction
	$action = isset($_POST['action']) ? $_POST['action'] : "test";

	switch ($action):
	
		case "test":

			$Results = $AJBnet->SelfTest();
	
			echo "<h1>Environment Self Test</h1>\n\n";
		
			if (count($Results['errors']) > 0) {
				echo "<h3>Errors Encountered</h3>\n";
				echo "<ul>\n";
				for ($i = 0; $i < count($Results['errors']); $i++)
					echo "\t<li>{$Results['errors'][$i]}</li>\n";
				echo "</ul>\n\n";
			} else {
				echo "<h3>No Errors</h3>\n";
			}
		
			if (count($Results['warnings']) > 0) {
				echo "<h3>Warnings Encountered</h3>\n";
				echo "<ul>\n";
				for ($i = 0; $i < count($Results['warnings']); $i++)
					echo "\t<li>{$Results['warnings'][$i]}</li>\n";
				echo "</ul>\n";
			} else {
				echo "<h3>No Warnings</h3>\n";
			}

			break;
			
		case "config":

			// User needs to be shown an HTML form with all this nonsense on there.  Eg: 
			// mysql info, install directory
			// all this crap, so that they can pick and choose how their garbage gets input
			// also salt for the password encryption etc
			
			break;
	
	endswitch;
	
	
} catch (exception $e) {

	echo "<pre>";
	echo "CRITICAL ERROR.\n";
	echo $e->GetMessage();

}

?>