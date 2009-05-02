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
	 * sets error reporting level.
	 * set this under the contsruction function, apparently 
	 * bits and property definitions don't mix well.
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
	 * sets file caching on routes that implement it
	 *
	 * @access public
	 * @var boolean
	 **/
	public $useCaching = true;
	
	/**
	 * cache lifetime (seconds)
	 *
	 * @access public
	 * @var integer
	 **/
	public $cacheLifeTime = 5;
	
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
	
	public $exceptionController = 'Server';
	
	public $exceptionCodes = array( 400 => 'bad_request',
									404 => 'not_found',
									503 => 'service_unavailable',
									500 => 'internal_server_error');
	
	public function __construct()
	{	
		#YOU CAN WRITE ANYTHING HERE
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