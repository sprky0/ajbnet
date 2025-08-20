<?php

require_once __DIR__ . '/../../../bootstrap.php';

use AJBnet\Core\Traits\ImmutableObject;

class ImmutableObjectTest {

	use ImmutableObject;

	public function testCanSetProperty(): void {
		$this->testProperty = 'test value';
		assert($this->testProperty === 'test value', 'Property should be set correctly');
	}

	public function testCannotModifyProperty(): void {
		$this->testProperty = 'initial value';
		
		try {
			$this->testProperty = 'new value';
			assert(false, 'Should have thrown an exception');
		} catch (InvalidArgumentException $e) {
			assert(strpos($e->getMessage(), 'immutable') !== false, 'Exception should mention immutability');
		}
		
		assert($this->testProperty === 'initial value', 'Property should remain unchanged');
	}

	public function testGetNonExistentProperty(): void {
		$result = $this->nonExistentProperty;
		assert($result === null, 'Non-existent property should return null');
	}

	public function testMultipleProperties(): void {
		$this->prop1 = 'value1';
		$this->prop2 = 'value2';
		
		assert($this->prop1 === 'value1', 'First property should be set correctly');
		assert($this->prop2 === 'value2', 'Second property should be set correctly');
		
		try {
			$this->prop1 = 'new value';
			assert(false, 'Should have thrown an exception for prop1');
		} catch (InvalidArgumentException $e) {
			// Expected
		}
		
		try {
			$this->prop2 = 'new value';
			assert(false, 'Should have thrown an exception for prop2');
		} catch (InvalidArgumentException $e) {
			// Expected
		}
	}

	public static function runTests(): void {
		$test = new self();
		
		echo "Running ImmutableObject tests...\n";
		
		$test->testCanSetProperty();
		echo "✓ Can set property\n";
		
		$test = new self(); // Reset for next test
		$test->testCannotModifyProperty();
		echo "✓ Cannot modify property\n";
		
		$test = new self(); // Reset for next test
		$test->testGetNonExistentProperty();
		echo "✓ Get non-existent property returns null\n";
		
		$test = new self(); // Reset for next test
		$test->testMultipleProperties();
		echo "✓ Multiple properties work correctly\n";
		
		echo "All ImmutableObject tests passed!\n\n";
	}
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
	ImmutableObjectTest::runTests();
}