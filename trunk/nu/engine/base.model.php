<?php

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
 * @package mars
 * @version 1.0
 **/
abstract class BaseModel {
	/**
	 * DB variable holds the handle for the mysql connection.
	 *
	 * @access protected
	 * @var class PDO
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
 	 * @param PDO $DB
	 * @return void
	 **/
    function __construct($DB) {
    	$this->DB = $DB;
    	$this->initialize();
    }
    
    /**
     * throwsException function is used to forcefull throw an exception.
     *
	 * @access public
 	 * @param PDOStatement $statement
	 * @return void
	 * @throws PDOStatement errorInfo
     **/
    function throwsException($statement)
    {
		$errorInfo = $statement->errorInfo();
		if(isset($errorInfo[1]))		
			throw new Exception($errorInfo[2]);
    }
    
    /**
     * update_column updates a single column for this model instance.
     *
	 * @access public
 	 * @param string $column_name
	 * @return void
	 * @throws PDOStatement errorInfo
     **/
    function updateColumn($column_name)
    {
		$DB = $this->DB;
		
		$this_model =  explode('_', get_class($this));
		
		$statement = $DB->prepare('UPDATE `'.$this_model[0].'` SET `'.$column_name.'`=? WHERE `ID`=?');
		$statement->execute(array($this->$column_name, $this->ID));
		
		$this->throwsException($statement);	    
    }
    
    /**
     * intialize is called on construction of instance, should be developer-defined.
     *
     * @abstract function initialize
	 * @access public
     **/
    abstract function initialize();
    
    
} //END abstract class base_model

?>