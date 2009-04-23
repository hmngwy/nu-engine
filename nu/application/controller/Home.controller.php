<?php 

class Home extends BaseController
{
	function index()
	{
		$this->view->addData('message', 'Start by editing this view file, located at:<br /><br /> <code>./nu/application/_view/home/index.view.html</code>');
		return $this->output();
	}
}

?>