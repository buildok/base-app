<?php
namespace buildok\base;

use buildok\base\Application as App;

/**
 *
 */
class Router
{
	private $uri;

	public function __construct()
	{
		$this->uri = trim($_SERVER['REQUEST_URI'], '/');
	}

    /**
     * Returns route
     * @param  string $URI Request string
     * @return array Array of route as [controller_ns, action]
     *
     * @throws HttpException
     */
    private function route($URI)
    {
        $res = $this->parse(trim($URI, '/'));
        if (count($res) < 2) {
            throw new HttpException("Page not found: $URI", 404);
        }

        $controller_ns = 'app\\controllers\\' . ucfirst($res[0]);
        $action = $res[1];

        if (!class_exists($controller_ns) || !method_exists($controller_ns, $action)) {
            throw new HttpException("Page not found: $URI", 404);
        }

        return [$controller_ns, $action];
    }

    /**
     * Parse request URI
     * @param string Request URI
     * @return array Return array of result URI parsing as [controller, action]
     */
    private function parse($URI)
    {
        $patterns = self::config()->routes['patterns'];
        foreach ($patterns as $pattern => $route) {
            if (!preg_match($pattern, $URI, $matches) === false) {

                $route = $this->fetchPattern('controller', $matches, $route);
                $route = $this->fetchPattern('action', $matches, $route);

                return explode('/', $route);
            }
        }

        return [];
    }

    /**
     * Fetch pattern  with route data
     * @param  string $part Part of pattern
     * @param  array $matches Array of route data
     * @param  strng $route  Pattern
     * @return string Changed pattern
     */
    private function fetchPattern($part, $matches, $route)
    {
        if (array_key_exists($part, $matches)) {
            $route = str_replace($part, strtolower($matches[$part]), $route);
        }

        return $route;
    }
}