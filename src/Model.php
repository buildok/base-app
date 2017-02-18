<?php
namespace buildok\base;

use buildok\base\Config;
use buildok\helpers\ArrayWrapper;
use buildok\validator\Validator;

/**
 * Model class
 */
abstract class Model extends ArrayWrapper
{
    /**
     * Object of Validator class
     * @var Validator
     */
    protected $validator = null;

    /**
     * Get validation rules
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Load model data
     * @param  array  $data
     */
    public function load($data = [])
    {
        $this->set(array_merge($this->getData, $data));
    }

    /**
     * Validate loaded data
     * @return bool
     */
    public function validate()
    {
        if ($this->validator) {
            $this->validator->reload($this->getData());
        } else {
            $this->validator = new Validator($this->getData(), $this->rules());
        }

        return $this->validator->validate();
    }

    /**
     * Returns validation errors
     * @param  string $field Field name
     * @return array
     */
    public function getErrors($field = null)
    {
        if ($this->validator) {

            return $this->validator->getErrors($field);
        }

        return [];
    }

    /**
     * Returns TRUE if it has errors else FALSE
     * @return boolean
     */
    public function hasErrors()
    {
        return ($this->validator ? $this->validator->hasErrors() : false);
    }

    /**
     * Save model
     * @return bool
     */
    abstract public function save();

    /**
     * Find models
     * @param  array $filter Filters for select
     * @param  boolean $asObjects Type of array items
     * @return array Array of objects Model class or arrays of models data
     */
    abstract public static function find($filter, $asObjects = false);

    /**
     * Find one model
     * @param  int $id Model ID
     * @return Model Object of Model class
     */
    abstract public static function findOne($id);

    /**
     * Returns DB connection
     * @return PDOConnection
     */
    protected function getConnection()
    {
        $class = (Config::getInstance())->dataProvider['class'];
        $db = $class::getInstance();

        return $db;
    }

}
