<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 15/07/16
 * Time: 10:58
 */

namespace oat\tao\model\mvc\error;


class AjaxResponse extends ResponseAbstract
{
    protected $contentType = 'application/json';

    public function send()
    {

        $message = $this->exception->getMessage();
        if(method_exists($this->exception , 'getUserMessage')) {
            $message = $this->exception->getUserMessage();
        }

        $response = [
            "success" => false,
            "type" => 'Exception',
            "data" => array(
                'ExceptionType' => get_class($this->exception)
            ),
            "message" => $message,
        ];
        new \common_AjaxResponse($response);

    }
}