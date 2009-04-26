<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();
	
class Request extends CoreLib
{
	public $request_uri;
	public $method;
	
	public function __construct()
	{
		$this->request_uri = $this->parse_request_uri();
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
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
}

?>