<?php

error_reporting(E_ALL);

/**
 * TAO - tao/scripts/class.Runner.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 18.02.2011, 16:19:24 with ArgoUML PHP module 
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
    	
    	if(PHP_SAPI == 'cli' && !isset($options['argv'])){
			$this->argv = $_SERVER['argv'];
		}
		else{
			$this->argv = $options['argv'];
		}
    	
    	self::out("\n * Running {$this->argv[0]} *\n", array('color' => 'white'));
    	
    	$this->inputFormat = $inputFormat;

    	//check if help is needed
    	$helpTokens = array('-h', 'help', '-help', '--help)');
    	foreach( $helpTokens as $helpToken){
    		 if(in_array($helpToken, $this->argv)){
    		 	$this->help();
    		 	exit(0);
    		 }
    	}

    	//validate the input parameters
    	if(!$this->validateInput()){
    		$this->help();
    		self::err("Scripts stopped!", true);
    	}
    	
    	//script run loop
    	
    	$this->preRun();
    	
    	$this->run();
    	
    	$this->postRun();
    	
    	self::out("\n");
    	
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
        while($i < count($this->argv)){
        	$arg = trim($this->argv[$i]);
        	if(!empty($arg)){
        		if(preg_match("/^[\-]{1,2}\w+=(.*)+$/", $arg)){
	        		$sequence = explode('=', preg_replace("/^[\-]{1,}/", '', $arg));
	        		if(count($sequence) >= 2){
	        			$this->parameters[$sequence[0]] = $sequence[1];
	        		}
	        	}
        		else if(preg_match("/^[\-]{1,2}\w+$/", $arg)){
        			if(isset($this->argv[$i + 1])){
	        			$key = preg_replace("/^[\-]{1,}/", '', $arg);
	        			$this->parameters[$key] = trim($this->argv[++$i]);
        			}
	        	}
	        	else{
	        		$this->parameters[$i] = $arg;
	        	}
        	}
        	$i++;
        }
        
        //replaces shortcuts by their original names
        foreach($this->inputFormat['parameters'] as $parameter){
        	if(isset($parameter['shortcut'])){
        		$short = $parameter['shortcut'];
        		$long = $parameter['name'];
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
        
        if(isset($this->inputFormat['required']) && is_array($this->inputFormat['required'])){
        	if(!is_array($this->inputFormat['required'][0])){
        		$requireds = array($this->inputFormat['required']);
        	}
        	else{
        		$requireds = $this->inputFormat['required'];
        	}
        	
        	$found = false;
        	foreach($requireds as $required){
        		
        		$matched = 0;
	        	foreach($required as $parameter){
	        		if(array_key_exists($parameter, $this->parameters)){
	        			$matched++;
	        		}
	        	}
	        	if($matched == count($required)){
        			$found = true;
        			break;
	        	}
        	}
        	
        	if(!$found){
        		self::err("Unable to find required arguments");
        		$returnValue = false;
        	}
        }
        
        if($returnValue){
        	
        	 foreach($this->inputFormat['parameters'] as $parameter){
        		if(isset($this->parameters[$parameter['name']])){
	        	 	$parameter = $this->parameters[$parameter['name']];
		        	switch($parameter['type']){
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
	        				if(!is_numeric($parameter)){
	        					self::err("$parameter is not a valid $type");
	        					$returnValue = false;
	        				}
	        				break;
	        			case 'string':
	        				if(!is_string($parameter)){
	        					self::err("$parameter is not a valid $type");
	        					$returnValue = false;
	        				}
	        				break;
	        			case 'boolean':
	        				if(!is_bool($parameter) && $parameter != 'true' && $parameter != 'false' ){
	        					self::err("$parameter is not a valid $type");
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
    	
    	if(isset($options['prefix'])){
    		$returnValue = $options['prefix'];
    	}
    	
        $colorized = false;
        isset($options['color']) ?  $color = $options['color'] : $color = 'grey';
        $color = trim(tao_helpers_Cli::getFgColor($color));
        if(!empty($color)){
        	$colorized = true;
        	$returnValue .= "\033[{$color}m";
        }
        isset($options['background']) ?  $bg = $options['background'] : $bg = '';
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
        
        echo self::out($message, array('color' => 'light_red'));
        
        if($stopExec == true){
        	exit(1);	//exit the program with an error
        }
        
        // section 127-0-1-1--39e3a8dd:12e33ba6c22:-8000:0000000000002D5B end
    }

    /**
     * Short description of method help
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function help()
    {
        // section 127-0-1-1--5d5119d4:12e3924f2ec:-8000:0000000000002D86 begin
        
    	$usage = "Usage:php {$this->argv[0]} [arguments]\n";
    	$usage .= "\nArguments list:\n";
		foreach($this->inputFormat['parameters'] as $parameter){
       		if(isset($parameter['required'])){
       			if($parameter['required'] == true){
       				$usage .= "Required";
       			}
       			else{
       				$usage .= "Optional";
       			}
       		}
       		else{
       			$usage .= "\t";
       		}
			$usage .= "\t--{$parameter['name']}";
       		if(isset($parameter['shortcut'])){
       			$usage .= "|-{$parameter['shortcut']}";
       		}
       		$usage .= "\t\t{$parameter['description']}";
       		$usage .= "\n";
       	}
  		self::out($usage, array('color' => 'light_blue'));
    	
        // section 127-0-1-1--5d5119d4:12e3924f2ec:-8000:0000000000002D86 end
    }

} /* end of abstract class tao_scripts_Runner */

?>