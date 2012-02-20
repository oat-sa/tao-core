<?php

error_reporting(E_ALL);

/**
 * Service is the base class of all services, and implements the singleton
 * for derived services
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1-25600304:12a5c17a5ca:-8000:00000000000024AC-includes begin
// section 127-0-1-1-25600304:12a5c17a5ca:-8000:00000000000024AC-includes end

/* user defined constants */
// section 127-0-1-1-25600304:12a5c17a5ca:-8000:00000000000024AC-constants begin
// section 127-0-1-1-25600304:12a5c17a5ca:-8000:00000000000024AC-constants end

/**
 * Service is the base class of all services, and implements the singleton
 * for derived services
 *
 * @abstract
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package tao
 * @subpackage models_classes
 */
abstract class tao_models_classes_Service
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Contains the references of each service instance. 
     * The service name is used as key.
     *
     * @access public
     * @var array
     */
    public static $instances = array();

    /**
     * pattern to create service dynamically.
     * Use the printf syntax, where %1$ is the short name of the service
     *
     * @access private
     * @var string
     */
    const namePattern = 'tao%1$s_models_classes_%1$sService';

    // --- OPERATIONS ---

    /**
     * protected constructor to enforce the singleton pattern
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    protected function __construct()
    {
        // section 127-0-1-1--196c75a3:133f5095367:-8000:000000000000343A begin
        // section 127-0-1-1--196c75a3:133f5095367:-8000:000000000000343A end
    }

    /**
     * returns an instance of the service defined by servicename. Always returns
     * same instance for a class
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string serviceName
     * @return tao_models_classes_Service
     */
    public static function getServiceByName($serviceName)
    {
        $returnValue = null;

        // section 127-0-1-1--196c75a3:133f5095367:-8000:000000000000343C begin
		
        if( !class_exists($serviceName) || !preg_match("/^(tao|wf)/", $serviceName) ){
        	//in case the parameter is the interface name we load the default dataSource implementation
        	$serviceName = sprintf(self::namePattern, ucfirst(strtolower($serviceName)));
        }
        if(class_exists($serviceName)){
        
        	//create the instance only once
        	$construct = false;
        	if(!isset(self::$instances[$serviceName])){
        		self::$instances[$serviceName] = new $serviceName();
        	}
        
        	//get the instance
        	$returnValue = self::$instances[$serviceName];
        
        	if( ! $returnValue instanceof tao_models_classes_Service ){
        		unset(self::$instances[$serviceName]);
        		throw new Exception("$serviceName must referr to a class extending the tao_models_classes_Service");
        	}
        }
        else{
        	throw new Exception("Unknow service $serviceName");
        }

        // section 127-0-1-1--196c75a3:133f5095367:-8000:000000000000343C end

        return $returnValue;
    }

    /**
     * returns an instance of the service the function was called from. Always
     * the same instance for a class
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return tao_models_classes_Service
     */
    public static function singleton()
    {
        $returnValue = null;

        // section 127-0-1-1--83665c3:133f534928e:-8000:0000000000003447 begin
        $serviceName = get_called_class();
        if (!isset(self::$instances[$serviceName])) {
        	self::$instances[$serviceName] = new $serviceName();
        }
        
        $returnValue = self::$instances[$serviceName];
        // section 127-0-1-1--83665c3:133f534928e:-8000:0000000000003447 end

        return $returnValue;
    }

} /* end of abstract class tao_models_classes_Service */

?>