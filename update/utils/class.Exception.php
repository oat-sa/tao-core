<?php
/**
 * Override the exception to type the update exceptions.
 * Used to display install errors and misconfiguration to the user.
 * 
 * @author Bertrand CHEVRIER <bertrand.chevrier@tudor.lu>
 */
class tao_update_utils_Exception extends Exception{
	
	/**
	 * @return string the exception message
	 */
	public function __toString()
	{
        return $this->message;
    }
	
}
?>