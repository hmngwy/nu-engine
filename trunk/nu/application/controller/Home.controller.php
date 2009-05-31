<?php 

class Home extends BaseController
{
	function index()
	{		
		return $this->output();
	}
	
	function test()
	{
		$this->view->setContent('testSuccess');
		return $this->output('e');
	}
}

?>