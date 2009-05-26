<?php 

class Home extends BaseController
{
	function index()
	{
		$message = 'Start by editing this view file, located at:<br /><br /><code>./nu/application/_view/home/index.view.php</code>';
		$this->view->addData('message', $message);
		return $this->output();
	}
}

?>