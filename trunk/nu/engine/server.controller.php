<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();

class Server extends BaseController
{
	function index(){}
	
	function bad_request()
	{
		$this->addHeader('HTTP/1.1 400 Bad Request');
		
		$this->view->load('Exception', true);
		
		$this->view->addData('title', '400 Bad Request');
		$this->view->addData('subject', 'There is an error in your request');
		$this->view->addData('body', 'Your request is invalid, please check your inputs or address bar, use <a href="/">this link</a> to get back home.');
		$this->view->addData('debug', $this->registry['config']->debug);
		$this->view->addData('registry', $this->registry);
		
		return $this->output();
	}
	
	function not_found()
	{		
		$this->addHeader('HTTP/1.1 404 Not Found');
		
		$this->view->load('Exception', true);
		
		$this->view->addData('title', '404 Not Found');
		$this->view->addData('subject', 'Page Not found');
		$this->view->addData('body', 'The page you requested does not exist, use <a href="/">this link</a> to get back home.');
		$this->view->addData('debug', $this->registry['config']->debug);
		$this->view->addData('registry', $this->registry);
		
		return $this->output();
	}
	
	function service_unavailable()
	{
		$this->addHeader('HTTP/1.1 503 Service Unavailable');
		$this->addHeader('Retry-After: 120');
		
		$this->view->load('Exception', true);
		
		$this->view->addData('title', '503 Service Unavailable');
		$this->view->addData('subject', 'Undergoing Maintenance');
		$this->view->addData('body', 'We are making some changes to the server, please check back again later.');
		$this->view->addData('debug', $this->registry['config']->debug);
		$this->view->addData('registry', $this->registry);
		
		return $this->output();
	}
	
	function internal_server_error()
	{		
		$this->addHeader('HTTP/1.1 500 Internal Server Error');
		
		$this->view->load('Exception', true);
		
		$this->view->addData('title', '500 Internal Server Error');
		$this->view->addData('subject', 'Error Encountered');
		$this->view->addData('body', 'The page you requested has errors, use <a href="/">this link</a> to get back home.');
		$this->view->addData('debug', $this->registry['config']->debug);
		$this->view->addData('registry', $this->registry);
		
		return $this->output();
	}
	
	function unknown_error()
	{
		$this->addHeader('HTTP/1.1 500 Internal Server Error');
		
		$this->view->load('Exception', true);
		
		$this->view->addData('title', '500 Internal Server Error');
		$this->view->addData('subject', 'Error Encountered');
		$this->view->addData('body', 'Woah! Well no one expected that. The page you requested has errors, use <a href="/">this link</a> to get back home while we inform the developers.');
		$this->view->addData('debug', $this->registry['config']->debug);
		$this->view->addData('registry', $this->registry);
		
		return $this->output();
	}
}

?>