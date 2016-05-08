<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\controllers;

use ntentan\utils\Input;
use ntentan\Controller;

/**
 * Description of DefaultModelBinder
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
    
    public function bind(Controller $controller, $type)
    {
        $this->bound = false;
        $object = \ntentan\panie\InjectionContainer::resolve($type);
        if(is_a($object, '\ntentan\Model')) {
            $fields = $this->getModelFields($object);
        } else {
            $fields = $this->getClassFields($object);
        }
        $requestData = Input::post() + Input::get();
        foreach($fields as $field)
        {
            if(isset($requestData[$field])) {
                $object->$field = $requestData[$field] == '' ? null : $requestData[$field];
                $this->bound = true;
            }
        }
        return $object;
    }
    
    public function getBound()
    {
        return $this->bound;
    }
}
