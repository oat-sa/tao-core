<?php
/*
 * This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *  
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * 
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 *  Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\mvc\psr7;

/**
 * Description of ActionRenderer
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class ActionExecutor implements ActionExecutorInterface 
{
    /**
     *
     * @var \Psr\Http\Message\ResponseInterface 
     */
    protected $response;

    /**
     * 
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \oat\tao\model\mvc\psr7\ActionExecutor
     */
    public function setResponse(\Psr\Http\Message\ResponseInterface $response) {
        $this->response = $response;
        return $this;
    }
    /**
     * 
     * @return \Psr\Http\Message\ResponseInterface 
     */
    public function getResponse() {
        return $this->response;
    }
    
    /**
     * 
     * @return $this
     */
    protected function sendHttpCode() {
        $status = $this->getResponse()->getStatusCode();
        http_response_code($status);
        return  $this;
    }
    
    /**
     * 
     * @return $this
     */
    protected function sendHeaders() {
        $headers = $this->getResponse()->getHeaders();
        foreach ($headers as $name => $value) {
            header($name . ': ' . implode(',' , $value));
        }
        return $this;
    }
    
    /**
     * 
     * @return $this
     */
    protected function sendBody() {
        $body = $this->getResponse()->getBody();
        echo $body->getContents();
        return $this;
    }

    /**
    * send psr7 response
    */
    public function send($controller , \Psr\Http\Message\ResponseInterface $response = null) {
        if(is_null($response)) {
            $response = $controller->sendResponse($response)->getResponse();
        }
        return $this->execute($response);
    }
    
    public function execute(\Psr\Http\Message\ResponseInterface $response = null) {
        return $this->setResponse($response)->sendHttpCode()->sendHeaders()->sendBody();
    }
    
}
