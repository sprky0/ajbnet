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

	protected array $routes = [];

	public function __construct() {
	}

	public function register(string $route, callable $action): void {

		if ($this->routeExists($route)) {
			throw new Exceptions\ApplicationException("Route '{$route}' is already registered.");
		}

		$this->routes[] = [
			'route' => $route,
			'action' => $action
		];
	}

	public function resolve(?string $path = null): mixed {

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

		// locate a matching route on the stack
		$route = $this->resolveRoute($path);

		if (false === $route) {
			// throw new HTTPException(404);
			header('HTTP/1.1 404');
			echo '404';
			exit();
		}

		$matches = [];
		if (preg_match('/^\/.*\/$/', $route['route'])) {
			preg_match_all($route['route'], $path, $matches);
		}

		return (isset($matches[1]) && is_array($matches[1])) ? $route['action']($matches[1]) : $route['action']();

	}

	/**
	 * @param string $test
	 * @return array|false
	 */
	protected function resolveRoute(string $test): array|false {

		for($i = 0; $i < count($this->routes); $i++) {
			// string match
			if ($this->routes[$i]['route'] === $test) {
				return $this->routes[$i];
			}
			// regex match (only if route looks like a regex with delimiters)
			else if (preg_match('/^\/.*\/$/', $this->routes[$i]['route']) && preg_match_all($this->routes[$i]['route'], $test)) {
				return $this->routes[$i];
			}
		}

		return false;

	}

	/**
	 * @return boolean
	 */
	protected function routeExists(string $test): bool {
		return $this->resolveRoute($test) !== false;
	}

}
