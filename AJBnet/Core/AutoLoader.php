<?php

namespace AJBnet\Core;

class AutoLoader extends \SplClassLoader {

	public function __construct($ns = null, $includePath = null) {
		parent::__construct($ns, $includePath);
	}

}
