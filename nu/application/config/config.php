<?php 
class Config
{
	public $debug = true;
	public $debugLevel = E_ALL;
	public $maintenance = false;
	
	public $usingDB = false;
	public $usingPDO = false;
	
	public $dbHost = false;
	public $dbName = false;
	public $dbPass = false;
	public $dbUser = false;
	
	public $usingMemcache = false;
	public $memcacheHost = 'localhost';
	public $memcachePort = '11211';
	
	public $domain = 'lolcathost.com';
	
	public $plugins = array('templater');
	
	public function __construct()
	{
		
	}	
}
?>