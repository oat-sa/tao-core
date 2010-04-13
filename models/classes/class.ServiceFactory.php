<?php

error_reporting(E_ALL);

/**
 * The ServiceFactory enable you to get Service instances dynamically.
 * Use the ServiceFactory::get(serviceName) to retrieve a single instance of a
 * implementation.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 * @version 0.1
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001829-includes begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001829-includes end

/* user defined constants */
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001829-constants begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001829-constants end

/**
 * The ServiceFactory enable you to get Service instances dynamically.
 * Use the ServiceFactory::get(serviceName) to retrieve a single instance of a
 * implementation.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage models_classes
 * @version 0.1
 */
class tao_models_classes_ServiceFactory
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Contains the references of each service instance. 
     * The service name is used as key.
     *
     * @access private
     * @var array
     */
    private static $instances = array();

    /**
     * The name of the service data source implemetation
     *
     * @access private
     * @var string
     */
    private static $dataSource = 'Generis';

    /**
     * pattern to create service dynamically.
     * Use the printf syntax, where %1$ is the short name of the service
     *
     * @access protected
     * @var string
     */
    protected static $namePattern = 'tao%1$s_models_classes_%1$sService';

    // --- OPERATIONS ---

    /**
     * Entry point to get an instance of a service 
     * by it's short name.
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string serviceName The name of the service you want to retrieve. You can set the complete class name, the interface name or only the ressource name managed by the service.
     * @return tao_models_classes_Service
     */
    public static function get($serviceName)
    {
        $returnValue = null;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001832 begin
		
		if( !class_exists($serviceName) || !preg_match("/^(tao|wf)/", $serviceName) ){
			//in case the parameter is the interface name we load the default dataSource implementation
			$serviceName = sprintf(self::$namePattern, ucfirst(strtolower($serviceName)));
		}
		if(class_exists($serviceName)){
			
			//create the instance only once
			$construct = false;
			if(!array_key_exists($serviceName, self::$instances)){
				$construct = true;
			}
			else if (is_null(self::$instances[$serviceName])){
				$construct = true;
			}
			if($construct){
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
        // section 10-13-1-45-792423e0:12398d13f24:-8000:0000000000001832 end

        return $returnValue;
    }

} /* end of class tao_models_classes_ServiceFactory */

?>