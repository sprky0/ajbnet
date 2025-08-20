<?php

echo "=== AJBnet Test Suite ===\n\n";

// Include all test files
require_once __DIR__ . '/Core/Traits/ImmutableObjectTest.php';
require_once __DIR__ . '/Core/Traits/MutableObjectTest.php';
require_once __DIR__ . '/Core/TemplateTest.php';
require_once __DIR__ . '/Core/ControllerTest.php';

// Run all tests
try {
	ImmutableObjectTest::runTests();
	MutableObjectTest::runTests();
	TemplateTest::runTests();
	ControllerTest::runTests();
	
	echo "=== All tests passed! ===\n";
} catch (Throwable $e) {
	echo "=== Test failed! ===\n";
	echo "Error: " . $e->getMessage() . "\n";
	echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
	exit(1);
}