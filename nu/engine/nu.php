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
define('CACHEDIR', 	NUDIR.'/cache');

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
include ENGINEDIR.'/cache.class.php';

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
	
	public function run()
	{
		
		try
		{
			/**
			 * Loading Developer Configurations
			 */
			$this->loadConfig();
			$this->loadRoutes();
			
			$this->config = new Config();
			$this->registry->set('config', $this->config);
			
			/**
			 * turns on error_reporting if set to true in developer's _config.php file.
			 */
			if($this->config->debug) { error_reporting($this->config->debugLevel); }
			else { error_reporting(0); }
			
			/**
			 * Registering the request instance
			 */
			$this->registry['request'] = new Request($this->config);
			
			/**
			 * THROWS MAINTENANCE EXCEPTION (HTTP/1.1 503 Service Unavaible)
			 * if MAINTENANCE is true in developer's config file.
			 */
			if($this->config->maintenance) throw new Exception('SITE ON MAINTENANCE', 503);
			
			/**
			 * Setting up the route
			 */
			if(isset($this->override))
			{
				#WHEN NU IS OVERRIDEN
				$this->routeRules = new RouteRules($this->registry, true);
				
				$this->routeRules->setController($this->override['controller']);
				$this->routeRules->setAction($this->override['action']);
				
				if(isset($this->override['params']))
					$this->routeRules->setParams($this->override['params']);
			}
			else
			{
				#NORMAL WAY
				$this->routeRules = new RouteRules($this->registry);
			}
			
			/**
			 * Caching Pre-conditions
			 * do not cache when there is no route match
			 * the matched rule should implements caching
			 */
			$isCacheable = ($this->routeRules->match
							&& $this->config->useCaching
							&& ($this->routeRules->matchedRule['cache'] == true 
							|| is_array($this->routeRules->matchedRule['cache'])));
			
			#EXECUTE ANY CACHING
			if($isCacheable)
			{
				#die(md5($_SERVER['REQUEST_URI']));
				$this->cache = new Cache(md5($_SERVER['REQUEST_URI']), $this->config->cacheLifeTime);
				if($this->cache->valid)
				{
					$this->cache->outputCache();
					#End properly now that we have output.
					$this->end();
				}
				else
				{
					$this->cache->start();
				}
			}
			
			/**
			 * Creates the proper method of database connection that the
			 * developer defined in the config file.
			 */
			if($this->config->usingDB)
			{
				$DBCONN = mysql_connect($this->config->dbConn['host'], $this->config->dbConn['user'], $this->config->dbConn['pass']);
				mysql_select_db($this->config->dbConn['name']);
				$this->registry->set('db', $DBCONN);
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
				$this->registry->set('memcache', $MEMCACHE);
			}
					
			
			/**
			 * Registering the final delegate instructions,
			 */
			$this->registry['route'] = $this->routeRules->getRoute();
			
			
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
			 * Creating the Router instance, passing final registry instance
			 * NO MORE CHANGES TO THE REGISTRY AFTER THIS LINE
			 */
			$this->router = new Router($this->registry);
			
			if(isset($this->override))
				$this->router->overrideRules = true;
			 
			/**
			 * Executing the request.
			 */
			$this->output = $this->router->execute();
			
		}
		catch(Exception $e)
		{			
			$this->registry['exception'] = $e;
			
			$code = $this->registry['exception']->getCode();
			
			# Store the Original Route
			$this->registry['oRoute'] = $this->registry['route'];
			
			$this->registry->remove('route');
			
    		$this->registry['route'] = array('match'		=> true,
    										 'controller'	=> $this->config->exceptionController,
											 'action'		=> isset($this->config->exceptionCodes[$code]) ? 
											 					$this->config->exceptionCodes[$code] : 
											 					$this->config->exceptionCodes[500],
											 'params'		=> array());
    		
			$this->eRouter = new Router($this->registry);
			$this->eRouter->overrideRules = true;
			
			$this->output = $this->eRouter->execute();
		
		}
		
		/**
		 * Love.
		 */
		if(isset($this->output))
			$this->output->render();
			
		#STORE CACHE IF CACHEABLE
		if($isCacheable)
			if(!$this->cache->valid)
				$this->cache->end();
		/**
		 * All's well that ends well.
		 */
		$this->end();
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
	
	private function end()
	{
		if(ob_get_contents() != false)
		{
			ob_end_flush();			
		}
		flush();
		exit(0);
	}
}
?>