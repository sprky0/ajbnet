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
		
		assert($directory === realpath($tempDir), 'Template directory should be resolved with realpath');
		
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

	public function testRegisterTemplateDirectoryRejectsMissing(): void {
		$template = new Template();
		$missing = sys_get_temp_dir() . '/ajbnet_absent_' . uniqid();

		$thrown = false;
		try {
			$template->registerTemplateDirectory($missing);
		} catch (\AJBnet\Core\Exceptions\FilesystemException $error) {
			$thrown = true;
		}

		assert($thrown, 'Registering a missing directory should throw FilesystemException');
	}

	public function testLoadTemplateWithDataset(): void {
		$template = new Template();
		$tempDir = sys_get_temp_dir() . '/ajbnet_test_' . uniqid();
		mkdir($tempDir);
		file_put_contents($tempDir . '/greeting.php', '<?php echo $greeting; ?>');

		$template->registerTemplateDirectory($tempDir);

		$reflection = new ReflectionMethod($template, 'loadTemplate');
		$reflection->setAccessible(true);
		$output = $reflection->invoke($template, 'greeting', ['greeting' => 'hello']);

		assert($output === 'hello', 'loadTemplate should expose its dataset to the template');

		unlink($tempDir . '/greeting.php');
		rmdir($tempDir);
	}

	public function testLoadPartial(): void {
		$template = new Template();
		$tempDir = sys_get_temp_dir() . '/ajbnet_test_' . uniqid();
		mkdir($tempDir);
		mkdir($tempDir . '/partials');
		file_put_contents($tempDir . '/partials/item.php', '<?php echo "item:" . $label; ?>');

		$template->registerTemplateDirectory($tempDir);

		assert($template->partialExists('item'), 'partialExists should find the partial');
		assert($template->loadPartial('item', ['label' => 'one']) === 'item:one', 'loadPartial should render with its dataset');

		unlink($tempDir . '/partials/item.php');
		rmdir($tempDir . '/partials');
		rmdir($tempDir);
	}

	public function testLoadUnwindsBufferOnThrow(): void {
		$template = new Template();
		$tempDir = sys_get_temp_dir() . '/ajbnet_test_' . uniqid();
		mkdir($tempDir);
		file_put_contents($tempDir . '/broken.php', '<?php echo "partial output"; throw new RuntimeException("boom"); ?>');

		$template->registerTemplateDirectory($tempDir);

		$before = ob_get_level();
		$thrown = false;

		$reflection = new ReflectionMethod($template, 'loadTemplate');
		$reflection->setAccessible(true);

		try {
			$reflection->invoke($template, 'broken', []);
		} catch (RuntimeException $error) {
			$thrown = true;
		}

		assert($thrown, 'The template exception should propagate');
		assert(ob_get_level() === $before, 'The output buffer should be unwound after a throw');

		unlink($tempDir . '/broken.php');
		rmdir($tempDir);
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

		$test->testRegisterTemplateDirectoryRejectsMissing();
		echo "✓ Missing template directory is rejected\n";

		$test->testLoadTemplateWithDataset();
		echo "✓ loadTemplate passes its dataset through\n";

		$test->testLoadPartial();
		echo "✓ loadPartial renders from the partials directory\n";

		$test->testLoadUnwindsBufferOnThrow();
		echo "✓ Output buffer unwinds when a template throws\n";
		
		echo "All Template tests passed!\n\n";
	}
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
	TemplateTest::runTests();
}