<?php
namespace buildok\base;

use buildok\base\Config;
use buildok\base\exceptions\HttpException;
use buildok\base\exceptions\AppException;

/**
 *  Application Class
 */
class Application
{
    /**
     * Request processing
     *
     * Call controller action from request URL
     */
    public function run()
    {
        ob_start();

        try {

            list ($controller_ns, $action) = $this->route($_SERVER['REQUEST_URI']);
            echo (new $controller_ns)->$action();

        } catch (AppException $e) {
            $code = $e->getCode();
            $message = $e->getMessage();

            echo $this->error("Application Error:[$code] $message", 500);
        } catch (HttpException $e) {

            echo $this->error($e->getMessage(), $e->getCode());
        }

        $out = ob_get_contents();
        ob_end_clean();

        file_put_contents('php://output', $out);
    }

    /**
     * Overload
     *
     * Returns object of specified component
     * @param  string $name Component name
     * @param  array $args
     * @return mixed
     *
     * @throws AppException
     */
    public static function __callStatic($name, $args)
    {
        if (!self::config()->components || !array_key_exists($name, self::config()->components)) {
            throw new AppException("Component $name not found", 500);
        }

        $cfg = self::config()->components[$name];

        if (!array_key_exists('class', $cfg)) {
            throw new AppException("Not specified [class] for component $name", 500);
        }

        $class_ns = $cfg['class'];

        if (!class_exists($class_ns)) {
            throw new AppException("Class $class_ns not found: $uri", 500);
        }

        if (!$args) {
            unset($cfg['class']);
            $args = array_values($cfg);
        }

        $component = $class_ns::getInstance(...$args);

        return $component;
    }

    /**
     * Get application config
     * @return Config Object of class Config
     */
    public static function config()
    {
        return Config::getInstance();
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

    /**
     * Set error code
     * @param int $code Error code
     * @param string $message Error message
     * @return JSON Error description
     */
    private function error($message, $code)
    {
        http_response_code($code);

        return json_encode(['error' => $message, 'code' => $code]);
    }
}