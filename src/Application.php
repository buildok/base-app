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
            echo (new $controller_ns)->{$action}();

        } catch (HttpException $e) {
            http_response_code($e->getCode());
            echo $e->getMessage();
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
    public function __call($name, $args)
    {
        $class_ns = self::config()->getComponent($name);
        if (!$args) {
            $args = self::config()->getArgs($name);
        }

        $component = new $class_ns(...$args);

        return $component;
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
        $class_ns = self::config()->getComponent($name);
        if (!$args) {
            $args = self::config()->getArgs($name);
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


}