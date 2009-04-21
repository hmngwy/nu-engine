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
	
	public $content;
	
	public function __construct($registry)
	{
		$this->registry = $registry;
		$this->setPath();
	}
	
    public function setContent($content)
    {
    	$this->content = $content;
    }
	
    
    /**
     * Data Manager Methods
     */
	function addData($key, $var) 
	{
        if (isset($this->data[$key]) == true) 
        {
                return false;
        }
        $this->data[$key] = $var;
        return true;
	}
	
	function getData($key) 
	{
        if (isset($this->data[$key]) == false) 
        {
                return null;
        }

        return $this->data[$key];
	}
	
	function removeData($var) 
	{
	    unset($this->data[$key]);
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
    public function viewPath($view_name = 'default', $global = false)
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
	    
	    #Actual Setting of Path
    	if (!is_file($viewPath)) 
    	{
    		throw new Exception('View does not exist.', 500);
		}
		else
		{
	    	return $viewPath;
		}	
    }
    
    public function setPath($view_name = 'default', $global = false)
    {
    	$this->path = $this->viewPath($view_name, $global);
    	$this->loadContent();
    }
    
    private function loadContent()
    {
    	$this->content = file_get_contents($this->path);
    }
}

?>