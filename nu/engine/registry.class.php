<?php

/**
 * class Registry holds all the global data needed by the application.
 *
 * An instance of this class will be passed on to the router, 
 * then to the controllers and models.
 *
 * @access public
 * @author pat ambrosio <cp.ambrosio@gmail.com>
 * @package mars
 * @version 1.0
 **/
class Registry implements ArrayAccess 
{
	/**
	 * Main array that holds the associative data.
	 */
	private $vars = array();
	
	/**
	 * Sets/Creates a variable in the array.
	 */
	function set($key, $var) 
	{
        if (isset($this->vars[$key]) == true) 
        {
                return false;
        }
        $this->vars[$key] = $var;
        return true;
	}
	
	/**
	 * Gets a variable from the array.
	 */
	function get($key) 
	{
        if (isset($this->vars[$key]) == false) 
        {
                return null;
        }

        return $this->vars[$key];
	}
	
	/**
	 * Removes a variable from the array.
	 */
	function remove($var) 
	{
	        unset($this->vars[$key]);
	}
	
	/**
	 * AAI Functions.
	 * Don't touch unless you know what you're doing.
	 */
	function offsetExists($offset) 
	{
        return isset($this->vars[$offset]);
	}
	function offsetGet($offset) 
	{
	        return $this->get($offset);
	}
	
	function offsetSet($offset, $value)
	{
	        $this->set($offset, $value);
	}
	
	function offsetUnset($offset) 
	{
	        unset($this->vars[$offset]);
	}
}

?>