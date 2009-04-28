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
include ENGINEDIR.'/request.class.php';

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
	public $routeRules;
	
	public $eRouter;
	public $eRouteRules;
	
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
			
			$this->config = new Config();
				
			$this->registry->set('config', $this->config);
			
			$this->registry['request'] = new Request($this->config->domain);

			/**
			 * loads plugins defined
			 * @uses variable mixed $PLUGINS defined at developer's _config.php
			 * @uses CoreLib::load_plugin defined at core.lib.php
			 */
			foreach($this->config->plugins as $plugin)
			{
				$this->loadPlugin($plugin);
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
			
			
			/**
			 * Creating the RouterRules instance
			 */
			$this->routeRules = new RouteRules($this->registry);
			
			#OVERRIDE ROUTES IF SET
			if($override = isset($this->override))
			{
				$this->routeRules->setController($this->override['controller']);
				$this->routeRules->setAction($this->override['action']);
				
				if(isset($this->override['params'])) 
					$this->routeRules->setParams($this->override['params']);
			}
			
			$finalRoute = $this->routeRules->getRoute();
			
			/**
			 * Registering the final delagate instructions,
			 */
			$this->registry['route'] = $finalRoute;
			
			/**
			 * Creating the Router instance, passing final registry instance
			 * NO MORE CHANGES TO THE REGISTRY AFTER THIS LINE
			 */
			$this->router = new Router($this->registry);
			
			if($override)
				$this->router->overrideRules = true;
			 
			/**
			 * Executing the request.
			 */
			$this->output = $this->router->execute();
		}
		catch(Exception $e)
		{
			$this->registry['exception'] = $e;
			
			$this->registry->remove('route');
			
    		$this->registry['route'] = array('match'		=> true,
    										 'controller'	=> $this->config->exceptionController,
											 'action'		=> $this->config->exceptionCodes[$this->registry['exception']->getCode()],
											 'params'		=> array());
    		
			$this->eRouter = new Router($this->registry);
			$this->eRouter->overrideRules = true;
			
			$this->output = $this->eRouter->execute();
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
	
	public function setConfig($configFile)
	{
		$this->configFile = CONFIGDIR.'/'.$configFile.'.php';
	}
	
	public function setRoutes($routesFile)
	{
		$this->routesFile = ROUTESDIR.'/'.$routesFile.'.php';
	}
	
	private function loadConfig()
	{
		if(is_readable($this->configFile))
		{
			include $this->configFile;
		}
		else
		{
			echo $this->configFile;
			throw new Exception('APPLICATION ERROR: 1', 500);
		}
	}
	
	private function loadRoutes()
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