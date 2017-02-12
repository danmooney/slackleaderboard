<?php

namespace App;
use Illuminate\Foundation\Application as IlluminateApplication;

class Application extends IlluminateApplication
{
    private $_helpers = [];

    public function addHelper($name, callable $func)
    {
        $this->_helpers[$name] = $func;
    }

    public function __call($method, $args)
    {
        if (isset($this->_helpers[$method])) {
            $this->_helpers[$method]($args);
        }
    }
}