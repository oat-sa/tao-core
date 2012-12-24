<?php

error_reporting(E_ALL);

/**
 * This Script class aims at providing tools to hardify/unhardify classes.
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage scripts
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_scripts_Runner
 *
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 */
require_once('tao/scripts/class.Runner.php');

/* user defined includes */
// section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A4A-includes begin
// section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A4A-includes end

/* user defined constants */
// section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A4A-constants begin
// section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A4A-constants end

/**
 * This Script class aims at providing tools to manage TAO extensions.
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 * @subpackage scripts
 */
class tao_scripts_TaoHardify
    extends tao_scripts_Runner
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * The current action the TaoExtensions Script class is running. It
     * to the 'action' parameter given in input.
     *
     * @access public
     * @var string
     */
    public $currentAction = '';

    /**
     * Contains the final values of the CLI parameters given as input for this
     * (merge of the default values and paraleters array).
     *
     * @access public
     * @var array
     */
    public $options = array();

    /**
     * States if the Generis user is connected or not.
     *
     * @access public
     * @var boolean
     */
    public $connected = false;

    // --- OPERATIONS ---

    /**
     * Instructions to execute before the run method.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function preRun()
    {
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A4C begin
        $this->checkInput();
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A4C end
    }

    /**
     * Instructions to execute to handle the action to perform.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function run()
    {
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A4E begin
        $this->outVerbose("Connecting...");
        if ($this->connect($this->options['user'], $this->options['password'])){
            $this->outVerbose("Connected to TAO API.");
            
            switch ($this->options['action']){
                case 'hardify':
                    $this->setCurrentAction($this->options['action']);
                    $this->actionHardify();
                break;
                
                case 'unhardify':
                    $this->setCurrentAction($this->options['action']);
                    $this->actionUnhardify();
                break;
            }
            
            $this->disconnect();    
        }
        else{
            $this->error("Could not connect to TAO API. Please check your user name and password.", true);
        }
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A4E end
    }

    /**
     * Instructions to execute after the postRun method.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function postRun()
    {
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A50 begin
        $this->outVerbose("Script executed gracefully.");
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A50 end
    }

    /**
     * Checks the input parameters when the script is called from the CLI. It
     * check parameters common to any action (user, password, action) and
     * to the appropriate checking method for the other parameters.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function checkInput()
    {
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A56 begin
        $this->options = array('verbose' => false,
                               'action' => null,
                               'user' => null,
                               'password' => null);
                               
        $this->options = array_merge($this->options, $this->parameters);
        
        // Check common inputs.
        if ($this->options['user'] == null){
            $this->error("Please provide a Generis 'user'.", true);
        }
        else{
            if ($this->options['password'] == null){
                $this->error("Please provide a Generis 'password'.", true);
            }
            else{
                if ($this->options['action'] == null){
                    $this->error("Please provide the 'action' parameter.", true);
                }
                else{
                    switch ($this->options['action']){
                        case 'hardify':
                            $this->checkHardifyInput();
                        break;
                        
                        case 'unhardify':
                            $this->checkUnhardifyInput();
                        break;
                        
                        default:
                            $this->error("Please provide a valid 'action' parameter.", true);
                        break;
                    }
                }    
            }
        }
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A56 end
    }

    /**
     * Get the current action being executed.
     *
     * @access protected
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
    protected function getCurrentAction()
    {
        $returnValue = (string) '';

        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A5D begin
        $returnValue = $this->currentAction;
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A5D end

        return (string) $returnValue;
    }

    /**
     * Set the current action being executed.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string currentAction The name of the current action being executed by the script.
     * @return void
     */
    public function setCurrentAction($currentAction)
    {
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A60 begin
        $this->currentAction = $currentAction;
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A60 end
    }

    /**
     * Hardify a class.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function actionHardify()
    {
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A65 begin
        
        // Retrieve parameter values.
        $class = $this->options['class'];
        $topClass = $this->options['topClass'];
        $createForeigns = $this->options['createForeigns'];
        $recursive = $this->options['recursive'];
        $classUri = $class->getUri();
        $additionalProperties = array();
        $blackList = array('http://www.tao.lu/middleware/wfEngine.rdf#ClassProcessVariables');
        
        if (!empty($this->options['additionalProperties'])){
        	$additionalProperties = $this->options['additionalProperties'];
        }
        
        $optionsHardify = array('recursive'             => $recursive,
        						'append'                => true,
        						'createForeigns'        => $createForeigns,
        						'referencesAllTypes'	=> true,
        						'rmSources'             => true,
        						'topClass'				=> $topClass,
        						'additionalProperties'	=> $additionalProperties);
        
        try{
        	$this->outVerbose("Hardifying class '${classUri}'...");
        	$switcher = new core_kernel_persistence_Switcher($blackList);
        	$switcher->hardify($class, $optionsHardify);
        	
        	$hardenedClasses = $switcher->getHardenedClasses();
        	if (array_key_exists($classUri, $hardenedClasses)){
        		$count = $hardenedClasses[$classUri];
        		$this->outVerbose("Class '${classUri}' successfuly hardified: ${count} instance(s) compiled.");	
				
				if (true == $optionsHardify['createForeigns']){
			    	unset($hardenedClasses[$classUri]);
			    	
			    	if (true == empty($hardenedClasses)){
			    		$this->outVerbose("No foreign classes were compiled.");
			    	}
			    	else{
			    		foreach ($hardenedClasses as $uri => $count){
							$this->outVerbose("Foreign class '${uri} successfuly hardified: ${count} instance(s) compiled.'");
			    		}
			    	}
				}
			}
        	
        }
        catch (Exception $e){
        	$msg = "A fatal error occured while hardifying class '${classUri}': " . $e->getMessage();
        	$this->error($msg, true);
        }
        
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A65 end
    }

    /**
     * Create a new instance of the TaoExtensions script and executes it. If the
     * inputFormat parameter is not provided, the script configures itself
     * to foster code reuse.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array inputFormat
     * @param  array options
     * @return mixed
     */
    public function __construct($inputFormat = array(), $options = array())
    {
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A6C begin
        if (count($inputFormat) == 0){
            // Autoconfigure the script.
            $inputFormat = array('min' => 4,
            	'parameters' => array(
                	array('name' => 'verbose',
                    	  'type' => 'boolean',
                    	  'shortcut' => 'v',
                    	  'description' => 'Verbose mode (default = false)'
                	),
                    array('name' => 'user',
                    	  'type' => 'string',
                    	  'shortcut' => 'u',
                    	  'description' => 'Generis user (must be a TAO Manager)'
                    ),
                    array('name' => 'password',
                    	  'type' => 'string',
                    	  'shortcut' => 'p',
                    	  'description' => 'Generis password'
                    ),
                    array('name' => 'action',
                    	  'type' => 'string',
                    	  'shortcut' => 'a',
                    	  'description' => 'Action to perform'
                    ),
                    array('name' => 'class',
                    	  'type' => 'string',
                    	  'shortcut' => 'c',
                    	  'description' => 'Class to hardify/unhardify'
                    ),
                    array('name' => 'additionalProperties',
                    	  'type' => 'string',
                    	  'shortcut' => 'aP',
                    	  'description' => 'Additional properties to be compiled with the class, separated by commas (",").'
                    ),
                    array('name' => 'createForeigns',
                    	  'type' => 'boolean',
                    	  'shortcut' => 'cF',
                    	  'description' => 'Create foreign classes (default = false).'
                    ),
                    array('name' => 'topClass',
                    	  'type' => 'string',
                    	  'shortcut' => 'tC',
                    	  'description' => 'The class where to stop in the class hierarchy hardification (default = rdfs:Resource).'
                    ),
                    array('name' => 'recursive',
                    	  'type' => 'boolean',
                    	  'shortcut' => 'r',
                    	  'description' => 'Hardify subclasses of the class to hardify or not (default = false).'
                    ),
                    ));
        }

        parent::__construct($inputFormat, $options);
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A6C end
    }

    /**
     * Check additional inputs for the 'setConfig' action.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function checkHardifyInput()
    {
        // section -64--88-56-1--62742a90:13778cdce7f:-8000:0000000000003A8A begin
        $defaults = array('class' => null,
        				  'createForeigns' => false,
        				  'recursive' => false,
                          'additionalProperties' => null,
        				  'topClass' => null);
                          
        $this->options = array_merge($defaults, $this->options);
        
        if (empty($this->options['class'])){
            $this->error("Please provide the 'class' parameter.", true);
        }
        else{
        	$classUri = trim($this->options['class']);
        	if (common_Utils::isUri($classUri)){
        		
        		// We are OK with the class to Hardify
				$class = new core_kernel_classes_Class($classUri);
				$this->options['class'] = $class;
				
				if (!empty($this->options['additionalProperties'])){
		        	$additionalProperties = explode(',', $this->options['additionalProperties']);
		        	if (empty($additionalProperties)){
		        		$this->error("The 'additionalProperties' parameter value is invalid.", true);
		        	}
		        	else{
		        		foreach ($additionalProperties as $k => $aP){
		        			$uri = trim($aP);
		        			if (true == common_Utils::isUri($uri)){
		        				// store clean uri.
		        				$additionalProperties[$k] = new core_kernel_classes_Property($uri);
		        			}
		        			else{
		        				$this->error("'${uri}' is not a valid URI in 'additionalProperties'.", true);
		        			}
		        		}
		        		
		        		$this->options['additionalProperties'] = $additionalProperties;
		        		
		        		if ($this->options['topClass'] == null){
		        			$this->options['topClass'] = new core_kernel_classes_Class(CLASS_GENERIS_RESOURCE);
		        		}
		        		else{
		        			$topClassUri = trim($this->options['topClass']);
		        			if (true == common_Utils::isUri($topClassUri)){
		        				$this->options['topClass'] = new core_kernel_classes_Class($topClassUri);	
		        			}
		        			else
		        			{
		        				$this->error("'${topClassUri}' is not a valid URI in 'topClass'.", true);	
		        			}
		        		}
		        	}
		        }
        	}
        	else{
        		$this->error("The 'class' parameter value is not a valid URI.", true);	
        	}
        }
        // section -64--88-56-1--62742a90:13778cdce7f:-8000:0000000000003A8A end
    }

    /**
     * Set the connected attribute to a given value.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  boolean value true if the user is connected, otherwhise false.
     * @return void
     */
    public function setConnected($value)
    {
        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003A91 begin
        $this->connected = $value;
        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003A91 end
    }

    /**
     * Short description of method isConnected
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return boolean
     */
    public function isConnected()
    {
        $returnValue = (bool) false;

        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003A96 begin
        $returnValue = $this->connected;
        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003A96 end

        return (bool) $returnValue;
    }

    /**
     * Display an error message. If the stopExec parameter is set to true, the
     * of the script stops and the currently connected user is disconnected if
     * It overrides the Runner::err method for this purpose.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string message The error message to display.
     * @param  boolean stopExec If set to false, the execution of the script stops.
     * @return mixed
     */
    public function error($message, $stopExec = false)
    {
        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003A99 begin
        if ($stopExec == true){
            $this->disconnect();
        }
        
        self::err($message, $stopExec);
        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003A99 end
    }

    /**
     * Connect to the generis API by using the CLI arguments 'user' and
     * It returns true or false depending on the connection establishement.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string user
     * @param  string password
     * @return boolean
     */
    public function connect($user, $password)
    {
        $returnValue = (bool) false;

        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003AC6 begin
        $userService = tao_models_classes_UserService::singleton();
        $returnValue = $userService->loginUser($user, md5($password));
        $this->setConnected($returnValue);
        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003AC6 end

        return (bool) $returnValue;
    }

    /**
     * Disconnect the currently connected user.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return mixed
     */
    public function disconnect()
    {
        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003ACB begin
        if ($this->isConnected()){
            $this->outVerbose("Disconnecting user...");
            $userService = tao_models_classes_UserService::singleton();
            if ($userService->logout() == true){
                $this->outVerbose("User gracefully disconnected from TAO API.");
                $this->setConnected(false);
            }
            else{
                $this->error("User could not be disconnected from TAO API.");
            }
        }
        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003ACB end
    }

    /**
     * Short description of method actionInstall
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function actionUnhardify()
    {
        // section -64--88-56-1-6bececbf:1380e785555:-8000:0000000000003B29 begin
        $this->error("Not yet implemented.", true);
        // section -64--88-56-1-6bececbf:1380e785555:-8000:0000000000003B29 end
    }

    /**
     * Short description of method checkInstallInput
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function checkUnhardifyInput()
    {
        // section -64--88-56-1-6bececbf:1380e785555:-8000:0000000000003B2B begin
        $this->error("Not yet implemented.", true);
        // section -64--88-56-1-6bececbf:1380e785555:-8000:0000000000003B2B end
    }

} /* end of class tao_scripts_TaoExtensions */

?>