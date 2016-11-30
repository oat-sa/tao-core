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
 * psr7 request/response controller Action
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
trait ActionTrait {
    
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;
    
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * 
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest() {
        if(is_null($this->request)) {
            $this->request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
        }
        return $this->request;
    }
    
    /**
     * 
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse() {
        if(is_null($this->response)) {
            $this->response = new \GuzzleHttp\Psr7\Response();
        }
        return $this->response;
    }
    
    public function getRequestParameter($name) {
        $params = array_merge((array) $this->getRequest()->getParsedBody(), $this->getRequest()->getQueryParams());
        if(array_key_exists($name, $params)) {
            return $params[$name];
        }
        return null;
    }

    public function hasRequestParameter($name) {
        $params = array_merge((array) $this->getRequest()->getParsedBody(), $this->getRequest()->getQueryParams());
        return array_key_exists($name, $params);
    }

    public function addParameters($parameters) {
    }

    public function getRequestParameters() {
        return array_merge((array) $this->getRequest()->getParsedBody(), $this->getRequest()->getQueryParams());
    }

    public function getHeader($string) {
        return $this->getRequest()->getHeader($string);
    }

    public function getHeaders() {
        return $this->getRequest()->getHeaders();
    }

    public function hasHeader($string) {
        return $this->getRequest()->hasHeader($string);
    }
    
    public function getRequestMethod() {
        return $this->getRequest()->getMethod();
    }

    public function isRequestGet() {
        return ($this->getRequest()->getMethod() === helper\RequestConstant::HTTP_GET);
    }

    public function isRequestPost() {
        return ($this->getRequest()->getMethod() === helper\RequestConstant::HTTP_POST);
    }

    public function isRequestPut() {
        return ($this->getRequest()->getMethod() === helper\RequestConstant::HTTP_PUT);
    }

    public function RequestisDelete() {
        return ($this->getRequest()->getMethod() === helper\RequestConstant::HTTP_DELETE);
    }

    public function isRequestHead() {
        return ($this->getRequest()->getMethod() === helper\RequestConstant::HTTP_HEAD);
    }

    public function getUserAgent() {
        return $this->getRequest()->getHeader('USER_AGENT');
    }

    public function getQueryString() {
        return $this->getRequest()->getHeader('QUERY_STRING');
    }

    public function getRequestURI() {
        return (string)$this->request->getUri();
    }

    public function getRawParameters() {
        return $this->getParameters();
    }
    
    public function setContentHeader($contentType, $charset = 'UTF-8') {
	$this->response = $this->getResponse()->withHeader('Content-Type', $contentType . '; charset=' . $charset);
        return $this->response;
    }
    
     /**
     * @param $response \GuzzleHttp\Psr7\Response
     * @return $this
     */
    public function updateResponse(\GuzzleHttp\Psr7\Response $response) {
        $this->response = $response;
        return $this;
    }
    
    /**
     * @return clearfw\Response
     */
    public function sendResponse($response = null) {
         
        if(!is_a($response, \GuzzleHttp\Psr7\Response::class )) {
            /* @var $response \GuzzleHttp\Psr7\Response */
            $response = $this->getResponse();
        }
        if($this->hasView()) {
            $view = $this->getRenderer()->render();
            $body     = \GuzzleHttp\Psr7\stream_for($view);
            $this->response = $response->withBody($body);
        }
        
        return $this;
    }
}
