<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExceptionHandler
 *
 * @author plichart
 */
/*TODO move it */
class tao_actions_RESTExceptionHandler extends common_exception_ExceptionHandler{
   
    public static function handle(common_Exception $exception){
	
	switch (get_class($exception)) {
	
	case "common_exception_BadRequest":{header("HTTP/1.0 400 Bad Request" );break;}
	case "common_exception_MissingParameter":{header("HTTP/1.0 400 Bad Request" );break;}
	case "common_exception_NotAcceptable":{header("HTTP/1.0 406 Bad Request" );break;}
	case "common_exception_BadRequest":{header("HTTP/1.0 401 Bad Request" );break;}
	
	


	default: {header("HTTP/1.0 500 Bad Request" );}
	}
    }
}

?>
