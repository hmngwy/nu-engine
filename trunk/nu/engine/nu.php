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
include ENGINEDIR.'/view.class.php';
include ENGINEDIR.'/output.class.php';
include ENGINEDIR.'/base.model.php';
include ENGINEDIR.'/base.controller.php';
include ENGINEDIR.'/base.routerules.php';

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
	public $exceptionRouter;
	
	public function __construct($override = false)
	{		
		/**
		 * turns off error reporting
		 */
		#error_reporting(0);
			
		$this->setConfig('config');
		
		$this->setRoutes('routes');
		
		if($override !== false)
			$this->override = $override;
		
		/**
		 * Creating the registry instance that will be passed to the router.
		 */
		$this->registry = new Registry();		
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
			
			
			
			$this->routeRules = new RouteRules($this->registry);
			
			$this->registry['controller'] = $this->routeRules->controller;
			$this->registry['action'] = $this->routeRules->action;
			$this->registry['arguments'] = $this->routeRules->arguments;
			
			/**
			 * Creating the router instance, and passing registry instance.
			 */
			$this->router = new Router($this->registry);
			
			#OVERRIDE ROUTES IF SET
			if(isset($this->override['controller']) && isset($this->override['action']))
			{
				$this->router->overrideRules = true;
				
				$this->routeRules->setController($this->override['controller']);
				$this->routeRules->setAction($this->override['action']);
				
				if(isset($this->override['params'])) 
					$this->routeRules->setParams($this->override['params']);
			}
			
			
			/**
			 * Executing the request.
			 */
			$this->output = $this->router->execute($this->routeRules);
		}
		catch(Exception $e)
		{
			$this->registry->set('exception', $e);
			
			$this->exceptionRouter = new Router($this->registry);
			
			$this->outputException();
			
			$this->output = $this->exceptionRouter->execute($this->routeRules);
		}
		
		/**
		 * Love.
		 */
		$this->output->render();
		
		/**
		 * Ending the Request.
		 */
		flush();
	}
	
	public function outputException()
	{
		$this->exceptionRouter->overrideRules = true;
		
		$this->routeRules->setController('Server');
		
		switch($this->registry['exception']->getCode())
		{
			case 400: 
				/**
				 * If request parameters, controller, action, etc. does not exist.
				 */
				$this->routeRules->setAction('bad_request');
				break;
				
			case 404: 
				/**
				 * If request parameters, controller, action, etc. does not exist.
				 */
				$this->routeRules->setAction('not_found');
				break;
				
			case 503:
				/**
				 * If site is on maintenance.
				 */
				$this->routeRules->setAction('service_unavailable');
				break;
			
			case 500:
				/**
				 * If an anticipated error occured, usually thrown on purpose.
				 */
				$this->routeRules->setAction('internal_server_error');
				break;
				
			default: 
				/**
				 * If an error occurs that is beyond the developer's awareness.
				 */
				$this->routeRules->setAction('unknown_error');
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
		}
		else
		{
			throw new Exception('APPLICATION ERROR: 2', 500);
		}
	}
	
	
}
?>