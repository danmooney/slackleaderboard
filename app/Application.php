<?php

namespace App;
use Illuminate\Foundation\Application as IlluminateApplication;

class Application extends IlluminateApplication
{
    private $_helpers = [];

    private static $_demo_mode = false;

    public function addHelper($name, $func_or_obj)
    {
        $this->_helpers[$name] = $func_or_obj;
    }

    public function __get($key)
    {
        if (isset($this->_helpers[$key])) {
            return $this->_helpers[$key];
        }

        return parent::__get($key);
    }

    public function __call($method, $args)
    {
        if (isset($this->_helpers[$method])) {
            return $this->_helpers[$method](...$args);
        }

        trigger_error('Call to undefined method ' . __CLASS__ . '::' . $method . '()', E_USER_ERROR);
    }

    public static function setDemoMode($demo_mode = true)
    {
        static::$_demo_mode = $demo_mode;
    }

    public static function getDemoMode()
    {
        return static::$_demo_mode;
    }
}