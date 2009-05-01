<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();
	
class View extends CoreLib
{
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
	public $path;
	
	public $data = array();
	
	private $content;
	
	public function __construct($registry)
	{
		$this->registry = $registry;
		$this->load();
	}
	
    public function setContent($content)
    {
    	$this->content = $content;
    }
	
    public function getContent()
    {
    	return $content;
    }
	
    
    /**
     * Data Manager Methods
     */
	public function addData($key, $var) 
	{
        if (isset($this->data[$key]) == true) 
        {
                return false;
        }
        $this->data[$key] = $var;
        return true;
	}
	
	public function getData($key) 
	{
        if (isset($this->data[$key]) == false) 
        {
                return null;
        }

        return $this->data[$key];
	}
	
	public function removeData($var) 
	{
	    unset($this->data[$key]);
	}
    
    /**
     * view function returns corresponding view file for requested action.
     *
     * If param $view_name == 'default' (or empty), file loaded wil be equal to 
     * '_view/[controller_name]/[action_name].view.php', and if overriden 
     * '_view/[controller_name]/[$view_name].view.php'.
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
    public function path($view_name = 'default', $global = false)
    {    	
	    if($view_name==='default')
	    {
	    	$view_name = $this->registry['route']['action'];
	    }
	    
	    if($global === false)
	    {
		    $view_subdir = $this->registry['route']['controller'];
	    }
	    else
	    {
		    $view_subdir = '_global';
	    }
	    
	    $viewPath = VIEWDIR.'/'.$view_subdir.'/'.$view_name.'.view.php';
	    
	    return $viewPath;
    }
    
    public function load($view_name = 'default', $global = false)
    {
    	$this->path = $this->path($view_name, $global);
    	if(is_file($this->path)) $this->loadContent();
    }
    
    private function loadContent()
    {
    	$this->content = file_get_contents($this->path);
    }
}

?>