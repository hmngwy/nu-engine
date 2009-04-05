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
abstract class BaseController extends CoreLib {
	/**
	 * registry variable contains global data for every request.
	 *
	 * @access protected
 	 * @see registry.class.php
	 * @var class registry
	 **/
    protected $registry;
	
	/**
	 * path to view file corresponding to request
	 *
	 * @access public
	 **/
    public $viewFile;
    
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
    function __construct($registry = false) 
    {
    	if($registry !== false)
    	{
    		$this->registry = $registry;
    		$this->viewFile = $this->viewPath($this->registry['action']);
    	}
    }
    
    /**
     * view function returns corresponding view file for requested action.
     *
     * If param $view_name == 'default' (or empty), file loaded wil be equal to 
     * '_view/[controller_name]/[action_name].view.html', and if overriden 
     * '_view/[controller_name]/[$view_name].view.html'.
     *
     * If global is set to true, requested view file will be under _global, 
     * like so '_view/_global', instead of [controller_name]. Otherwise, it uses
     * [controller_name].
     * 
	 * @access public
 	 * @param string $view_name
 	 * @param bool $global
 	 * @return string
 	 * @throws 500 view not implemented
     **/    
    function viewPath($view_name = 'default', $global = false)
    {    	
	    if($view_name==='default')
	    {
	    	$view_name = $this->registry['action'];
	    }
	    
	    if($global === false)
	    {
		    $view_subdir = $this->registry['controller'];
	    }
	    else
	    {
		    $view_subdir = '_global';
	    }
	    
	    $viewPath = VIEWDIR.'/'.$view_subdir.'/'.$view_name.'.view.html';
	    
	    if(!is_file($viewPath))
	    {
	    	$viewPath = '';
	    }
	    
	    return $viewPath;
    }
    
    function renderView($view_name = false, $global = false)
    {
    	if($view_name !== false && $global!==false)
    		$viewPath = $this->viewPath($view_name, $global);
    	else
    		$viewPath = $this->viewFile;
    		
    	if (is_file($viewPath)) {
    		return file_get_contents($viewPath);
    	}
    	else
    	{
    		throw new Exception('View does not exist.', 500);
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