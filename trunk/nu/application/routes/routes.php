<?php

class Routes
{
	public $rules = array(
		
		array(
			'url' => array(''),
			'controller' => 'Home',
			'action' => 'index'	
		),
		
		array(
			'url' => array('{controller}', '{action}')
		)
		
	);
}

?> 