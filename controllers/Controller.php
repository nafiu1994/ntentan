<?php

/**
 * The Controller class represents the base class for all controllers that are
 * built for the ntentan framework. Controllers are used to direct the flow of
 * your application. They are stored in modules and they contain methods which
 * are called from the url. Parameters to the methods are also passed through the
 * URL. If no method is specified, the Controller:getContents() method is called.
 * The methods called by the controllers are expected to generate HTML output
 * which should be directly displayed to the screen.
 *
 * All the controllers you build must extend this class and implement 
 *
 * @todo Controllers must output data that can be passed to some kind of template
 *       engine like smarty.
 * @author james
 *
 */
class Controller
{
    public $defaultMethodName = "run";

	/**
	 * A copy of the path that was used to load this controller.
	 * @var String
	 */
	public $path;

	/**
	 * A short machine readable name for this controller.
	 * @var string
	 */
	public $name;

    /**
     *
     * @var Array
     */
    public $variables = array();
    
    protected $components = array();

    /**
     *
     * @var Array
     */
    private $componentInstances = array();

    /**
     *
     * @var View
     */
    public $viewInstance;
    
    private $modelInstance;
    
    private $modelPath;

    public $data;

    protected $blocks = array();
    
    public function __construct()
    {
        foreach($this->components as $component)
        {
            $this->addComponent($component);
        }
    }

    public function getName()
    {
        $object = new ReflectionObject($this);
        return $object->getName();
    }

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
            if($this->viewInstance == null)
            {
                $this->viewInstance = new View();
                $this->viewInstance->layout = "main";
                $this->viewInstance->template = $path;
            }
            return $this->viewInstance;

        case "layout":
            return $this->view->layout;
            
        case "model":
            if($this->modelInstance == null)
            {
                $this->modelInstance = Model::load($this->modelPath);
            }
            return $this->modelInstance;
            
        case "directory":
            return Ntentan::$packagesPath . $this->path . "/";
            
        default:
            if(substr($property, -5) == "Block")
            {
                $block = substr($property, 0, strlen($property) - 5);
                return $this->blocks[$block];
            }
            else if(substr($property, -9) == "Component")
            {
                $component = substr($property, 0, strlen($property) - 9);
                return $this->componentInstances[$component];
            }
        }
    }

    /**
     * Adds a component to the controller.
     * @param string $component Name of the component
     */
    public function addComponent($component)
    {
        Ntentan::addIncludePath(Ntentan::getFilePath("controllers/components/$component"));
        $componentName = ucfirst($component) . "Component";
        $componentInstance = new $componentName();
        $componentInstance->setController($this);
        $this->componentInstances[$component] = $componentInstance;
    }

    public function addBlock($blockName, $alias = null)
    {
        Ntentan::addIncludePath(Ntentan::$blocksPath . "$blockName");
        $blockClass = Ntentan::camelize($blockName)."Block";
        $blockInstance = new $blockClass();
        $blockInstance->setName($blockName);
        if($alias == null) $alias = $blockName;
        $this->blocks[$alias] = $blockInstance;
    }

    /**
     * 
     * @param mixed $params1
     * @param string $params2
     */
    protected function set($params1, $params2 = null)
    {
        if(is_array($params1))
        {
            $this->variables = array_merge($this->variables, $params1);
        }
        else
        {
            $this->variables[$params1] = $params2;
        }
    }

    protected function get()
    {
        return $this->variables;
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
	 * @param $path 		The path for the model to be loaded.
	 * @return Controller
	 */
	public static function load($path)
	{
        $controllerPath = '';
        $controllerPathArray = explode('/', $path);

        // Remove all empty paths from the query
        foreach($controllerPathArray as $value)
        {
            if($value == "") continue;
            $pathArray[] = $value;
        }
        
		for($i = 0; $i<count($pathArray); $i++)
		{
			$p = $pathArray[$i];
            $pCamelized .= Ntentan::camelize($p);
			if(file_exists(Ntentan::$packagesPath . "$controllerPath/$p/{$pCamelized}Controller.php"))
			{
				$controllerName = $pCamelized."Controller";
				$controllerPath .= "/$p";
                $modelPath .= ".$p";
				break;
			}
			else
			{
				$controllerPath .= "/$p";
                $modelPath .= ".$p";
			}
		}

        $controllerPath = substr($controllerPath,1);

        if($controllerName == "")
        {
            Ntentan::error("Path not found! [$path]");
        }
        else
        {
            require_once Ntentan::$packagesPath . "$controllerPath/$controllerName.php";
            if(class_exists($controllerName))
            {
                $controller = new $controllerName();
                $controller->setPath($controllerPath);
                $controller->setName($controllerName);
                $controller->modelPath = $modelPath;
            }
            else
            {
            	Ntentan::error("Controller class <b><code>$controllerName</code></b> not found.");
            }

            if($i != count($pathArray)-1)
            {
                $methodName = $pathArray[$i+1];
            }
            else
            {
                $methodName = $controller->defaultMethodName;
            }

            if($controller->hasPath($methodName))
            {
                $ret = $controller->runPath($methodName, array_slice($pathArray,$i+2));
            }
            else
            {
                echo Ntentan::message("Method not found <code><b>$controllerName::$methodName()</b></code>");
                die();
            }
        }
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

    public function setPath($path)
    {
        $this->path = $path;
        foreach($this->componentInstances as $component)
        {
            $component->setControllerPath($path);
        }
    }

    public function hasPath($path)
    {
        $ret = false;
        if(method_exists($this, $path))
        {
            $ret = true;
        }
        else
        {
            foreach($this->componentInstances as $component)
            {
                $ret = $component->hasPath($path);
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

    public function runPath($path, $params)
    {
        if(method_exists($this, $path))
        {
            $this->mainPreRender();
            $controllerClass = new ReflectionClass($this->getName());
            $method = $controllerClass->GetMethod($path);
            $this->view->template = Ntentan::$packagesPath . "$this->path/$path.tpl.php";
            $method->invokeArgs($this, $params);
            $this->view->layout->blocks = $this->blocks;
            $ret = $this->view->out($this->get());
            $this->mainPostRender();
        }
        else
        {
            foreach($this->componentInstances as $component)
            {
                if($component->hasPath($path))
                {
                    $component->variables = $this->variables;
                    $component->blocks = $this->blocks;
                    $component->runPath($path, $params);
                }
            }
        }
        echo $ret;
    }

    public function mainPreRender()
    {
        foreach($this->componentInstances as $component)
        {
            $component->preRender();
        }
        $this->preRender();
    }

    public function mainPostRender()
    {
        foreach($this->componentInstances as $component)
        {
            $component->postRender();
        }
        $this->postRender();
    }

    public function preRender()
    {

    }

    public function postRender()
    {
        
    }
    
    public function hasComponent($component)
    {
        if(array_search($component, $this->components) !== false)
        {
            return true;        
        }
        else
        {
            return false;
        }
    }
    
}