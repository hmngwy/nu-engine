<?php

class Cache extends CoreLib
{	
	public function __construct($key, $cacheLifeTime)
	{
		$this->key = $key;
		$this->cacheLifeTime = $cacheLifeTime;
		$this->valid = $this->isCacheValid();
	}
	
	public function isCacheValid()
	{
		$fileExists = is_file(CACHEDIR.'/'.$this->key);
		if($fileExists)
			$fileExpired = (time() - $this->cacheLifeTime) < filemtime(CACHEDIR.'/'.$this->key);
		else 
			$fileExpired = false;
		
		return $fileExists && $fileExpired;
	}
	
	public function start()
	{
		ob_start();
	}
	
	public function end()
	{
		$outputPath		= CACHEDIR.'/'.$this->key;
		$outputString	= ob_get_contents();
		
		$fp = fopen($outputPath, 'w'); 
		fwrite($fp, $outputString); 
		fclose($fp);
		
		#ob_end_flush();
	}
	
	public function outputCache($return=false)
	{
		if($return)
		{
			return file_get_contents(CACHEDIR.'/'.$this->key);
		}
		else
		{
			include CACHEDIR.'/'.$this->key;
		}
	}
}

?>