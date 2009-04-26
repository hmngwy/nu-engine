<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();
	
/**
 * class BaseRoutes will be the base class for the 
 * developer defined routes class.
 * 
 * @abstract abstract class BaseRoutes
 * @access public
 * @author pat ambrosio <cp.ambrosio@gmail.com>
 * @package nu
 * @version 1.0
 **/

abstract class BaseRouteRules extends CoreLib
{
	private $registry;
	public $rules;
	
	public $match;
	public $controller;
	public $action;
	public $params;
	public $matchedRule;
	
	public function __construct($registry)
	{
		$this->registry = $registry;
		$this->match = false;
		$this->controller = false;
		$this->action = false;
		$this->arguments = false;
		$this->matchedRule = false;
		$this->initialize();
	}
	
	abstract function initialize();
	
	public function addRoute($rule)
	{
		if(!isset($rule['methods'])) $rule['methods'] = 'all';
		$this->rules[] = $rule;
		
		#CHECK IF MATCH
		if($this->match!==true)
			$this->match = $this->isMatch($rule);
	}
	
	public function remRoute($handle)
	{
		unset($this->rules[$handle]);
	}
	
	public function setRoutes($rules)
	{
		$this->rules = $rules;
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
	
	public function setParams($arguments)
	{
		$this->params = $params;
		$this->registry->set('request_params', $params);
	}
	
	private function isMatch($rule)
	{
		$request = $this->parse_request_uri();
		$request_length = count($request);
		$method  = strtolower($_SERVER['REQUEST_METHOD']);
		
		#ELIMINATIVE METHOD
		#default true, shall be set to false once proven there is no match
		$match = true;
		
		#CHECK IF METHODS MATCH
		if($this->doesMethodMatch($method, $rule['methods']))
		{
			#CHECK IF REQUEST DIR LENGTH IS EQUAL TO RULE DIR LENGTH
			if($request_length == count($rule['url']))
			{
				
				#CHECK THE DIRS IF IT MATCHES
				for($i=0; $i<$request_length; $i++)
				{
					$request_url_item	= $request[$i];
					$rule_url_item		= $rule['url'][$i];
					
					$is_rui_keyword		= $this->isKeyword($rule_url_item);
					
					#NOT A SPECIAL KEYWORD, NOR DOES IT MATCH
					if(!$is_rui_keyword && $request_url_item != $rule_url_item)
					{
						$match = false;
						break;
					}
					
					#STORE DYNAMIC URI PARAMETERS
					if($is_rui_keyword)
					{
						#TODO EDIT HERE							
						switch ($rule_url_item) {
							case $rule['controller']:
								$this->setController($request_url_item);
								break;
							
							case $rule['action']:
								$this->setAction($request_url_item);
								break;
								
							default:
								$param_name = substr($rule_url_item, 1, count($rule_url_item)-2);
								$request_params[$param_name] = $request_url_item;
								break;
						}
					}
				}
				
			}
			else $match = false; #uri length does not match
		}
		else $match = false; #method does not match
			
		#IF MATCH FOUND
		if($match == true)
		{
			#UPDATE PROPERTY, TELL EVERYONE MATCH WAS FOUND
			$this->match = true;
			$this->matchedRule = $rule;
			
			if(!$this->isKeyword($rule['controller']))
				$this->setController($rule['controller']);
				
			if(!$this->isKeyword($rule['action']))
				$this->setAction($rule['action']);
				
			#STORE REQUEST PARAMS TO REGISTRY
			if(isset($request_params))
			{
				$this->setParams($request_params);
			}
		}
		
		return $match;
	}
	
	private function parse_request_uri()
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
	
	private function doesMethodMatch($request_method, $methods)
	{
		return (!isset($methods)
				|| $methods == 'all'
					|| ($methods != 'all' && array_search($request_method, $methods) !== false));
	}
}

?>