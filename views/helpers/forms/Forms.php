<?php

namespace ntentan\views\helpers\forms;

use \ntentan\views\helpers\Helper;
use \ntentan\Ntentan;
use \ReflectionMethod;

/**
 * Enter description here ...
 * @author ekow
 *
 */
class Forms extends Helper
{
    private $container;
    
    public $submitValue;
    
    public $id;
    
    public function __construct()
    {
        $this->container = new FormContainer();
    }
    
    public function __toString()
    {
        $this->container->submitValue = $this->submitValue;
        $this->container->setId($this->id);
        return (string)$this->container;
    }
    
    public function add()
    {
        $args = func_get_args();
        if(is_string($args[0]))
        {
            $method = new ReflectionMethod(__NAMESPACE__ . "\\Element", "create");
            $element = $method->invokeArgs(null, $args);
            $this->container->add($element);
        }
        return $element;
    }
    
    public function generateForm($description)
    {
        
    }
    
    public function setErrors($errors)
    {
        $this->container->setErrors($errors);
    }
    
    public function setData($data)
    {
        $this->container->setData($data);
    }
    
    public function addModelField($field, $return = false)
    {
        switch($field["type"])
        {
            case "double":
                $element = new TextField(ucwords(str_replace("_", " ", $field["name"])));
                $element->setAsNumeric();
                break;

            case "integer":
                if($field["foreing_key"]===true)
                {
                    $element = new ModelField(ucwords(str_replace("_", " ", substr($field["name"], 0, strlen($field["name"])-3))), $field["model"]);
                    $element->name = $field["name"];
                }
                else
                {
                    $element = new TextField(ucwords(str_replace("_", " ", $field["name"])));
                    $element->setAsNumeric();
                }
                break;

            case "string":
               $element = new TextField(Ntentan::toSentence($field["name"]), $field["name"]);
                break;
            case "text":
                $element = new TextArea(Ntentan::toSentence($field["name"]), $field["name"]);
                break;
            case "boolean":
                $element = new Checkbox(Ntentan::toSentence($field["name"]), $field["name"]);
                break;
                
            default:
                throw new \Exception("Unknown data type {$field["type"]}");
        }
        if($field["required"]) $element->setRequired(true);
        $element->setDescription($field["comment"]);
        if($return)
        {
            return $element;
        }
        else
        {
            $this->container->add($element);
        }
    }
}