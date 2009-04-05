<?php
	
/**
* A simple and low footprint templating engine
* refer to @link for full documentation
*
* @copyright 2008 pat ambrosio
* @license http://www.opensource.org/licenses/mit-license.php
* @version Release: @0.2.3@
* @link http://code.google.com/p/mars-templater/
* @since Class available since Release 0.1
*/

class templater
{
    var $filename;
    var $filestring;
    var $vars = array();
    var $loops = array();
    var $cons = array();
    
    public function templater($filename='')
    {
        $this->filename = $filename;
        $this->filestring = ($filename != '') ? file_get_contents($this->filename) : '';
    }
    
    public function setfile($filename)
    {
        $this->templater($filename);
    }
    
    public function setfilestring($filestring)
    {
        $this->filestring = $filestring;
    }
    
    public function addvar($varname, $data)
    {
        $this->vars[$varname] = $data;
    }
    
    public function addloop($varname, $data)
    {
        $this->loops[$varname] = $data;
    }
    
    public function addcon($varname, $data)
    {
        $this->cons[$varname] = $data;
    }
    
    public function setvars($data)
    {
        $this->vars = $data;
    }
    
    public function setloops($data)
    {
        $this->loops = $data;
    }
    
    public function setcons($data)
    {
        $this->loops = $data;		
    }
    
    public function render($rules='parse all remove all')
    {
        $newfile = ' '.$this->filestring;
        $rules =  str_replace('all', 'cons vars loops', $rules);
        
        $steps = explode(' ', $rules);
        
        $w = 'parse';
        foreach($steps as $step)
        {
            $w = (strcmp($step, 'parse')===0) ? 'parse' : $w ;
            $w = (strcmp($step, 'remove')===0) ? 'remove' : $w ;
            
            switch($step)
            {
                case 'cons':
                switch($w)
                {
                    case 'parse': 
                    $newfile = (!empty($this->cons)) ? $this->parsecons($newfile) : $newfile;
                    break;
                    case 'remove': 
                    $newfile = $this->removecons($newfile);
                    break;
                }
                break;
                
                case 'vars':
                switch($w)
                {
                    case 'parse': 
                    $newfile = (!empty($this->vars)) ? $this->parsevars($newfile) : $newfile;
                    break;
                    case 'remove': 
                    $newfile = $this->removevars($newfile);
                    break;
                }
                break;
                
                case 'loops':
                switch($w)
                {
                    case 'parse': 
                    $newfile = (!empty($this->loops)) ? $this->parseloops($newfile) : $newfile;
                    break;
                    case 'remove': 
                    $newfile = $this->removeloops($newfile);
                    break;
                }
                break;
                
                case 'none' : ; case '' : ; default : ; break;
            }
            
        }
        
        return ltrim($newfile, ' ');
    }
    
    
    /*
    
    THE FOLLOWING CAN CAUSE YOU MUCH CONFUSION, MIND ME.
    
    */
    
    private function removecons($filestring)
    {
        $filecons = $filestring;
        
        $pos = -1;
        while($pos!==false){
            $pos = strpos($filecons, '{con:', $pos+1);
            if($pos !== false)
            {
                $tagend = strpos($filecons, '}', $pos);
                $key = substr($filecons, $pos+5, $tagend-$pos-5); //parse Key from found con
                $tplconend = strpos($filecons, '{/con:'.$key.'}', $tagend ); //end of the text to be con
                $endtaglength = strlen('{/con:'.$key.'}');
                
                $filecons = substr_replace($filecons, '', $pos, $tplconend+$endtaglength-$pos);
            }
            
        }
        return $filecons;
    }
    
    private function removeloops($filestring)
    {
        $fileloops = $filestring;
        
        $pos = -1;
        while($pos!==false){
            $pos = strpos($fileloops, '{loop:', $pos+1);
            if($pos !== false)
            {
                $tagend = strpos($fileloops, '}', $pos);
                $key = substr($fileloops, $pos+6, $tagend-$pos-6); //parse Key from found loop
                $tplloopend = strpos($fileloops, '{/loop:'.$key.'}', $tagend ); //end of the text to be loop
                $endtaglength = strlen('{/loop:'.$key.'}');
                
                $fileloops = substr_replace($fileloops, '', $pos, $tplloopend+$endtaglength-$pos);
            }
            
        }
        return $fileloops;
    }
    
    private function removevars($filestring)
    {
        $filevars = $filestring;
        
        $pos = -1;
        while($pos!==false){
            $pos = strpos($filevars, '{', $pos+1);
            if($pos !== false)
            {
                $tagend = strpos($filevars, '}', $pos);
                $var = substr($filevars, $pos+1, $tagend-$pos-1); //parse Key from found var
                //echo $var,'<br />';
				
                if(strpos($var, ' ', 0)===false && strpos($var, "\n", 0)===false && strlen($var)>0)
                {
	                if(strpos($var, 'con:', 0)!==false || strpos($var, 'loop:', 0)!==false )
	                {
	                    if(strpos($var, 'con:', 0)!==false)
	                    {
	                        $key = substr($filevars, $pos+5, $tagend-$pos-5);
	                        $tplconend = strpos($filevars, '{/con:'.$key.'}', $tagend );
	                        $pos = $tplconend + strlen('{/con:'.$key.'}');
	                    }
	                    if(strpos($var, 'loop:', 0)!==false)
	                    {
	                        $key = substr($filevars, $pos+6, $tagend-$pos-6);
	                        $tplloopend = strpos($filevars, '{/loop:'.$key.'}', $tagend );
	                        $pos = $tplloopend + strlen('{/loop:'.$key.'}');
	                    }
	                }else{
	                    $filevars = substr_replace($filevars, '', $pos, $tagend-$pos+1);
	                    //echo $pos,' hey ',$tagend-$pos;
	                    //echo substr($filevars, $pos, $tagend-$pos+1);
	                }
	                //*/
            	}
            }
            
        }
        return $filevars;
    }
    //-------------------------------------------------------------------------------------------------//
    
    private function parsecons($filestring)
    {	
        $filecons = $filestring;
        
        $conkeys = array_keys($this->cons);
        $pos = -1;
        while($pos!==false){
            $pos = strpos($filecons, '{con:', $pos+1);
            if($pos !== false)
            {
                $tagend = strpos($filecons, '}', $pos);
                $key = substr($filecons, $pos+5, $tagend-$pos-5); //parse Key from found con
                $tplconend = strpos($filecons, '{/con:'.$key.'}', $tagend ); //end of the text to be con
                $endtaglength = strlen('{/con:'.$key.'}');
                if($this->cons[$key]===false)
                {
                    //erase conditional block
                    $filecons = substr_replace($filecons, '', $pos, $tplconend+$endtaglength-$pos);
                }else if($this->cons[$key]===true){
                    //erase tags
                    $filecons = substr_replace($filecons, '', $tplconend, $endtaglength);
                    $filecons = substr_replace($filecons, '', $pos, $tagend-$pos+1);
                }
            }
        }
        return $filecons;
    }
    
    private function parsevars($filestring)
    {
        
        $keys = array_keys($this->vars);
        $newkeys = array();
        foreach($keys as $key)
            $newkeys[] = '{'.$key.'}';
    	
        $taggifiedarray = array_combine($newkeys, array_values($this->vars));
        
        $filevars = str_replace(array_keys($taggifiedarray),
                                array_values($taggifiedarray),
                                $filestring);
        return $filevars;
    }
    
    private function parseloops($filestring)
    {
        $fileloops = $filestring;
        $loopkeys = array_keys($this->loops);
        
        $pos = -1;
        while($pos!==false){
            $pos = strpos($fileloops, '{loop:', $pos+1);
            if($pos !== false)
            {
                $tagend = strpos($fileloops, '}', $pos);
                $key = substr($fileloops, $pos+6, $tagend-$pos-6); //parse Key from found loop
                $tplloopstart = $tagend+1; //start of the text to be looped
                $tplloopend = strpos($fileloops, '{/loop:'.$key.'}', $tagend ); //end of the text to be looped
                
                $tplloop = substr($fileloops, $tplloopstart, $tplloopend-$tplloopstart); //the text to be looped
                $parse = '';
                
                if(!empty($this->loops[$key])){ //do below if key exists in array loops
                    $tags = array();
                    foreach($this->loops[$key] as $row){
                        $keys = array_keys($row);
                        foreach($keys as $k){
                            $tags[] = '{'.$k.'}';
                        }
                        $tags = array_unique($tags);
                        $parse .= str_replace($tags, array_values($row), $tplloop);
                    }
                    $fileloops = substr_replace($fileloops, $parse, $pos, $tplloopend+strlen('{/loop:'.$key.'}')-$pos);
                }
            }
        }
        
        return $fileloops;
    }
    
}

?>