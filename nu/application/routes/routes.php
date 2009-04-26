<?php

class RouteRules extends BaseRouteRules
{
	public function initialize()
	{
		$this->addRoute(
			array(
				'url' => array(''),
				'controller' => 'Home',
				'action' => 'index',
				'methods' => array('get', 'post')
			)
		);
		
		$this->addRoute(
			array(
				'url' => array('{controller}', '{action}'),
				'controller' => '{controller}',
				'action' => '{action}',
				'methods' => 'all'
			)
		);
	}
}

?>