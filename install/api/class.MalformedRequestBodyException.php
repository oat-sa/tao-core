<?php
/**
 * An Exception that represents a malformed request body in an API call context..
 */
class tao_install_api_MalformedRequestBodyException extends tao_install_api_InvalidAPICallException{
    
    /**
     * Creates a new instance of MalformedRequestBodyException..
     * @param string $message A message explaining why this is a bad API call.
     */
    public function __construct($message){
        parent::__construct($message);
    }
}
?>