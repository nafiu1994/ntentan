<?php

namespace ntentan\controllers\model_binders;

use ntentan\utils\Input;
use ntentan\Controller;
use ntentan\controllers\ModelBinderInterface;
use ntentan\panie\Container;

/**
 * This class
 *
 * @author ekow
 */
class DefaultModelBinder implements ModelBinderInterface
{
    private $bound;

    /**
     *
     * @param \ntentan\Model $object
     */
    private function getModelFields($object)
    {
        return array_keys($object->getDescription()->getFields());
    }
    
    private function getClassFields($object)
    {
        $reflection = new \ReflectionClass($object);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $fields = [];
        foreach ($properties as $property) {
            $fields[] = $property->name;
        }
        return $fields;
    }

    public function bind(Controller $controller, $type, $name, $instance = null)
    {
        $this->bound = false;
        if (is_a($instance, '\ntentan\Model')) {
            $fields = $this->getModelFields($instance);
        } else {
            $fields = $this->getClassFields($instance);
        }
        $requestData = Input::post() + Input::get();
        foreach ($fields as $field) {
            if (isset($requestData[$field])) {
                $instance->$field = $requestData[$field] == '' ? null : $requestData[$field];
                $this->bound = true;
            }
        }
        return $instance;
    }

    public function requiresInstance() : bool
    {
        return true;
    }
}
