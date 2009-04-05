<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();

/**
 * class Nu loads and runs your application
 *
 * @access public
 * @author pat ambrosio <cp.ambrosio@gmail.com>
 * @package nu
 * @version 1.0
 **/
class Nu
{
	private $configFile;
	private $routesFile;
	
	private $config;
	private $routes;
	
	public $registry;
	
	public function __construct()
	{
		/**
		 * hopefully disables register_globals.
		 */
		ini_set('register_globals', 0);
		
		/**
		 * turns off error reporting
		 */
		error_reporting(0);
		
		/**
		 * defines ENGINE directories.
		 */
		define('ENGINEDIR', NUDIR.'/engine');
		define('PLUGINDIR', NUDIR.'/plugins');
		define('HELPERDIR', NUDIR.'/helpers');
		define('APPDIR', 	NUDIR.'/application');
		
		/**
		 * defines developer directoriues.
		 */
		define('CONTROLLERDIR', APPDIR.'/controller');
		define('MODELDIR', 		APPDIR.'/model');
		define('VIEWDIR', 		APPDIR.'/view');
		define('ROUTESDIR', 	APPDIR.'/routes');
		define('CONFIGDIR', 	APPDIR.'/config');
		
		$this->setConfig('config');
		$this->setRoutes('routes');
		
		/**
		 * LOAD ENGINE LIBRARIES
		 */
		include ENGINEDIR.'/core.lib.php';
		include ENGINEDIR.'/router.class.php';
		include ENGINEDIR.'/registry.class.php';
		include ENGINEDIR.'/base.controller.php';
		include ENGINEDIR.'/base.model.php';
			
		/**
		 * Creating the registry instance that will be passed to the router.
		 */
		$this->registry = new registry();
		
	}
	
	public function run(){
		
		try
		{
			/**
			 * Loading Developer Configurations
			 */
			$this->loadConfig();
			$this->loadRoutes();
			
			/**
			 * loads plugins defined
			 * @uses variable mixed $PLUGINS defined at developer's _config.php
			 * @uses CoreLib::load_plugin defined at core.lib.php
			 */
			foreach($this->config->plugins as $plugin)
			{
				CoreLib::load_plugin($plugin);
			}
			
			/**
			 * loads helpers defined
			 * @uses variable mixed $helpers defined at developer's _config.php
			 * @uses CoreLib::load_helper defined at core.lib.php
			 */ 
			foreach($this->config->helpers as $helper)
			{
				CoreLib::load_helper($helper);
			}
			
			/**
			 * turns on error_reporting if set to true in developer's _config.php file.
			 */
			if($this->config->debug) { error_reporting($this->config->debugLevel); }
			else { error_reporting(0); }
			
			/**
			 * THROWS MAINTENANCE EXCEPTION (HTTP/1.1 503 Service Unavaible)
			 * if Constant MAINTENANCE is true in developer's config file.
			 */
			if($this->config->maintenance) throw new Exception('SITE ON MAINTENANCE', 503);
			
			/**
			 * Creates the proper method of database connection that the
			 * developer defined in the config file.
			 */
			if($this->config->usingDB && $this->config->usingPDO)
			{
				$DBCONN = new PDO('mysql:host='.$this->config->dbHost.';dbname='.$this->config->dbName, $this->config->dbUser, $this->config->dbPassword);
				if($this->config->debug==true) $DBCONN->setAttribute(2, 1);
			}
			else if($this->config->usingDB && !$this->config->usingPDO)
			{
				$DBCONN = mysql_connect($this->config->dbHost, $this->config->dbUser, $this->config->dbPassword);
				mysql_select_db($this->config->dbName);
			}
			
			/**
			 * Creates a MEMCACHE instance and connection if Constant
			 * USING_MEMCACHE is set to true in developers config file.
			 */
			if($this->config->usingMemcache)
			{
				#MEMCACHE INITIALIZATION
				$MEMCACHE = new Memcache;
				$MEMCACHE->connect($this->config->memcacheHost, $this->config->memcachePort);
			}
			
			/**
			 * Storing Connections to the Registry
			 */
			if($this->config->usingDB)
				$this->registry->set('db', $DBCONN);
				
			if($this->config->usingMemcache)
				$this->registry->set('memcache', $MEMCACHE);
				
			$this->registry->set('config', $this->config);
			/**
			 * Creating the router instance, and passing registry instance.
			 */
			$router = new router($this->registry);
		
			/**
			 * Passing the router rules
			 */
			$router->set_rules($this->routes->rules);
				
			/**
			 * Executing the request.
			 */
			$router->execute();
			
			/**
			 * Fetching the result.
			 */
			$this->output = $router->fetch_result();
			
		}
		catch(Exception $e)
		{
			
			$this->registry->set('exception', $e);
			
			include ENGINEDIR.'/server.controller.php';			
			$server = new Server($this->registry);
			
			switch($e->getCode())
			{
				case '400': 
					/**
					 * If request parameters, controller, action, etc. does not exist.
					 */
					$this->output = $server->bad_request();
					break;
					
				case '404': 
					/**
					 * If request parameters, controller, action, etc. does not exist.
					 */
					$this->output = $server->not_found();
					break;
					
				case '503':
					/**
					 * If site is on maintenance.
					 */
					$this->output = $server->service_unavailable();
					break;
				
				case '500':
					/**
					 * If an anticipated error occured, usually thrown on purpose.
					 */
					$this->output = $server->internal_server_error();
					#echo '<br /><pre>'.print_r($e).'</pre>';
					break;
					
				default: 
					/**
					 * If an error occurs that is beyond the developer's awareness.
					 */
					$this->output = $server->unknown_error();
					#echo '<br /><pre>'.print_r($e).'</pre>';
					break;
			}
			
		}
		
		#TODO: make an output manager
		/**
		 * Echo's output if string.
		 */
		if (is_string($this->output) === true)
			echo $this->output;
		/**
		 * Ending the Request.
		 */
		flush();
	}
	
	public function setConfig($configFile)
	{
		$this->configFile = CONFIGDIR.'/'.$configFile.'.php';
	}
	
	public function setRoutes($routesFile)
	{
		$this->routesFile = ROUTESDIR.'/'.$routesFile.'.php';
	}
	
	public function loadConfig()
	{
		if(is_readable($this->configFile))
		{
			include $this->configFile;
			$this->config = new Config();
		}
		else
		{
			echo $this->configFile;
			throw new Exception('APPLICATION ERROR: 1', 500);
		}
	}
	
	public function loadRoutes()
	{
		if(is_readable($this->routesFile))
		{
			include $this->routesFile;
			$this->routes = new Routes();
		}
		else
		{
			throw new Exception('APPLICATION ERROR: 2', 500);
		}
	}
	
	
}
?>