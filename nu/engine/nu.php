<?php

# disallows direct acces
if(!defined('NUDIR'))
	die();

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

/**
 * LOAD ENGINE LIBRARIES
 */
include ENGINEDIR.'/core.lib.php';
include ENGINEDIR.'/router.class.php';
include ENGINEDIR.'/registry.class.php';
include ENGINEDIR.'/base.controller.php';
include ENGINEDIR.'/base.model.php';

/**
 * class Nu loads and runs your application
 *
 * @access public
 * @author pat ambrosio <cp.ambrosio@gmail.com>
 * @package nu
 * @version 1.0
 **/
class Nu extends CoreLib
{
	private $configFile;
	private $routesFile;
	
	private $config;
	private $routes;
	
	public $registry;
	public $router;
	
	public function __construct()
	{		
		/**
		 * turns off error reporting
		 */
		error_reporting(0);
			
		$this->setConfig('config');
		
		$this->setRoutes('routes');
		
		/**
		 * Creating the registry instance that will be passed to the router.
		 */
		$this->registry = new Registry();		
	}
	
	public function run($params){
		
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
				$this->load_plugin($plugin);
			}
			
			/**
			 * loads helpers defined 
			 * @uses variable mixed $helpers defined at developer's _config.php
			 * @uses CoreLib::load_helper defined at core.lib.php
			 */ 
			foreach($this->config->helpers as $helper)
			{
				$this->load_helper($helper);
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
			if($this->config->usingDB)
			{
				$DBCONN = mysql_connect($this->config->dbConn['host'], $this->config->dbConn['user'], $this->config->dbConn['pass']);
				mysql_select_db($this->config->dbConn['name']);
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
			$this->router = new Router($this->registry);
		
			if(isset($params['controller']) && isset($params['action']))
			{
				$this->router->overrideRules = true;
				$this->router->setController($params['controller']);
				$this->router->setAction($params['action']);
				if(isset($params['params'])) $this->router->setParams($params['params']);
			}
			else #let's do this the normal way
			{
				/**
				 * Passing the router rules, this determines the controller->action(params)
				 */
				$this->router->setRules($this->routes->rules);	
			}
			
				
			/**
			 * Executing the request.
			 */
			$this->router->execute();
			
			/**
			 * Fetching the result.
			 */
			$this->output = $this->router->fetch_result();
			
		}
		catch(Exception $e)
		{
			
			$this->registry->set('exception', $e);
			$this->outputException();
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
	
	public function outputException()
	{
		include ENGINEDIR.'/server.controller.php';			
		$server = new Server($this->registry);
		
		switch($this->registry['exception']->getCode())
		{
			case 400: 
				/**
				 * If request parameters, controller, action, etc. does not exist.
				 */
				$this->output = $server->bad_request();
				break;
				
			case 404: 
				/**
				 * If request parameters, controller, action, etc. does not exist.
				 */
				$this->output = $server->not_found();
				break;
				
			case 503:
				/**
				 * If site is on maintenance.
				 */
				$this->output = $server->service_unavailable();
				break;
			
			case 500:
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