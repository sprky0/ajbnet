<?php

require_once __DIR__ . '/../../../bootstrap.php';

use AJBnet\Core\Traits\MutableObject;

class MutableObjectTest {

	use MutableObject;

	public function testCanSetProperty(): void {
		$this->testProperty = 'test value';
		assert($this->testProperty === 'test value', 'Property should be set correctly');
	}

	public function testCanModifyProperty(): void {
		$this->testProperty = 'initial value';
		$this->testProperty = 'new value';
		assert($this->testProperty === 'new value', 'Property should be modified correctly');
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
		
		$this->prop1 = 'new value1';
		$this->prop2 = 'new value2';
		
		assert($this->prop1 === 'new value1', 'First property should be modified correctly');
		assert($this->prop2 === 'new value2', 'Second property should be modified correctly');
	}

	public function testDifferentDataTypes(): void {
		$this->stringProp = 'string';
		$this->intProp = 42;
		$this->arrayProp = ['a', 'b', 'c'];
		$this->boolProp = true;
		
		assert($this->stringProp === 'string', 'String property should work');
		assert($this->intProp === 42, 'Integer property should work');
		assert($this->arrayProp === ['a', 'b', 'c'], 'Array property should work');
		assert($this->boolProp === true, 'Boolean property should work');
	}

	public static function runTests(): void {
		$test = new self();
		
		echo "Running MutableObject tests...\n";
		
		$test->testCanSetProperty();
		echo "✓ Can set property\n";
		
		$test = new self(); // Reset for next test
		$test->testCanModifyProperty();
		echo "✓ Can modify property\n";
		
		$test = new self(); // Reset for next test
		$test->testGetNonExistentProperty();
		echo "✓ Get non-existent property returns null\n";
		
		$test = new self(); // Reset for next test
		$test->testMultipleProperties();
		echo "✓ Multiple properties work correctly\n";
		
		$test = new self(); // Reset for next test
		$test->testDifferentDataTypes();
		echo "✓ Different data types work correctly\n";
		
		echo "All MutableObject tests passed!\n\n";
	}
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
	MutableObjectTest::runTests();
}