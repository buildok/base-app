<?php
namespace buildok\base;

use buildok\base\Config;
use buildok\base\exceptions\BaseHttpException;
use buildok\base\exceptions\BaseAppException;

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

        } catch (BaseAppException $e) {
            $code = $e->getCode();
            $message = $e->getMessage();

            echo $this->error("Application Error:[$code] $message", 500);
        } catch (BaseHttpException $e) {

            echo $this->error($e->getMessage(), $e->getCode());
        }

        $out = ob_get_contents();
        ob_end_clean();

        file_put_contents('php://output', $out);
    }

    /**
     * [route description]
     * @param  [type] $URI [description]
     * @return [type]      [description]
     *
     * @throws BaseAppException
     * @throws BaseAppException
     */
    private function route($URI)
    {
        $res = $this->parse(trim($URI, '/'));
        if (count($res) < 2) {
            throw new BaseAppException('Wrong definition of rules');
        }

        $controller_ns = 'app\\controllers\\' . ucfirst($res[0]);
        $action = $res[1];

        if (!class_exists($controller_ns) || !method_exists($controller_ns, $action)) {
            throw new BaseHttpException("Page not found: $URI", 404);
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
        $patterns = (Config::getInstance())->routes['patterns'];
        foreach($patterns as $pattern => $route) {
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