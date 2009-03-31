<?php

/**
 * class CoreLib holds functions needed by the mars engine.
 * 
 * @access public
 * @author pat ambrosio <cp.ambrosio@gmail.com>
 * @package mars
 * @version 1.0
 **/
class CoreLib
{
	
	public static function parse_request()
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
	
	public static function parse_subdomain($domain)
	{
		$host = explode('.', $_SERVER['HTTP_HOST']);
		$subdomain = rtrim(str_replace($domain, '', $_SERVER['HTTP_HOST']), '.');
		$subdomain = ($subdomain == '') ? 'www' : $subdomain ;
		
		return $subdomain;
	}
	
	public static function load_model($name)
	{
		include MODELDIR.'/'.$name.'.model.php';
	}
	
	public static function load_models()
	{
		$d = dir(MODELDIR); 
		while (false !== ($filename = $d->read())) { 
			 if (substr($filename, -10) == '.model.php') { 
			 	include MODELDIR.'/'.$filename; 
			 } 
		} 
		$d->close(); 
	}
	
	public static function load_plugin($name)
	{
		include PLUGINDIR.'/'.$name.'/'.$name.'.class.php';
	}
	
	#FORWARDS A PAGE TO $location
	public static function forward_to($location, $permanent = false)
	{
		if(!$permanent) header('HTTP/1.1 302 Found');
		else header('HTTP/1.1 301 Moved Permanently');
		
		header('Location: '.$location);
	}
	
	#TODO: Move this to a separate class
	#$bool:true FORWARDS PAGE TO $loc WHEN AUTHENTICATED
	#$bool:false FORWARDS PAGE TO $loc WHEN NOT AUTHENTICATED
	public static function authenticated_redir($bool, $loc)
	{
		if(isset($_SESSION['authenticated']) && $bool)
		{
			if($_SESSION['authenticated'] === true)
			{
				core::forward_to($loc);
			}
		}
		else if(!isset($_SESSION['authenticated']) && !$bool)
		{
			core::forward_to($loc);
		}
	}
	
	#TODO: Move this to a separate class
	#REQUESTS FOR LOGIN WHEN NOT AUTHENTICATED
	public static function require_authentication($redirect_to)
	{
		if(!isset($_SESSION['authenticated']))
		{
			core::forward_to(LOGIN.'/redirect_to/'.str_replace('/', '_', $redirect_to));
		}
	}
    
}

?>