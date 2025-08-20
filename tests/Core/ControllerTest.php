<?php

require_once __DIR__ . '/../../bootstrap.php';

use AJBnet\Core\Controller;

class ControllerTest {

	public function testConstructor(): void {
		$controller = new Controller();
		// Test that constructor initializes routes array
		$routes = $this->getRoutes($controller);
		assert(is_array($routes), 'Routes should be an array');
		assert(empty($routes), 'Routes should be empty initially');
	}

	public function testRegisterRoute(): void {
		$controller = new Controller();
		$testAction = function() { return 'test response'; };
		
		$controller->register('/test', $testAction);
		
		$routes = $this->getRoutes($controller);
		assert(count($routes) === 1, 'Should have one route registered');
		assert($routes[0]['route'] === '/test', 'Route should be stored correctly');
		assert($routes[0]['action'] === $testAction, 'Action should be stored correctly');
	}

	public function testRegisterDuplicateRoute(): void {
		$controller = new Controller();
		$testAction1 = function() { return 'test response 1'; };
		$testAction2 = function() { return 'test response 2'; };
		
		$controller->register('/test', $testAction1);
		
		try {
			$controller->register('/test', $testAction2);
			assert(false, 'Should have thrown an exception for duplicate route');
		} catch (AJBnet\Core\Exceptions\ApplicationException $e) {
			assert(strpos($e->getMessage(), 'already registered') !== false, 'Exception should mention route already registered');
		}
	}

	public function testRouteExists(): void {
		$controller = new Controller();
		$testAction = function() { return 'test response'; };
		
		// Use reflection to test routeExists method
		$reflection = new ReflectionClass($controller);
		$method = $reflection->getMethod('routeExists');
		$method->setAccessible(true);
		
		assert($method->invoke($controller, '/test') === false, 'Route should not exist initially');
		
		$controller->register('/test', $testAction);
		
		assert($method->invoke($controller, '/test') === true, 'Route should exist after registration');
	}

	public function testResolveRoute(): void {
		$controller = new Controller();
		$testAction = function() { return 'test response'; };
		
		$controller->register('/test', $testAction);
		
		// Use reflection to test resolveRoute method
		$reflection = new ReflectionClass($controller);
		$method = $reflection->getMethod('resolveRoute');
		$method->setAccessible(true);
		
		$route = $method->invoke($controller, '/test');
		assert($route !== false, 'Route should be resolved');
		assert($route['route'] === '/test', 'Resolved route should match');
		assert($route['action'] === $testAction, 'Resolved action should match');
		
		$notFound = $method->invoke($controller, '/nonexistent');
		assert($notFound === false, 'Non-existent route should return false');
	}

	public function testMultipleRoutes(): void {
		$controller = new Controller();
		$action1 = function() { return 'response 1'; };
		$action2 = function() { return 'response 2'; };
		$action3 = function() { return 'response 3'; };
		
		$controller->register('/route1', $action1);
		$controller->register('/route2', $action2);
		$controller->register('/route3', $action3);
		
		$routes = $this->getRoutes($controller);
		assert(count($routes) === 3, 'Should have three routes registered');
		
		// Use reflection to test resolveRoute method
		$reflection = new ReflectionClass($controller);
		$method = $reflection->getMethod('resolveRoute');
		$method->setAccessible(true);
		
		$route1 = $method->invoke($controller, '/route1');
		assert($route1['action'] === $action1, 'First route should resolve correctly');
		
		$route2 = $method->invoke($controller, '/route2');
		assert($route2['action'] === $action2, 'Second route should resolve correctly');
		
		$route3 = $method->invoke($controller, '/route3');
		assert($route3['action'] === $action3, 'Third route should resolve correctly');
	}

	private function getRoutes(Controller $controller): array {
		$reflection = new ReflectionClass($controller);
		$property = $reflection->getProperty('routes');
		$property->setAccessible(true);
		return $property->getValue($controller);
	}

	public static function runTests(): void {
		$test = new self();
		
		echo "Running Controller tests...\n";
		
		$test->testConstructor();
		echo "✓ Constructor works correctly\n";
		
		$test->testRegisterRoute();
		echo "✓ Register route works correctly\n";
		
		$test->testRegisterDuplicateRoute();
		echo "✓ Register duplicate route throws exception\n";
		
		$test->testRouteExists();
		echo "✓ Route exists check works correctly\n";
		
		$test->testResolveRoute();
		echo "✓ Resolve route works correctly\n";
		
		$test->testMultipleRoutes();
		echo "✓ Multiple routes work correctly\n";
		
		echo "All Controller tests passed!\n\n";
	}
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
	ControllerTest::runTests();
}