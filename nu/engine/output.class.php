<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();
	
class Output
{
	public $headers = array();
	public $view;
	public $mode;
	
	public function __construct($registry)
	{
		$this->registry = $registry;
	}
	
	public function flushHeaders()
	{
		foreach($this->headers as $header)
			header($header);
	}
	
	public function render()
	{
		$this->flushHeaders();
		
		ob_start();
		
		switch($this->mode)
		{
			case 'e':
				echo $this->view->content;
				break;
				
			case 'i':
			default:
				$data = $this->view->data;
				include $this->view->path;
				break;
		}
		
		$outputPath		= CACHEDIR.'/'.md5($_SERVER['REQUEST_URI']).'.html';
		$outputString	= ob_get_contents();
		
		$fp = fopen($outputPath, 'w'); 
		fwrite($fp, $outputString); 
		fclose($fp);
		
		ob_end_flush();
	}
}

?>