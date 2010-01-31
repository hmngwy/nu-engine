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
    
    public $tuple;
    
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
			$this->loadHelper($helper);
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
	
	public function loadModel($name, $instanciate = false, $params=false, $handle=false)
	{
		include_once MODELDIR.'/'.$name.'.model.php';
		
		if($instanciate == true)
		{
			if($handle === false)
			{
				$this->model->$name = new $name($this->registry);
				$instance = $name;			
			}
			else
			{
				$this->model->$handle = new $name($this->registry);	
				$instance = $handle;
			}
			
			$params[] = $this->registry;
			call_user_func_array(array($this->model->$instance, $instance), $params);
			
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
	
	public function loadTuple($name, $instanciate = false, $params=false, $handle=false)
	{
		include_once MODELDIR.'/'.$name.'.tuple.php';
		
		if($instanciate == true)
		{
			if($handle === false)
			{
				$this->tuple->$name = new $name($this->registry);
				$instance = $name;			
			}
			else
			{
				$this->tuple->$handle = new $name($this->registry);	
				$instance = $handle;
			}
			
			$params[] = $this->registry;
			call_user_func_array(array($this->tuple->$instance, $instance), $params);
			
		}
		
	}
	
	public function loadTuples()
	{
		$d = dir(MODELDIR); 
		while (false !== ($filename = $d->read())) { 
			 if (($modelname = substr($filename, -10)) == '.tuple.php') { 
			 	$this->loadTuple($modelname);
			 } 
		} 
		$d->close();
	}
	
	public function loadHelper($name, $handle=false, $params)
	{
		include_once HELPERDIR.'/'.$name.'/'.$name.'.helper.php';
		
		if($handle === false)
		{
			$this->helper->$name = new $name();		
			$instance = $name;				
		}
		else
		{
			$this->helper->$handle = new $name();	
			$instance = $handle;
		}
		
		if($params!=false) call_user_func_array(array($this->helper->$instance, 'initialize'), $params);
		else $this->helper->$instance->initialize();
	}

	/**
	 * index action is a requirement for all controllers.
	 *
	 * @abstract function index
	 * @return string
	 **/
    #abstract function index();
} //END abstract class base_controller

?>