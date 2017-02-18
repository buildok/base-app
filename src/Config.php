<?php
namespace buildok\base;

use buildok\base\exceptions\BaseAppException;

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
     * Overload.
     *
     * Returns parameter value or NULL if is not exist
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
     * @throws BaseAppException
     */
    private function __construct()
    {
        if (!defined('ROOT')) {
            throw new BaseAppException('ROOT not defined');
        }

        $this->settings = require(ROOT . '/app/config/web.php');
    }
}