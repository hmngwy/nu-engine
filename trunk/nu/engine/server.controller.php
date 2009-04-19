<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();

class Server extends BaseController
{
	function index(){}
	
	function bad_request()
	{
		header('HTTP/1.1 400 Bad Request');
		include $this->viewPath('400', true);
	}
	
	function not_found()
	{
		header('HTTP/1.1 404 Not Found');
		include $this->viewPath('404', true);
	}
	
	function service_unavailable()
	{
		header('HTTP/1.1 503 Service Unavailable');
		header('Retry-After: 120');
		include $this->viewPath('503', true);
	}
	
	function internal_server_error()
	{
		header('HTTP/1.1 500 Internal Server Error');
		include $this->viewPath('500', true);
	}
	
	function unknown_error()
	{
		header('HTTP/1.1 500 Internal Server Error');
		include $this->viewPath('500', true);
	}
}

?>