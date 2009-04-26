<?php 

class Home extends BaseController
{
	function index()
	{
		$message = 'Start by editing this view file, located at:<p><code>./nu/application/_view/home/index.view.html</code></p>';
		$this->view->addData('message', $message);
		return $this->output();
	}
}

?>