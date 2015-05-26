<?php
/**
 * The Controller base class for the Ntentan framework
 * 
 * Ntentan Framework
 * Copyright (c) 2008-2012 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright Copyright 2010 James Ekow Abaka Ainooson
 * @license MIT
 */

namespace ntentan\controllers;

use ntentan\caching\Cache;
use ntentan\controllers\exceptions\ComponentNotFoundException;
use \ReflectionClass;
use \ReflectionObject;
use ntentan\Ntentan;
use ntentan\View;
use ntentan\models\Model;
use ntentan\utils\Text;

/**
 * The Controller class represents the base class for all controllers that are
 * built for the ntentan framework. Controllers are used to direct the flow of
 * your application logic. They are stored in modules and they contain methods
 * which are called from the url. Parameters to the methods are also passed
 * through the URL. If a method is not specified, the default method is called.
 * The methods called by the controllers are expected to set data into variables
 * which are later rendered as output to the end user through views.
 *
 * @author  James Ekow Abaka Ainooson
 * @todo    Controllers must output data that can be passed to some kind of
 *          template engine like smarty.
 */
class Controller
{
    /**
     * The name of the default method to execute when the controller is called
     * without any action methods specified.
     * @var string
     */
    public $defaultMethodName = "run";

    /**
     * A copy of the route that was used to load this controller.
     * @var String
     */
    public $route;

    /**
     * A short machine readable name for this controller.
     * @var string
     */
    public $name;

    /**
     * The variables generated by any method called in this controller are
     * stored in this array. The values from this array would later be used
     * with the view classes to render the views.
     * @var Array
     */
    public $variables = array();

    /**
     * An array to hold the names of all the loaded components of this instance
     * of the controller class.
     * @var type Array
     */
    public $components = array();

    /**
     * An array to hold the instances of all the loaded components of this instance
     * of the controller class. The controller names in the Controller::variables
     * property correspond directly with the instances in this array.
     * @var type Array
     */
    private $componentInstances = array();

    /**
     * The raw name of the method to be called in this controller
     * @var string
     */
    public $rawMethod;

    /**
     * An array detailing all the loaded components.
     * @var array
     */
    private static $loadedComponents = array();

    /**
     * The instance of the view template which is going to be used to render
     * the output of this controller.
     * @var View
     */
    private $viewInstance;

    /**
     * The instance of the model class which shares the same package or namespace
     * with this controller.
     * @var Model
     */
    private $modelInstance;

    /**
     * A route to the model of the default model this controller is liked to.
     * @var string
     */
    private $modelRoute;

    /**
     * Stores the data this controller holds for passing ot to the template.
     * This data is stored as an associative array in this variable. The values
     * can be manipulated through the Controller::set() method.
     * @var array
     */
    public $data;

    /**
     * The directory path to the file of this controller's class.
     * @var string
     */
    public $filePath;

    public $method;

    /**
     * Returns the name of the controller.
     * @return string
     */
    public function getName()
    {
        $object = new ReflectionObject($this);
        return $object->getName();
    }

    /**
     * Setter property
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value)
    {
        switch($property)
        {
        case "layout":
            $this->view->layout = $value;
            break;
        }
    }

    public function __get($property)
    {
        switch ($property)
        {
        case "view":
            $viewInstance = $this->getViewInstance();
            if($viewInstance == null)
            {
                $viewInstance = new View();
                $viewInstance->setLayout('main.tpl.php');
                $this->setViewInstance($viewInstance);
                $viewInstance->defaultTemplatePath = $this->filePath;
            }
            return $viewInstance;

        case "layout":
            return $this->view->layout;

        case "model":
            if($this->modelInstance == null)
            {
                $this->modelInstance = Model::load($this->modelRoute);
            }
            return $this->modelInstance;

        case "directory":
            return Ntentan::$modulesPath . $this->route . "/";

        default:
            if(substr($property, -9) == "Component")
            {
                $component = substr($property, 0, strlen($property) - 9);
                return $this->getComponentInstance($component);
            }
            else
            {
                throw new \Exception("Unknown property *{$property}* requested");
            }
        }
    }

    /**
     * Adds a component to the controller. Component loading is done with the
     * following order of priority.
     *  1. Application components
     *  2. Plugin components
     *  3. Core components
     *  
     * @param string $component Name of the component
     * @todo cache the location of a component once found to prevent unessearry
     * checking
     */
    public function addComponent()
    {
        $arguments = func_get_args();
        $component = array_shift($arguments);
        
        // Attempt to load an application component
        $namespace = "\\" . Ntentan::$namespace . "\\components";
        $className = $this->loadComponent($component, $arguments, $namespace); 
        if(is_string($className))
        {
            return;
        }
        
        // Attempt to load plugin component
        $componentPaths = explode(".", $component);
        $namespace = "\\ntentan\\extensions\\{$componentPaths[0]}\\components";
        $className = $this->loadComponent(
            $componentPaths[1], 
            $arguments, 
            $namespace, 
            $componentPaths[0]
        );
         
        if(is_string($className))
        {
            return;
        }
        
        // Attempt to load a core component
        $className = $this->loadComponent(
            $component, $arguments, '\\ntentan\\controllers\\components'
        );
        if(is_string($className))
        {
            return;
        }
        throw new exceptions\ComponentNotFoundException(
            "Component not found *$component*"
        );
    }

    private function loadComponent($component, $arguments, $path, $plugin = null)
    {
        $camelizedComponent = Text::ucamelize($component);
        $componentName = "$path\\$component\\{$camelizedComponent}Component";
        if(class_exists($componentName))
        {
            $key = Text::camelize($plugin . ($plugin == null ? $camelizedComponent : $camelizedComponent));

            $componentClass = new ReflectionClass($componentName);
            $componentInstance = $componentClass->newInstanceArgs($arguments);
            $componentInstance->filePath = Ntentan::getFilePath("lib/controllers/components/$component");
            
            $this->componentInstances[$key] = $componentInstance;
            $this->componentInstances[$key]->setController($this);
            $this->componentInstances[$key]->route = $this->route;
            $this->componentInstances[$key]->init();
            
            return $componentName;
        }
        else
        {
            return false;
        }
    }

    /**
     *
     * @param mixed $params1
     * @param string $params2
     */
    protected function set($params1, $params2 = null)
    {
        if(is_object($params1) && \method_exists($params1, "toArray"))
        {
            $this->variables = array_merge($this->variables, $params1->toArray());
        }
        else if(is_array($params1))
        {
            $this->variables = array_merge($this->variables, $params1);
        }
        else
        {
            if(\is_object($params2) && method_exists($params2, "toArray"))
            {
                $params2 = $params2->toArray();
            }
            $this->variables[$params1] = $params2;
        }
    }
    
    public function setIfNotSet($params1, $params2)
    {
        if(!isset($this->variables[$params1]))
        {
            $this->variables[$params1] = $params2;
        }
    }

    protected function getVariable($variable)
    {
        return $this->variables[$variable];
    }

    protected function getRawMethod()
    {
        return $this->rawMethod;
    }

    /**
     * Appends a string to an already setup template variable.
     * @param string $params1
     * @param string $params2
     */
    protected function append($params1, $params2)
    {
        $this->variables[$params1] .= $params2;
    }

    protected function getData($variable = null)
    {
        return $variable != null ? $this->variables[$variable] : $this->variables;
    }

    /**
     * A utility method to load a controller. This method loads the controller
     * and fetches the contents of the controller into the Controller::$contents
     * variable if the get_contents parameter is set to true on call. If a
     * controller doesn't exist in the module path, a ModelController is loaded
     * to help manipulate the contents of the model. If no model exists in that
     * location, it is asumed to be a package and a package controller is
     * loaded.
     *
     * @param $path                 The path for the model to be loaded.
     * @param $returnInstanceOnly   Fources the method to return only the instance of the controller object.
     * @return Controller
     */
    public static function load($route, $returnInstanceOnly = false)
    {
        $controllerRoute = '';
        $routeArray = explode('/', $route);

        // Loop through the filtered path and extract the controller class
        for($i = 0; $i<count($routeArray); $i++)
        {
            $p = $routeArray[$i];
            $pCamelized = Text::ucamelize($p);
            $filePath = Ntentan::$modulesPath . "/modules/$controllerRoute/$p/";
            if(file_exists($filePath . "{$pCamelized}Controller.php"))
            {
                $controllerName = $pCamelized."Controller";
                $controllerRoute .= "/$p";
                $modelRoute .= "$p";

                if($controllerRoute[0] == "/") $controllerRoute = substr($controllerRoute,1);

                if($controllerName == "")
                {
                    Ntentan::error("Path not found! [$route]");
                }
                else
                {
                    $controllerNamespace = "\\" . str_replace("/", "\\", Ntentan::$namespace . "/modules/$controllerRoute/");
                    $controllerName = $controllerNamespace . $controllerName;
                    if(class_exists($controllerName))
                    {
                        $controller = new $controllerName();
                        foreach($controller->components as $component)
                        {
                            $controller->addComponent($component);
                        }

                        $controller->setRoute($controllerRoute);
                        $controller->setName($controllerName);
                        $controller->modelRoute = $modelRoute;
                        $controller->filePath = $filePath;
                        $controller->init();
                        
                        if($returnInstanceOnly) return $controller;
                        
                        // Trap for the cache
                        if(Cache::exists("view_" . Ntentan::$route) && Ntentan::$debug === false)
                        {
                            echo Cache::get('view_' . Ntentan::$route);
                            return;
                        }

                        if($controller->method == '')
                        {
                            $controller->method = $routeArray[$i + 1] != '' ? Text::ucamelize($routeArray[$i + 1], ".", "", true) : $controller->defaultMethodName;
                            $controller->rawMethod = $routeArray[$i + 1] != '' ? $routeArray[$i + 1]: $controller->defaultMethodName;
                        }

                        if(!$controller->hasMethod())
                        {
                            $modelRoute .= ".";
                            continue;
                        }
                    }
                    else
                    {
                        Ntentan::error("Controller class *$controllerName* not found.");
                    }
                    $controller->runMethod(array_slice($routeArray, $i + 2));
                    return;
                }
            }
            else
            {
                $controllerRoute .= "/$p";
                $modelRoute .= "$p.";
            }
        }
        if(is_object($controller))
        {
            $message = "Controller method *{$routeArray[$i - 1]}()* not found for the *{$controllerName}* controller.";
        }
        else
        {
            $message = "Controller not found for route *$route*";
        }
        Ntentan::error($message);
    }

    /**
     * Set the name of this controller
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        foreach($this->componentInstances as $component)
        {
            $component->setControllerName($name);
        }
    }

    /**
     * Set the value of the route used to load this controller.
     * @param string $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
        foreach($this->componentInstances as $component)
        {
            $component->setControllerRoute($route);
        }
    }

    /**
     * Returns true if this controller has the requested method and returns
     * false otherwise.
     * @param string $method
     * @return booleam
     */
    public function hasMethod($method = null)
    {
        $ret = false;
        $path = $method === null ? $this->method : $method;
        if(method_exists($this, $path))
        {
            $ret = true;
        }
        else
        {
            foreach($this->componentInstances as $i => $component)
            {
                $ret = $component->hasMethod($path);
                if($ret)
                {
                    break;
                }
            }
        }
        return $ret;
    }

    public function setView($view)
    {
        $this->viewInstance = $view;
    }

    public function runMethod($params, $method = null)
    {
        $path = $method === null ? $this->method : $method;
        if(method_exists($this, $path))
        {
            $controllerClass = new ReflectionClass($this->getName());
            $method = $controllerClass->GetMethod($path);
            if($this->view->getTemplate() == null)
            {
                $this->view->setTemplate(
                    str_replace("/", "_", $this->route) 
                    . '_' . $this->getRawMethod() 
                    . '.tpl.php');
            }
            $method->invokeArgs($this, $params);
            $this->preRender();
            $return = $this->view->out($this->getData());
            $return = $this->postRender($return);
        }
        else
        {
            foreach($this->componentInstances as $component)
            {
                //@todo Look at how to prevent this from running several times
                if($component->hasMethod($path))
                {
                    $component->variables = $this->variables;
                    $component->runMethod($params, $path);
                    break;
                }
            }
        }
        
        if($this->view->getCacheTimeout() !== false && Ntentan::$debug !== true)
        {
            Cache::add('view_' . Ntentan::getRouteKey(), $return, $this->view->cacheTimeout);
        }
        echo $return;
    }

    public function preRender()
    {

    }

    /**
     *
     */
    public function postRender($data)
    {
        return $data;
    }

    /**
     * Checks whether this controller has a particular component loaded.
     * @param string $component
     * @return boolean
     */
    public function hasComponent($component)
    {
        if(array_search($component, array_keys($this->componentInstances)) !== false)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function getViewInstance()
    {
        return $this->viewInstance;
    }

    protected function setViewInstance($viewInstance)
    {
        $this->viewInstance = $viewInstance;
    }

    protected function getComponentInstance($component = false)
    {
        if($component === false)
        {
            return $this->componentInstances;
        }
        else
        {
            if(is_object($this->componentInstances[$component]))
            {
                return $this->componentInstances[$component];
            }
            else
            {
                throw new ComponentNotFoundException("Component <code><b>$component</b></code> not currently loaded.");
            }
        }
    }

    /**
     * Function called automatically after the controller is initialized. This
     * method should be overriden by controllers which want to initialize
     * certain variables after the constructor function is called.
     */
    public function init()
    {

    }
}
