<?php
/**
 * A specific install exception that must be thrown when an installation
 * parameter value is malformed (wrong data type, format, does not fit with
 * business rules).
 * 
 * @author Jerome Bogaerts <jerome@taotesting.com>
 */
class tao_install_utils_MalformedParameterException extends common_Exception{
	
	/**
	 * @return string the exception message
	 */
	public function __toString()
	{
        return $this->message;
    }
	
}
?>