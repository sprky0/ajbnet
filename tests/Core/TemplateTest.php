<?php

require_once __DIR__ . '/../../bootstrap.php';

use AJBnet\Core\Template;

class TemplateTest {

	public function testConstructor(): void {
		$template = new Template();
		// Test that constructor sets generated time
		$data = $this->getTemplateData($template);
		assert(isset($data['global']['generated']), 'Generated timestamp should be set');
		assert(is_int($data['global']['generated']), 'Generated timestamp should be an integer');
	}

	public function testSetAndGetData(): void {
		$template = new Template();
		$template->setData('test_key', 'test_value');
		
		$data = $this->getTemplateData($template);
		assert($data['global']['test_key'] === 'test_value', 'Data should be set correctly');
	}

	public function testSetDataWithCustomSet(): void {
		$template = new Template();
		$template->setData('test_key', 'test_value', 'custom');
		
		$data = $this->getTemplateData($template);
		assert($data['custom']['test_key'] === 'test_value', 'Data should be set in custom set');
	}

	public function testRegisterTemplateDirectory(): void {
		$template = new Template();
		
		// Create a temporary directory for testing
		$tempDir = sys_get_temp_dir() . '/ajbnet_test_' . uniqid();
		mkdir($tempDir);
		
		$template->registerTemplateDirectory($tempDir);
		
		// Use reflection to check the directory was set
		$reflection = new ReflectionClass($template);
		$property = $reflection->getProperty('templateDirectory');
		$property->setAccessible(true);
		$directory = $property->getValue($template);
		
		assert($directory === $tempDir, 'Template directory should be set correctly');
		
		// Clean up
		rmdir($tempDir);
	}

	public function testTemplateExists(): void {
		$template = new Template();
		
		// Create a temporary directory with a test template
		$tempDir = sys_get_temp_dir() . '/ajbnet_test_' . uniqid();
		mkdir($tempDir);
		file_put_contents($tempDir . '/test_template.php', '<?php echo "test"; ?>');
		
		$template->registerTemplateDirectory($tempDir);
		
		// Use reflection to test templateExists method
		$reflection = new ReflectionClass($template);
		$method = $reflection->getMethod('templateExists');
		$method->setAccessible(true);
		
		$exists = $method->invoke($template, 'test_template');
		assert($exists === true, 'Template should exist');
		
		$notExists = $method->invoke($template, 'non_existent_template');
		assert($notExists === false, 'Non-existent template should not exist');
		
		// Clean up
		unlink($tempDir . '/test_template.php');
		rmdir($tempDir);
	}

	private function getTemplateData(Template $template): array {
		$reflection = new ReflectionClass($template);
		$property = $reflection->getProperty('templateData');
		$property->setAccessible(true);
		return $property->getValue($template);
	}

	public static function runTests(): void {
		$test = new self();
		
		echo "Running Template tests...\n";
		
		$test->testConstructor();
		echo "✓ Constructor works correctly\n";
		
		$test->testSetAndGetData();
		echo "✓ Set and get data works correctly\n";
		
		$test->testSetDataWithCustomSet();
		echo "✓ Set data with custom set works correctly\n";
		
		$test->testRegisterTemplateDirectory();
		echo "✓ Register template directory works correctly\n";
		
		$test->testTemplateExists();
		echo "✓ Template exists check works correctly\n";
		
		echo "All Template tests passed!\n\n";
	}
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
	TemplateTest::runTests();
}