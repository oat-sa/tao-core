<?php

error_reporting(E_ALL);

/**
 * TAO - tao/scripts/class.Runner.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 17.02.2011, 15:21:40 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage scripts
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D48-includes begin
// section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D48-includes end

/* user defined constants */
// section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D48-constants begin
// section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D48-constants end

/**
 * Short description of class tao_scripts_Runner
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage scripts
 */
abstract class tao_scripts_Runner
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute parameters
     *
     * @access protected
     * @var array
     */
    protected $parameters = array();

    /**
     * Short description of attribute inputFormat
     *
     * @access protected
     * @var array
     */
    protected $inputFormat = array();

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array inputFormat
     * @param  array options
     * @return mixed
     */
    public function __construct($inputFormat = array(), $options = array())
    {
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D4B begin
        
    	self::out("Running {$_SERVER['argv'][0]}", array('color' => 'green'));
    	
    	$this->inputFormat = $inputFormat;
    	
    	if(!$this->validateInput()){
    		self::err("Scripts stopped!", true);
    	}
    	
    	$this->preRun();
    	
    	$this->run();
    	
    	$this->postRun();
    	
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D4B end
    }

    /**
     * Short description of method validateInput
     *
     * @access private
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return boolean
     */
    private function validateInput()
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D63 begin
        
        $returnValue = true;
        
        /**
         * Parse the arguments from the command lines 
         * and set them into the parameter variable.
         * All the current formats are allowed:
         * <code>
         * php script.php -arg1 value1 -arg2 value2
         * php script.php --arg1 value1 --arg2 value2
         * php script.php -arg1=value1 -arg2=value2
         * php script.php --arg1=value1 --arg2=value2
         * php script.php value1 value2 //the key will be numeric
         * </code>
         */
        $i = 1;
        while($i < count($_SERVER['argv'])){
        	$arg = trim($_SERVER['argv'][$i]);
        	if(!empty($arg)){
        		if(preg_match("/^[\-]{1,2}\w+=(.*)+$/", $arg)){
	        		$sequence = explode('=', preg_replace("/^[\-]{1,}/", '', $arg));
	        		if(count($sequence) >= 2){
	        			$this->parameters[$sequence[0]] = $sequence[1];
	        		}
	        	}
        		else if(preg_match("/^[\-]{1,2}\w+$/", $arg)){
        			if(isset($_SERVER['argv'][$i + 1])){
	        			$key = preg_replace("/^[\-]{1,}/", '', $arg);
	        			$this->parameters[$key] = trim($_SERVER['argv'][++$i]);
        			}
	        	}
	        	else{
	        		$this->parameters[$i] = $arg;
	        	}
        	}
        	$i++;
        }
        
		//replaces shortcuts by their orginal names
		if(isset($this->inputFormat['shortcuts']) && is_array($this->inputFormat['shortcuts'])){
			foreach($this->inputFormat['shortcuts'] as $long => $short){
				if(array_key_exists($short, $this->parameters) && !array_key_exists($long, $this->parameters)){
					$this->parameters[$long] = $this->parameters[$short];
					unset($this->parameters[$short]);
				}
			}
		}
        
        //one we have the parameters, we can validate it
        if(isset($this->inputFormat['min'])){
        	$min 	= (int) $this->inputFormat['min'];
        	$found 	=  count($this->parameters);
        	if($found < $min){
        		self::err("Invalid parameter count: $found parameters found ($min expected)");
        		$returnValue = false;
        	}
        }
        
        if(isset($this->inputFormat['required'])){
        	
        	if(!is_array($this->inputFormat['required'])){
        		$required = array($this->inputFormat['required']);
        	}
        	else{
        		$required = $this->inputFormat['required'];
        	}
        	
        	foreach($required as $parameter){
        		if(!array_key_exists($parameter, $this->parameters)){
        			self::err("Unable to find required argument: $parameter");
        			$returnValue = false;
        		}
        	}
        	
        	if(!$returnValue){
        		$usage = "Usage:php \n{$_SERVER['argv'][0]}";
        		foreach($required as $parameter){
        			$usage .= " --{$parameter}={$parameter}Value";
        		}
        		self::err($usage);
        	}
        }
        
        if($returnValue && isset($this->inputFormat['types']) && is_array($this->inputFormat['types'])){
        	foreach($this->inputFormat['types'] as $paramName => $type){
        		if(isset($this->parameters[$paramName])){
        			$parameter = $this->parameters[$paramName];
	        		switch($type){
	        			case 'file': 
	        				if( !is_file($parameter) || 
	        					!file_exists($parameter) || 
	        					!is_readable($parameter))
	        				{
	        					self::err("Unable to access to the file: $parameter");
	        					$returnValue = false;
	        				}
	        				break;
	        			case 'dir': 
	        				if( !is_dir($parameter) || 
	        					!is_readable($parameter))
	        				{
	        					self::err("Unable to access to the directory: $parameter");
	        					$returnValue = false;
	        				}
	        				break;
	        			case 'path': 
	        				if( !is_dir(dirname($parameter)) )
	        				{
	        					self::err("Wrong path given: $parameter");
	        					$returnValue = false;
	        				}
	        				break;
	        			case 'int':
	        			case 'float':
	        			case 'double':
	        				self::err("$parameter is not a valid $type");
	        				if(!is_numeric($parameter)){
	        					$returnValue = false;
	        				}
	        				break;
	        			case 'string':
	        				self::err("$parameter is not a valid $type");
	        				if(!is_string($parameter)){
	        					$returnValue = false;
	        				}
	        				break;
	        		}
	        	}
        	}
        	
        }
        
      
        
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D63 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method preRun
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function preRun()
    {
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D49 begin
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D49 end
    }

    /**
     * Short description of method run
     *
     * @abstract
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected abstract function run();

    /**
     * Short description of method postRun
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function postRun()
    {
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D54 begin
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D54 end
    }

    /**
     * Short description of method out
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string message
     * @param  array options
     */
    public static function out($message, $options = array())
    {
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D56 begin
        
    	$returnValue = '';
    	
        $colorized = false;
        isset($options['color']) ?  $color = $options['color'] : $color = 'grey';
        $color = trim(tao_helpers_Cli::getFgColor($color));
        if(!empty($color)){
        	$colorized = true;
        	$returnValue .= "\033[{$color}m";
        }
        isset($options['background']) ?  $bg = $options['background'] : $bg = 'black';
        $bg = trim(tao_helpers_Cli::getBgColor($bg));
        if(!empty($bg)){
        	$colorized = true;
        	$returnValue .= "\033[{$bg}m";
        }
        
        $returnValue .= $message;
      	
        if(!isset($options['inline'])){
        	$returnValue .= "\n";
        }
        
        if($colorized){
        	$returnValue .= "\033[0m";
        }
        
        echo $returnValue;
        
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D56 end
    }

    /**
     * Short description of method err
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string message
     * @param  boolean stopExec
     */
    protected static function err($message, $stopExec = false)
    {
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D5B begin
        
        echo self::out($message, array('color' => 'red', 'background' => 'black'));
        
        if($stopExec == true){
        	exit(1);	//exit the program with an error
        }
        
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D5B end
    }

} /* end of abstract class tao_scripts_Runner */

?>