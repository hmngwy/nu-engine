<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();
	
/**
 * class BaseModel is extended by developer-defined models.
 *
 * This class holds the default behaviors of a controller class,
 * and all request information from the delegator that accepts
 * and delegates the request.
 * 
 * @abstract class BaseModel
 * @access public
 * @author pat ambrosio <cp.ambrosio@gmail.com>
 * @package nu
 * @version 1.0
 **/
abstract class BaseTuple extends BaseModel implements ArrayAccess {

	/**
	 * base model constructor sets the DB handle and intializes the instance.
	 *
	 * The initialize function is executed to prepare the instance for
	 * further queries, it is not required to do anything but is required,
	 * to be defined at the least.
	 *
	 * @access public
	 * @return void
	 **/
	function __construct($registry)
	{
		parent::__construct($registry);
	}
	
	function read($constraints=false)
	{
		$sql = "SELECT * FROM `".$this->tableName."`";
		if($constraints!=false)
		{
			$sql .= " WHERE ".$constraints;
		}
		$res = mysql_query($sql);
		
		
		while($row = mysql_fetch_object($res, $this->rowClass, array($this->registry)))
		{
			$this->vars[] = $row;
		}
		
		return mysql_num_rows($res);
	}
	
    
    
	/* ===== ARRAY ACCESS PROPERTIES AND METHODS ===== */
	/**
	 * Main array that holds the associative data.
	 */
	public $vars = array();
	
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
	function remove($key) 
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
} //END abstract class base_tuple

?>