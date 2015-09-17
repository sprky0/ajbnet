<?php

/**
 * AJBnet basic template
 */
namespace AJBnet\Core;
// use AJBnet\Core\Exceptions;

class Template {

	// use Traits\MutableObject;

	protected $templateData = ['global'=>[]];
	protected $templateDirectory = '';
	protected $layoutDirectory = 'layouts';
	protected $partialsDirectory = 'partials';
	protected $layout = 'index';

	public function __contruct() {
		$this->set('generated',time());
	}

	public function registerTemplateDirectory($directory) {
		$this->templateDirectory = realpath($directory);
	}

	public function autoloadTemplate($path = null) {

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
	public function loadTemplateWithLayout($path) {
		$templateData = $this->loadTemplate($path);
		$this->setData('page_data', $templateData);
		return $this->loadLayout();
	}

	protected function loadLayout() {

		$templateFile = "{$this->templateDirectory}/{$this->layoutDirectory}/{$this->layout}.php";

		if (!is_file($templateFile)) {
			throw new Exceptions\FilesystemException("Cannot locate layout '{$this->layout}'");
		}

		return $this->load($templateFile);
	}

	protected function loadTemplate($template, $dataset = null) {
		$templateFile = "{$this->templateDirectory}/{$template}.php";
		if (!is_file($templateFile)) {
			throw new Exceptions\FilesystemException("Cannot locate template '{$template}'");
		}
		return $this->load($templateFile);
	}

	protected function templateExists($template) {
		$templateFile = "{$this->templateDirectory}/{$template}.php";
		return is_file($templateFile);
	}

	protected function load($templateFile, $withData = true) {
		ob_start();
		if (true === $withData) {
			$data = $this->getAllData();
			extract($data);
		}
		require($templateFile);
		$content = ob_get_clean();
		return $content;
	}

	public function setData($k,$v,$set='global') {
		if (!is_array($this->templateData[$set])) {
			$this->templateData[$set] = [];
		}
		$this->templateData[$set][$k] = $v;
	}

	protected function getData($k,$set='global') {
		return isset($this->templateData[$set][$k]) ? $this->templateData[$set][$k] : null;
	}

	protected function getAllData($set=null) {
		if (!is_null($set)) {
			return array_merge($this->templateData[$set], $this->templateData['global']);
		} else {
			return $this->templateData['global'];
		}
	}

}
