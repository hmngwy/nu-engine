<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();
	
class Output
{
	public $headers = array();
	public $view;
	public $mode;
	
	public function render()
	{
		foreach($this->headers as $header)
			header($header);
			
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
	}
}

?>