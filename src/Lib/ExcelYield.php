<?php

namespace Mt\Lib;

use Fw\InstanceTrait;

class ExcelYield
{

    use InstanceTrait {
        getInstance as protected _getInstance;
    }

    protected $className = null;
    protected $method = null;
    protected $args = null;

    protected function __construct($className, $method, ...$args)
    {
        $this->className = $className;
        $this->method = $method;
        $this->args = $args;
    }

    public static function getInstance($className, $method, ...$args)
    {
        return self::_getInstance($className, $method, ...$args);
    }

    public function getData()
    {
        $className = $this->className;
        $method = $this->method;
        $args = $this->args;
        $classParams = [];
        if (is_array($className)) {
            $classBuffer = $className[0];
            if (count($className) > 1) {
                array_shift($className);
                $classParams = $className;
            }
            $className = $classBuffer;
        }
        if (method_exists($className, "getInstance")) {
            $object = $className::getInstance(...$classParams);
        } else {
            $object = new $className(...$classParams);
        }
        return $object->$method(...$args);
    }
}