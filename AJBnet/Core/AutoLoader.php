<?php

namespace AJBnet\Core;

class AutoLoader extends \SplClassLoader {

	public function __construct(?string $ns = null, ?string $includePath = null) {
		parent::__construct($ns, $includePath);
	}

}
