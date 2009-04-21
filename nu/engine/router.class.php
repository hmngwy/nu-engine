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
 * @access public
 * @author pat ambrosio <cp.ambrosio@gmail.com>
 * @package nu
 * @version 1.0
 * @todo document class methods.
 **/
class Router extends CoreLib
{
	public $rules = array();
	private $registry;
	public $output;

	private $match;
	
	private $controller;
	private $action;
	private $params;
	
	public $overrideRules = false;
	
	#ROUTER CLASS NEEDS THE REGISTRY SO IT CAN PASS IT ON TO THE CONTROLLER INSTANCE
	function __construct($registry)
	{
    	$this->registry = $registry;
	}
	
	function setRules($rules)
	{
		$request = $this->parse_request_uri();
		
		foreach($rules as $rule)
		{
			
			$match = true;
			
			$request_length = count($request);
			
			#CHECK IF REQUEST DIR LENGTH IS EQUAL TO RULE DIR LENGTH
			#INEQUALITY ALREADY IMPLIES THAT IT DOES NOT MATCH
			if($request_length == count($rule['url']))
			{
				
				#CHECK THE DIRS IF IT MATCHES
				for($i=0; $i<$request_length; $i++)
				{
					$request_url_item	= $request[$i];
					$rule_url_item		= $rule['url'][$i];
					
					$rui_firstchar = (isset($rule_url_item[0])) ? $rule_url_item[0]: false;
					
					if($rui_firstchar!='{' && $request_url_item != $rule_url_item)
					{
						$match = false;
						break;
					}
					
					#STORE DYNAMIC URI PARAMETERS
					if($rui_firstchar=='{')
					{
						switch ($rule_url_item) {
							case '{controller}':
								$this->controller = $request_url_item;
								break;
							
							case '{action}':
								$this->action = $request_url_item;
								break;
								
							default:
								$param_name = substr($rule_url_item, 1, count($rule_url_item)-2);
								$request_params[$param_name] = $request_url_item;
								break;
						}
					}
				}
				
			}else $match = false;
			
			if($match == true)
			{
				$this->match = true;
				
				if(!isset($this->controller))
					$this->setController($rule['controller']);
					
				if(!isset($this->action))
					$this->setAction($rule['action']);
				
				break;
			}
		
		}
		
		if($match == false)
		{
			throw new Exception('No Rule Matched.', 404);
		}
		
		#STORE REQUEST PARAMS TO REGISTRY
		if(isset($request_params))
		{
			$this->setParams($request_params);
		}
		
	}
	
	public function execute()
	{
		if($this->match === true || $this->overrideRules === true)
		{
			$controllerfile = CONTROLLERDIR.'/'.$this->controller.'.controller.php';

			# CHECK IF FILE IS READABLE
			if(is_readable($controllerfile))
			{
				include $controllerfile;
				
				if (class_exists($this->controller)) 
				{
					#INITIALIZE CONTROLLER, GIVE THE CONTROLLER CLASS A HOLD OF THE REGISTRY
					$controller_class = $this->controller;
					$controller = new $controller_class($this->registry);
					
					if(is_callable(array($controller, $this->action)))
					{
						$action = $this->action;
						$this->output = call_user_func_array(array($controller, $this->action), $this->registry['request_params']);
						return true;
					}
					else
					{
						#WHEN ACTION CANNOT BE CALLED
						throw new Exception('Action not Implemented.', 500);
					}
				}
				else
				{
					throw new Exception('Controller Class not Implemented.', 500);
				}
			}
			else
			{
				#WHEN CONTROLLER CANNOT BE READ
				throw new Exception('Controller File not Implemented. ('.$controllerfile.')', 500);
			}
			
		}
		else
		{
			#WHEN NO RULE MATCHES WITH REQUEST_URI
			throw new Exception('Page not found. No Rule Matched.', 500);
		}
		
	}
	
	public function setController($controller)
	{
		$this->controller = $controller;
		$this->registry->set('controller', $this->controller);
	}
	
	public function setAction($action)
	{
		$this->action = $action;
		$this->registry->set('action', $this->action);
	}
	
	public function setParams($params)
	{
		$this->params = $params;
		$this->registry->set('request_params', $params);
	}
	
	private static function parse_request_uri()
	{
		$request_uri = $_SERVER['REQUEST_URI'];
		
		$querypos = strpos($request_uri, '?');
		if ($querypos !== false) {
			$request_uri = substr_replace($request_uri, '', $querypos);
		}
		else
		{
			$fragmentpos = strpos($request_uri, '#');
			if ($fragmentpos !== false) {
				$request_uri = substr_replace($request_uri, '', $fragmentpos);
			}
		}
		
		$request = explode('/', $request_uri);
		array_shift($request); #REMOVE FIRST ELEMENT (always blank)
		
		$request_len = count($request);
		
		if($request_len>1) #REMOVE LAST EMPTY ELEMENT IF IT EXISTS
		if($request[$request_len-1]=='')
			unset($request[$request_len-1]);
		foreach($request as &$r) $r = strtolower($r);
		
		return $request;
	}
}

?>