<?php

/**
 * ImmutableObject trait allows a class to accept
 * setting arbitrary class properties once, but prevents
 * modification after initial assignment using the __set and __get magic methods.
 */
namespace AJBnet\Core\Traits;

trait ImmutableObject {

	protected array $__data = [];
	protected array $__locked_keys = [];

	public function __set(string $k, mixed $v): void {
		if (in_array($k, $this->__locked_keys, true)) {
			throw new \InvalidArgumentException("Property '{$k}' is immutable and cannot be modified");
		}
		$this->__data[$k] = $v;
		$this->__locked_keys[] = $k;
	}

	public function __get(string $k): mixed {
		return isset($this->__data[$k]) ? $this->__data[$k] : null;
	}

}