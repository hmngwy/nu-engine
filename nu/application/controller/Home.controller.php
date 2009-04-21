<?php 

class Home extends BaseController
{
	function index()
	{
		$template = new templater($this->view->viewPath('index'));
		$this->view->content = $template->render();
		
		return $this->output('e');
	}
}

?>