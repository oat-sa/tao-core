<?php

error_reporting(E_ALL);

/**
 * This Script class aims at providing tools to manage TAO extensions.
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
class tao_scripts_TaoExtensions
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
            self::err("Please provide a Generis 'user'.", true);
        }
        else{
            if ($this->options['password'] == null){
                self::err("Please provide a Generis 'password'.", true);
            }
            else{
                if ($this->options['action'] == null){
                    self::err("Please provide the 'action' parameter.", true);
                }
                else{
                    switch ($this->options['action']){
                        case 'setConfig':
                            $this->checkSetConfigInput();
                        break;
                        
                        default:
                            self::err("Please provide a valid 'action' parameter.", true);
                        break;
                    }
                    
                    // If we are here, it means that input is correct for the current action.
                    $this->setCurrentAction($this->options['action']);
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
     * Sets a configuration parameter of an extension. The configuration
     * to change is provided with the 'configParameter' CLI argument and its
     * is provided with the 'configValue' CLI argument. The extension on which
     * want to change a parameter value is provided with the 'extension' CLI
     *
     * Parameters that can be changed are:
     * - loaded (boolean)
     * - loadedAtStartup (boolean)
     * - ghost (boolean)
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function actionSetConfig()
    {
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A65 begin
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
            $inputFormat = array('min' => 2,
                                 'parameters' => array(
                                                        array('name' => 'verbose',
                                                              'type' => 'boolean',
                                                              'shortcut' => 'v',
                                                              'description' => 'Verbose mode'
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
                                                        array('name' => 'configParameter',
                                                              'type' => 'string',
                                                              'shortcut' => 'cP',
                                                              'description' => "Configuration parameter (loaded|loadedAtStartup|ghost) to change when the 'setConfig' action is called"
                                                             ),
                                                        array('name' => 'configValue',
                                                              'type' => 'boolean',
                                                              'shortcut' => 'cV',
                                                              'description' => "Configuration value to set when the 'setConfig' action is called"
                                                             ),
                                                        array('name' => 'extension',
                                                              'type' => 'string',
                                                              'shortcut' => 'e',
                                                              'description' => "Extension ID that determines the TAO extension to focus on")
                                                       )
                                );
        }
        
        parent::__construct($inputFormat, $construct);
        // section -64--88-56-1--60338e38:1374a9f6f9e:-8000:0000000000003A6C end
    }

    /**
     * Short description of method checkSetConfigInput
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return void
     */
    public function checkSetConfigInput()
    {
        // section -64--88-56-1--62742a90:13778cdce7f:-8000:0000000000003A8A begin
        
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
    public static function err($message, $stopExec = false)
    {
        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003A99 begin
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
        // section -64--88-56-1-14c4460b:13779143f0c:-8000:0000000000003ACB end
    }

} /* end of class tao_scripts_TaoExtensions */

?>