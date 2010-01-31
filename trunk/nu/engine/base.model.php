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
abstract class BaseModel extends CoreLib {
	/**
	 * registry variable contains global data for every request.
	 *
	 * @access protected
 	 * @see registry.class.php
	 * @var class registry
	 **/
    protected $registry;
    
	/**
	 * DB variable holds the handle for the db connection.
	 *
	 * @access protected
	 **/
    protected $DB;

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
    function __construct($registry) {
    	$this->registry = $registry;
    	$this->DB = $registry['db'];
    }
	
	function create()
	{
		$columns = '`'.implode('`,`', $this->insertInto).'`';
		
		$values = array();
		foreach($this->insertInto as $i)
		{
			$values[] = (is_numeric($this->$i)) ? $this->$i : '\''.$this->$i.'\'' ;
		}
		
		$values = implode(',', $values);
		
		
		$sql = "INSERT INTO `".$this->tableName."`(".$columns.") VALUES (".$values.")";
		#die($sql);
		mysql_query($sql);
		
		
		$this->ID = mysql_insert_id();
		
		return $this->ID;
	}
	
	function read($colconstraint, $more_constraints=false)
	{
		$sql = "SELECT * FROM `".$this->tableName."` WHERE `".$colconstraint."`='".$this->$colconstraint."'";
		if($more_constraints!=false)
		{
			$sql .= " ".$more_constraints;
		}
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		
		foreach($this->columnNames as $column)
		{
			$this->$column = $row[$column];
		}
		
		return mysql_num_rows($res);
	}
	
	function update($column, $colconstraint=false)
	{
		if($colconstraint==false)
		{
			$defConstraint = $this->columnNames[0];
			mysql_query("UPDATE `".$this->tableName."` SET `".$column."`='".$this->$column."' WHERE `".$defConstraint."`=".$this->$defConstraint);
		}
		else
		{
			mysql_query("UPDATE `".$this->tableName."` SET `".$column."`='".$this->$column."' WHERE `".$colconstraint."`=".$this->$colconstraint);
		}
		
	}
	
	function delete($column, $more_constraints=false)
	{
		$sql = "DELETE FROM `".$this->tableName."` WHERE $column=".$this->$column;
		
		if($more_constraints!=false)
		{
			$sql .= " ".$more_constraints;
		}
		
		mysql_query($sql) or die(mysql_error());
	}
    
} //END abstract class base_model

?>