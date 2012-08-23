<?php
/**
 * An Exception that represents a badly called Application Programming Interface.
 */
class tao_install_api_InvalidAPICallException extends Exception{
    
    /**
     * Creates a new instance of InvalidAPICallException.
     * @param string $message A message explaining why this is a bad API call.
     */
    public function __construct($message){
        parent::__construct($message);
    }
}
?>