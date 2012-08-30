<?php

/**
 * A Service implementation aiming at checking that it is possible to connect to
 * a database with particular driver, user and password.
 * 
 * Please refer to tao/install/api.php for more information about how to call this service.
 */
class tao_install_services_CheckDatabaseConnectionService extends tao_install_services_Service{
    
    /**
     * Creates a new instance of the service.
     * @param tao_install_services_Data $data The input data to be handled by the service.
     * @throws InvalidArgumentException If the input data structured is malformed or is missing data.
     */
    public function __construct($data){
        parent::__construct($data);
        
        $content = json_decode($this->getData()->getContent(), true);
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
        else if (!isset($content['value']['optional'])){
            throw new InvalidArgumentException("Missing data: 'optional' must be provided.");
        }
    }
    
    /**
     * Executes the main logic of the service.
     * @return tao_install_services_Data The result of the service execution.
     */
    public function execute(){
        $content = json_decode($this->getData()->getContent(), true);
        $name = (isset($content['value']['name']) && !empty($content['value']['name'])) ? $content['value']['name'] : 'db_connection';
        $driver = $content['value']['driver'];
        $user = $content['value']['user'];
        $password = $content['value']['password'];
        $host = $content['value']['host'];
		$overwrite = $content['value']['overwrite'];
		$database = $content['value']['database'];
        $optional = ($content['value']['optional'] == 'true') ? true : false;
        
        // Try such a driver. Because the provided driver name should
        // comply with a PHP Extension name (e.g. mysql, pgsql), we test its
        // existence.
        $ext = new common_configuration_PHPDatabaseDriver(null, null, $driver);
        $report = $ext->check();
        
        if ($report->getStatus() == common_configuration_Report::VALID){
            // Great the driver is there, we can try a connection.
            try{
                set_error_handler(array(get_class($this), 'onError'));
                $dbCreator = new tao_install_utils_DbCreator($host, $user, $password, $driver);
                
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
                $message = "Unable to connect to database at '${host}' using driver '${driver}'.";
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
                       'optional' => $optional,
                       'name' => $name);
                       
                       
        $data = array('type' => 'DatabaseConnectionReport',
                      'value' => $value);
                      
        $this->setResult(new tao_install_services_Data(json_encode($data)));
    }

    /**
     * Custom error handler to prevent noisy output at bad connection time.
     * @return boolean
     */
    public static function onError($errno, $errstr, $errfile, $errline){
        // Do not call PHP internal error handler !
        return true;
    }
}
?>