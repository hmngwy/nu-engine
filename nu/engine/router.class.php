<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();

/**
 * class Router processes requests and returns corresponding output.
 *
 * This class accepts a registry instance, parses the request url,
 * cheks the request against the router rules and executes the
 * corresponding controller class and action.
 * 
 * Based on Dennis Pallett article http://web.archive.org/web/20080128233531/www.phpit.net/article/simple-mvc-php5/3/
 * 
 * @access public
 * @author pat ambrosio <cp.ambrosio@gmail.com>
 * @package nu
 * @version 1.0
 * @todo document class methods.
 **/
class Router extends CoreLib
{
	private $registry;
	private $output;
	
	public $routeRules;
	public $overrideRules = false;
	
	#ROUTER CLASS NEEDS THE REGISTRY SO IT CAN PASS IT ON TO THE CONTROLLER INSTANCE
	public function __construct($registry)
	{
    	$this->registry = $registry;
	}
	
	public function execute($routeRules)
	{
		$this->routeRules = $routeRules;
		
		#die(print_r($this->routeRules, true));
		if($this->routeRules->match === true || $this->overrideRules === true)
		{
			$controllerfile = CONTROLLERDIR.'/'.$this->routeRules->controller.'.controller.php';

			# CHECK IF FILE IS READABLE
			if(is_readable($controllerfile))
			{
				include $controllerfile;
				
				if (class_exists($this->routeRules->controller)) 
				{
					#INITIALIZE CONTROLLER, GIVE THE CONTROLLER CLASS A HOLD OF THE REGISTRY
					$controller_class = $this->routeRules->controller;
					$controller = new $controller_class($this->registry);
					
					if(is_callable(array($controller, $this->routeRules->action)))
					{
						$action = $this->routeRules->action;
						$this->output = call_user_func_array(array($controller, $this->routeRules->action), $this->routeRules->params);
						#MAGIC
						return $this->output;
					}
					else
					{
						#WHEN ACTION CANNOT BE CALLED BECAUSE OF USER INPUT
						if($this->isKeyword($this->routeRules->matchedRule['action']))
							$code = 404;
						else #IF BECAUSE OF DEVELOPER INPUT
							$code = 500;
						throw new Exception('Action not Implemented.', $code);
					}
				}
				else
				{
					throw new Exception('Controller Class not Implemented.', 500);
				}
			}
			else
			{
				#WHEN CONTROLLER CANNOT BE READ BECAUSE OF USER INPUT
				if($this->isKeyword($this->routeRules->matchedRule['controller']))
					$code = 404;
				else #IF BECAUSE OF DEVELOPER INPUT
					$code = 500;
					
				throw new Exception('Controller File not Implemented. ('.$controllerfile.')', $code);
			}
			
		}
		else
		{
			#WHEN NO RULE MATCHES WITH REQUEST_URI
			throw new Exception('Page not found. No Rule Matched.', 404);
		}
		
	}
}

?>