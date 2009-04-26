<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();

/**
 * class CoreLib holds functions needed by the mars engine.
 * 
 * @access public
 * @author pat ambrosio <cp.ambrosio@gmail.com>
 * @package nu
 * @version 1.0
 **/
class CoreLib
{
	
	public static function parse_subdomain($domain)
	{
		$host = explode('.', $_SERVER['HTTP_HOST']);
		$subdomain = rtrim(str_replace($domain, '', $_SERVER['HTTP_HOST']), '.');
		$subdomain = ($subdomain == '') ? 'www' : $subdomain ;
		
		return $subdomain;
	}
	
	public static function load_plugin($name)
	{
		include PLUGINDIR.'/'.$name.'/'.$name.'.class.php';
	}
	
	public static function load_helper($name)
	{
		include HELPERDIR.'/'.$name.'/'.$name.'.class.php';
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
	
	public static function isKeyword($string)
	{
		if(isset($string[0]))
		{
			if($string[0] === '{')
			{
				return true;
			}
		}
		return false;
	}
}

?>