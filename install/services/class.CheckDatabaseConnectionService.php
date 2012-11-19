<?php

/**
 * A Service implementation aiming at checking that it is possible to connect to
 * a database with particular driver, user and password.
 * 
 * Please refer to tao/install/api.php for more information about how to call this service.
 */
class tao_install_services_CheckDatabaseConnectionService 
	extends tao_install_services_Service
	implements tao_install_services_CheckService
	{
    
    /**
     * Creates a new instance of the service.
     * @param tao_install_services_Data $data The input data to be handled by the service.
     * @throws InvalidArgumentException If the input data structured is malformed or is missing data.
     */
    public function __construct($data){
        parent::__construct($data);
    }
    
    /**
     * Executes the main logic of the service.
     * @return tao_install_services_Data The result of the service execution.
     */
    public function execute(){
        $ext = self::buildComponent($this->getData());
        $report = $ext->check();            
        $this->setResult(self::buildResult($this->getData(), $report, $ext));
    }

    /**
     * Custom error handler to prevent noisy output at bad connection time.
     * @return boolean
     */
    public static function onError($errno, $errstr, $errfile, $errline){
        // Do not call PHP internal error handler !
        return true;
    }
    
    public static function checkData(tao_install_services_Data $data){
    	$content = json_decode($data->getContent(), true);
        if (!isset($content['type']) || empty($content['type']) || $content['type'] != 'CheckDatabaseConnection'){
            throw new InvalidArgumentException("Unexpected type: 'type' must be equal to 'CheckDatabaseConnection'.");
        }
        else if (!isset($content['value']) || empty($content['value'])){
            throw new InvalidArgumentException("Missing data: 'value' must be provided.");
        }
        else if (!isset($content['value']['driver']) || empty($content['value']['driver'])){
            throw new InvalidArgumentException("Missing data: 'driver' must be provided.");
        }
        else if (!isset($content['value']['user']) || empty($content['value']['user'])){
            throw new InvalidArgumentException("Missing data: 'user' must be provided.");
        }
        else if (!isset($content['value']['password'])){
            throw new InvalidArgumentException("Missing data: 'password' must be provided.");
        }
        else if (!isset($content['value']['host']) || empty($content['value']['host'])){
            throw new InvalidArgumentException("Missing data: 'host' must be provided.");
        }
		else if (!isset($content['value']['overwrite']) || !is_bool($content['value']['overwrite'])){
			throw new InvalidArgumentException("Missing data: 'overwrite' must be provided.");
		}
		else if (!isset($content['value']['database']) || empty($content['value']['database'])){
			throw new InvalidArgumentException("Missing data: 'database' must be provided.");
		}
    }
    
    public static function buildComponent(tao_install_services_Data $data){
    	$content = json_decode($data->getContent(), true);
        $driver = str_replace('pdo_', '', $content['value']['driver']);
    	if (isset($content['value']['optional'])){
        	$optional = $content['value']['optional'];
        }
        else{
        	$optional = false;
        }
        
        // Try such a driver. Because the provided driver name should
        // comply with a PHP Extension name (e.g. mysql, pgsql), we test its
        // existence.
        $ext = new common_configuration_PHPDatabaseDriver(null, null, 'pdo_' . $driver, $optional);
        return $ext;
    }
    
    public static function buildResult(tao_install_services_Data $data,
									   common_configuration_Report $report,
									   common_configuration_Component $component){

		$content = json_decode($data->getContent(), true);
        $driver = str_replace('pdo_', '', $content['value']['driver']);
        $user = $content['value']['user'];
        $password = $content['value']['password'];
        $host = $content['value']['host'];
		$overwrite = $content['value']['overwrite'];
		$database = $content['value']['database'];
        
        if ($report->getStatus() == common_configuration_Report::VALID){
            // Great the driver is there, we can try a connection.
            try{
                set_error_handler(array('tao_install_services_CheckDatabaseConnectionService', 'onError'));
                $dbCreatorClassName = tao_install_utils_DbCreator::getClassNameForDriver($driver);
                $dbCreator = new $dbCreatorClassName($host, $user, $password, $driver);
                
				// If we are here, we are connected.
				if ($overwrite == false && $dbCreator->dbExists($database)){
					$message = "A database with name '${database}' already exists.";
					$status = 'invalid-overwrite';
				}
				else{
					$message = "Database connection successfuly established with '${host}' using driver '${driver}'.";
					$status = 'valid';
				}
				
				restore_error_handler();
            }
            catch(Exception $e){
                $message = "Unable to connect to database at '${host}' using driver '${driver}': " . $e->getMessage();
                $status = 'invalid-noconnection';
                
                restore_error_handler();
            }
        }
        else{
            // No driver found.
            $status = 'invalid-nodriver';
            $message = "Database driver '${driver}' is not available.";
        } 
        
        
        $value = array('status' => $status,
                       'message' => $message,
                       'optional' => $component->isOptional(),
                       'name' => $component->getName());
                       
                       
        $data = array('type' => 'DatabaseConnectionReport',
                      'value' => $value);
        
        return new tao_install_services_Data(json_encode($data));
	}
}
?>