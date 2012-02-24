<?php

require_once dirname(__FILE__) . '/../../../generis/common/inc.extension.php';
include_once dirname(__FILE__) . '/../../includes/raw_start.php';

class Foo {
    
    public function defaultAjaxResponse()
    {
        $ajaxResponse = new common_AjaxResponse();
    }
    
    public function jsonAjaxResponse()
    {
        $ajaxResponse = new common_AjaxResponse(Array(
            "data" => "expected data"
            , "message" => "expected message"
        ));
    }
    
    public function failedAjaxResponse()
    {
        $ajaxResponse = new common_AjaxResponse(Array(
            "success" => false
            , "type" => "json"
            , "data" => "expected data"
            , "message" => "expected message"
        ));
    }
    
    public function exceptionAjaxResponse()
    {
        try{
            throw new common_Exception("An expected test case exception occured");
        }
        catch(common_Exception $e){
            $ajaxResponse = new common_AjaxResponse(Array(
                "success" => false
                , "type" => "exception"
                , "message" => $e->getMessage()
            ));
        }
    }
}

$actions = $_GET['action'];
$foo = new Foo();
$foo->$actions();

?>
