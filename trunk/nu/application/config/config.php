<?php 

# disallows direct acces
if(!defined('NUDIR'))
	die();

class Config extends CoreLib
{
	/**
	 * turns on error reporting when true
	 *
	 * @access public
	 * @var boolean
	 **/
	public $debug = true;
	
	/**
	 * sets error reporting level
	 * set this under the contsruction function, apparently bits and property definitions don't mix well.
	 *
	 * @access public
	 * @var integer
	 **/
	public $debugLevel;
	
	/**
	 * sets the whole application to maintenance mode when true
	 *
	 * @access public
	 * @var string
	 **/
	public $maintenance = false;
	
	/**
	 * turns on db connection when true
	 *
	 * @access public
	 * @var boolean
	 **/
	public $usingDB = false;
	
	/**
	 * database connection variables
	 *
	 * @access public
	 * @var mixed
	 **/
	public $dbConn = array( 'host' => 'localhost',
							'name' => 'nuengine',
							'pass' => 'secret',
							'user' => 'root',
							'port' => '3306');
	
	/**
	 * turns on memcache connection when true
	 *
	 * @access public
	 * @var boolean
	 **/
	public $usingMemcache = false;
	
	/**
	 * memcache connection variables
	 *
	 * @access public
	 * @var mixed
	 */
	public $memcacheConn = array( 'host' => 'localhost',
								  'port' => 11211);
	
	/**
	 * the public domain where your application resides
	 *
	 * @access public
	 * @var string
	 */	
	public $domain = 'localhost';
	
	/**
	 * plugins to autoload
	 *
	 * @access public
	 * @var mixed
	 */
	public $plugins = array('templater');
	
	/**
	 * helpers to autoload
	 *
	 * @access public
	 * @var mixed
	 */
	public $helpers = array();
	
	public function __construct()
	{
		/**
		 * hopefully disables register_globals.
		 */
		ini_set('register_globals', 0);
		
		/**
		* apparently bits and property definitions don't mix well, so set your debugLevel here.
		*/
		$this->debugLevel = E_ALL;
	}
}
?>