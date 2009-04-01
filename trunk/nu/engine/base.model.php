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
	 * DB variable holds the handle for the db connection.
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
     * intialize is called on construction of instance, should be developer-defined.
     *
     * @abstract function initialize
	 * @access public
     **/
    abstract function initialize();
    
} //END abstract class base_model

?>