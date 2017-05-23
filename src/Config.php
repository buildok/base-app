<?php
namespace buildok\base;

use buildok\base\exceptions\AppException;

/**
* Config class
*/
class Config
{
    /**
     * Application settings
     * @var array
     */
    private $settings;

    /**
     * Instance of this class
     * @var Config
     */
    private static $_instance = null;

    /**
     * Get object of this class
     * @return Config
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * [getComponent description]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function getComponent($name)
    {
        if (!$this->components || !array_key_exists($name, $this->components)) {
            throw new AppException("Component $name not found", 500);
        }

        $cfg = $this->components[$name];

        if (!array_key_exists('class', $cfg)) {
            throw new AppException("Not specified [class] for component $name", 500);
        }

        if (!class_exists($cfg['class'])) {
            throw new AppException("Class $class_ns not found: $uri", 500);
        }

        return $cfg['class'];
    }

    public function getArgs($name)
    {
        if (!$this->components || !array_key_exists($name, $this->components)) {
            throw new AppException("Component $name not found", 500);
        }

        $cfg = $this->components[$name];

        unset($cfg['class']);
        $args = array_values($cfg);

        return $args;
    }

    /**
     * Overload.
     *
     * Returns parameter value or NULL if not exist
     * @param string $name Parameter name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->settings)) {
            return $this->settings[$name];
        }

        return null;
    }

    /**
     * Constructor
     *
     * @throws AppException
     */
    private function __construct()
    {
        if (!defined('ROOT')) {
            throw new AppException('ROOT is not defined');
        }

        $this->settings = require(ROOT . '/config/web.php');
    }
}