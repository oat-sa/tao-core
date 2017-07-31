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
    protected $contentType = 'application/json; charset=UTF-8';

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
                'ExceptionType' => get_class($this->exception),
                "message" => $message,
            ),
            "message" => $message,
        ];

        $this->response->getBody()->write(json_encode($response));

        $this->response =  $this->response->withStatus($this->httpCode)
            ->withHeader('Content-Type', $this->contentType);

        return $this->response;

    }
}