<?php

/**
 * AJBnet basic template
 */
namespace AJBnet\Core;
// use AJBnet\Core\Exceptions;

class Template {

	// use Traits\MutableObject;

	protected array $templateData = ['global'=>[]];
	protected string $templateDirectory = '';
	protected string $layoutDirectory = 'layouts';
	protected string $partialsDirectory = 'partials';
	protected string $layout = 'index';

	public function __construct() {
		$this->setData('generated', time());
	}

	public function registerTemplateDirectory(string $directory): void {

		$resolved = realpath($directory);

		if (false === $resolved) {
			throw new Exceptions\FilesystemException("Template directory '{$directory}' does not exist");
		}

		$this->templateDirectory = $resolved;
	}

	public function autoloadTemplate(?string $path = null): string|bool {

		if (is_null($path)) {
			$path = $_SERVER['REQUEST_URI'];
		}

		if (substr($path,-1) == '/') {
			$path .= 'index';
		}

		if (!$this->templateExists($path)) {
			return false;
		}

		return $this->loadTemplateWithLayout($path);

	}

	/**
	 *
	 */
	public function loadTemplateWithLayout(string $path): string {
		$templateData = $this->loadTemplate($path);
		$this->setData('page_data', $templateData);
		return $this->loadLayout();
	}

	protected function loadLayout(): string {

		$templateFile = "{$this->templateDirectory}/{$this->layoutDirectory}/{$this->layout}.php";

		if (!is_file($templateFile)) {
			throw new Exceptions\FilesystemException("Cannot locate layout '{$this->layout}'");
		}

		return $this->load($templateFile);
	}

	protected function loadTemplate(string $template, ?array $dataset = null): string {

		$templateFile = "{$this->templateDirectory}/{$template}.php";

		if (!is_file($templateFile)) {
			throw new Exceptions\FilesystemException("Cannot locate template '{$template}'");
		}

		return $this->load($templateFile, true, $dataset ?? []);
	}

	/**
	 * Renders a partial from $partialsDirectory with its own local variables.
	 */
	public function loadPartial(string $partial, array $dataset = []): string {

		$templateFile = "{$this->templateDirectory}/{$this->partialsDirectory}/{$partial}.php";

		if (!is_file($templateFile)) {
			throw new Exceptions\FilesystemException("Cannot locate partial '{$partial}'");
		}

		return $this->load($templateFile, true, $dataset);
	}

	public function partialExists(string $partial): bool {
		return is_file("{$this->templateDirectory}/{$this->partialsDirectory}/{$partial}.php");
	}

	protected function templateExists(string $template): bool {
		$templateFile = "{$this->templateDirectory}/{$template}.php";
		return is_file($templateFile);
	}

	protected function load(string $templateFile, bool $withData = true, array $dataset = []): string {

		$bufferLevel = ob_get_level();

		if (true === $withData) {
			extract($this->getAllData());
		}

		if ([] !== $dataset) {
			extract($dataset);
		}

		ob_start();

		try {
			require($templateFile);
			return (string)ob_get_clean();
		} catch (\Throwable $error) {
			while (ob_get_level() > $bufferLevel) {
				ob_end_clean();
			}
			throw $error;
		}
	}

	public function setData(string $k, mixed $v, string $set = 'global'): void {
		if (!isset($this->templateData[$set]) || !is_array($this->templateData[$set])) {
			$this->templateData[$set] = [];
		}
		$this->templateData[$set][$k] = $v;
	}

	protected function getData(string $k, string $set = 'global'): mixed {
		return isset($this->templateData[$set][$k]) ? $this->templateData[$set][$k] : null;
	}

	protected function getAllData(?string $set = null): array {
		if (!is_null($set)) {
			return array_merge($this->templateData[$set], $this->templateData['global']);
		} else {
			return $this->templateData['global'];
		}
	}

}
