<?php

/**
 * Test
 * 
 * Class for testing core functionality of the CMS.
 * Not really intended for any other use.
 * 
 * @package AJBnet
 * @subpackage Library
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 2009
 */
class Test extends AJBnet_RPC {

	public function Run() {

		$Information = array();
		$Information["ClassName"] = $this->GetClass();

		if ($this->GetID())
			$Information["TargetID"] = $this->GetID();

//		 if ($this->GetFormat())
//			$Information["TargetFormat"] = $this->GetFormat();

		return $Information;
	}

	public function TestAccessLevel() {
		$this->RequireLogin(10000);
	}

	public function TestLogin() {
		$this->RequireLogin();
	}

}

?>