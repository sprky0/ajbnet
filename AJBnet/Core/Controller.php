<?php

/**
 * AJBnet basic controller
 */
namespace AJBnet\Core;

class Controller {

	// this sort of thing might be nice to have:
	// static $HOME = '';
	// static $ANY_ALPHA_DIRS = '		// [A-Za-z0-9\/]*'
	// static $TWO_DIRECTORIES = ''

	protected $routes = [];

	public function __contruct() {
	}

	public function register($route,$action) {

		if ($this->routeExists($route)) {
			throw new Exceptions\ApplicationException("Route '{$route}' is already registered.");
		}

		$this->routes[] = [
			'route' => $route,
			'action' => $action
		];
	}

	public function resolve($path = null) {

		if (is_null($path)) {
			$path = $_SERVER['REQUEST_URI'];
		}

		// trim trailing slash
		if (substr($path,-1) == '/') {
			$path = substr($path,0,-1);
		}

		// trim leading slash
		if (substr($path,0,1) == '/') {
			$path = substr($path,1);
		}

		$route = $this->resolveRoute($path);

		if (false === $route) {
			// throw new HTTPException(404);
			header('HTTP/1.1 404');
			echo '404';
			exit();
		}

		$matches = [];
		preg_match_all($route['route'], $path, $matches);

		if (is_array($matches[0])) {
			$matches = array_shift($matches);
		}

		return $route['action']($matches);

	}

	/**
	 * @param string
	 * @return mixed
	 */
	protected function resolveRoute($test) {

		for($i = 0; $i < count($this->routes); $i++) {
			// string match
			if ($this->routes[$i]['route'] === $test) {
				return $this->routes[$i];
			}
			// regex
			else if (preg_match_all($this->routes[$i]['route'], $test)) {
				return $this->routes[$i];
			}
		}

		return false;

	}

	/**
	 * @return boolean
	 */
	protected function routeExists($test) {
		return $this->resolveRoute($test) !== false;
	}

}
