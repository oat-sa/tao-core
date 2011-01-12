<?php
class tao_install_utils_Exception extends Exception{
	
	public function __toString(){
        return $this->message;
    }
	
}
?>