<?php
/**
 * An exception describing that a requested custom check is unknown.
 */
class tao_install_services_UnknownCustomCheckException extends Exception{
    
    private $customCheckName;
    private $extensionName;
    
    /**
     * Creates a new instance.
     */
    public function __construct($customCheckName, $extensionName){
        parent::__construct("Unable to find Custom Check '${customCheckName}' in extension '${extensionName}'.");
        $this->setCustomCheckName($customCheckName);
        $this->setExtensionName($extensionName);
    }
    
    /**
     * Sets the Custom Check name that was requested but not found.
     * @param string $customCheckName A Custom Check name.
     */
    protected function setCustomCheckName($customCheckName){
        $this->customCheckName = $customCheckName;
    }
    
    /**
     * Gets the Custom Check name that was requested but not found.
     * @return string A Custom Check name.
     */
    public function getCustomCheckName(){
        return $this->customCheckName;
    }
    
    /**
     * Sets the Extension name where the Custom Check should have been found.
     * @param string $extensionName An Extension name.
     */
    protected function setExtensionName($extensionName){
        $this->extensionName = $extensionName;
    }
    
    /**
     * Gets the Extension name where the Custom Check should have been found.
     * @return string An Extension name.
     */
    public function getExtensionName(){
        return $this->extensionName;
    }
}
