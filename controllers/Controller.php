<?php
/*require_once "ModelController.php";
require_once "PackageController.php";
require_once "ErrorController.php";
require_once "ReportController.php";*/

require_once "Model.php";

/**
 * The Controller class represents the base class for all controllers that are
 * built for the ntentan framework. Controllers are used to direct the flow of
 * your application. They are stored in modules and they contain methods which
 * are called from the url. Parameters to the methods are also passed through the
 * URL. If no method is specified, the Controller:getContents() method is called.
 * The methods called by the controllers are expected to generate HTML output
 * which should be directly displayed to the screen.
 *
 * All the controllers you build must extend this class end implement
 *
 * @todo Controllers must output data that can be passed to some kind of template
 *       engine like smarty.
 * @author james
 *
 */
abstract class Controller
{
	/**
	 * A copy of the path that was used to load this controller in an array
	 * form.
	 * @var Array
	 */
	public $path;

	/**
	 * A short machine readable name for this label.
	 * @var string
	 */
	public $name;

	/**
	 * A utility method to load a controller. This method loads the controller
	 * and fetches the contents of the controller into the Controller::$contents
	 * variable if the get_contents parameter is set to true on call. If a controller
	 * doesn't exist in the module path, a ModelController is loaded to help
	 * manipulate the contents of the model. If no model exists in that location,
	 * it is asumed to be a package and a package controller is loaded.
	 *
	 * @param $path 		The path for the model to be loaded.
	 * @param $get_contents A flag which determines whether the contents of the
	 *						controller should be displayed.
	 * @return Controller
	 */
	public static function load($path)
	{
        $controllerPath = '';
        $pathArray = explode('/', $path);
        
		for($i = 0; $i<count($pathArray); $i++)
		{
			$p = $pathArray[$i];
			if(file_exists(Ntentan::$packagesPath . "$controllerPath/$p/$p.php"))
			{
				$controllerName = $p;
				$controllerPath .= "/$p";
				break;
			}
			else
			{
				$controllerPath .= "/$p";
			}
		}

    	require_once Ntentan::$packagesPath . "$controllerPath/$controllerName.php";
		$controller = new $controllerName();
	
		if($i != count($pathArray)-1)
		{
			if(method_exists($controller,$pathArray[$i+1]))
			{
				$controllerClass = new ReflectionClass($controllerName);
				$method = $controllerClass->GetMethod($pathArray[$i+1]);
				$ret = $method->invoke($controller,array_slice($pathArray,$i+2));
			}
        	else
			{
				//$ret = "<h2>Error</h2> Method does not exist. ".$pathArray[$i+1];
    		}
		}
						
		return $controller;
	}

	/**
	 * An implementation of the default getContents method which returns a No
	 * content string.
	 *
	 * @todo When the controllers are changed to return variables for template
	 * 		 engines make this return that to.
	 * @return string
	 */
	protected function getContents()
	{
		return "No Content";
	}

    /**
     * 
     * @return <type>
     */
	public function showInMenu()
	{
		return $this->_showInMenu;
	}

	public function getPermissions()
	{

	}

	public function getTemplateDescription($template,$data)
	{
		return array("template"=>"file:/".getcwd()."/app/modules/$template","data"=>$data);
	}
}
?>
