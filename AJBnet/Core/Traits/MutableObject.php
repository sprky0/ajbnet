<?php

/**
 * MutableObject trait allows a class to accept
 * setting and getting arbitrary class properties
 * using the __set and __get magic methods.
 */
namespace AJBnet\Core\Traits;

trait MutableObject {

	protected $__data = [];

	public function __set($k,$v) {
		$this->__data[$k] = $v;
	}

	public function __get($k) {
		return isset($this->__data[$k]) ? $this->__data[$k] : null;
	}

}
