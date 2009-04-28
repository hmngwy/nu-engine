<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();
	
/**
 * class BaseController is extended by developer-defined controllers.
 *
 * This class holds the default behaviors of a controller class,
 * and all request information from the delegator that accepts
 * and delegates the request.
 * 
 * @abstract class BaseController
 * @access public
 * @author pat ambrosio <cp.ambrosio@gmail.com>
 * @package nu
 * @version 1.0
 **/
abstract class BaseController extends CoreLib 
{
	/**
	 * registry variable contains global data for every request.
	 *
	 * @access protected
 	 * @see registry.class.php
	 * @var class registry
	 **/
    protected $registry;
    
    public $headers;
    
    public $model;
    
    public $view;
    
	/**
	 * base controller constructor sets registry variable.
	 *
	 * Developer does not need to use this, this is called by the
	 * delegator and passes the registry class itself.
	 *
	 * @access public
 	 * @param class registry $registry
 	 * @return void
	 **/
    public function __construct($registry) 
    {
    	$this->registry = $registry;
    	$this->config = $this->registry['config'];
    	$this->headers = array();
		
    	$this->view = new View($registry);
    	$this->model = new stdClass();
    	$this->helper = new stdClass();
    	
		foreach($this->config->helpers as $helper)
		{
			$this->load_helper($helper);
		}
    }
    
    public function addHeader($string)
    {
    	$this->headers[] = $string;
    }
    
    public function output($mode = 'i')
    {
    	$output = new Output($this->registry);	
    	$output->headers = $this->headers;
    	$output->view = $this->view;
    	$output->mode = $mode;
    	return $output;
    }
	
	public function loadModel($name, $handle=false)
	{
		include MODELDIR.'/'.$name.'.model.php';
		if($handle === false)
		{
			$this->model->$name = new $name();			
		}
		else
		{
			$this->model->$handle = new $name();	
		}
	}
	
	public function loadModels()
	{
		$d = dir(MODELDIR); 
		while (false !== ($filename = $d->read())) { 
			 if (($modelname = substr($filename, -10)) == '.model.php') { 
			 	$this->loadModel($modelname);
			 } 
		} 
		$d->close();
	}
	
	public function loadHelper($name, $handle=false)
	{
		include_once HELPERDIR.'/'.$name.'/'.$name.'.helper.php';
		
		if($handle === false)
		{
			$this->helper->$name = new $name();			
		}
		else
		{
			$this->helper->$handle = new $name();	
		}
	}

	/**
	 * index action is a requirement for all controllers.
	 *
	 * @abstract function index
	 * @return string
	 **/
    abstract function index();
} //END abstract class base_controller

?>