<?php 

class Home extends BaseController
{
	function index()
	{
		$page = new templater($this->viewFile);
		return $page->render();
	}
}

?>