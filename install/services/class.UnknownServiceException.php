<?php
/**
 * An Exception which states that a requested Service cannot be found.
 */
class tao_install_services_UnknownServiceException extends Exception{
    
    /**
     * The requested Service name.
     */
    private $serviceName;
    
    /**
     * Creates a new UnknownServiceException.
     * @param string $serviceName The name of the requested Service.
     */
    public function __construct($serviceName = null){
        if (!empty($serviceName)){
            parent::__construct("Service not found.");
        }
        else {
            parent::__construct("Service '${serviceName} not found.");
        }
        
        $this->setServiceName($serviceName);
    }

    /**
     * Sets the requested Service name.
     * @param string $serviceName A Service name.
     * @return void
     */
    protected function setServiceName($serviceName){
        $this->serviceName = $serviceName;
    }
    
    /**
     * Gets the requested Service name.
     * @return string A Service name.
     */
    public function getServiceName(){
        return $this->serviceName;
    }
}
?>