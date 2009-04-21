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
    	$this->headers = array();
    	$this->view = new View($registry);
    }
    
    public function addHeader($string)
    {
    	$this->headers[] = $string;
    }
    
    public function output($mode = 'i')
    {
    	$output = new Output();	
    	$output->headers = $this->headers;
    	$output->view = $this->view;
    	$output->mode = $mode;
    	return $output;
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