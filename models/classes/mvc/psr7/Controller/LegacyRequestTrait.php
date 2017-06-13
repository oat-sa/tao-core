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
 *  Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\mvc\psr7\Controller;


use oat\tao\model\mvc\psr7\clearfw\Request;
use oat\tao\model\mvc\psr7\clearfw\Response;

trait LegacyRequestTrait
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @return Request
     */
    public function getRequest() {
        if(is_null($this->request)) {
            $this->request = new \oat\tao\model\mvc\psr7\clearfw\Request();
            $this->request->setPsrRequest(\GuzzleHttp\Psr7\ServerRequest::fromGlobals());
        }
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse() {
        if(is_null($this->response)) {
            $this->response = new \oat\tao\model\mvc\psr7\clearfw\Response();
            $this->response->setPsrResponse(new \GuzzleHttp\Psr7\Response());
        }
        return $this->response;
    }

    public function getRequestParameters()
    {
        return $this->getRequest()->getParameters();
    }

    public function hasRequestParameter($name)
    {
        return $this->getRequest()->hasParameter($name);
    }
    public function getHeader($name)
    {
        return $this->getRequest()->getHeader($name);
    }

    public function hasHeader($name)
    {
        return $this->getRequest()->hasHeader($name);
    }
    public function getHeaders(){
        return $this->getRequest()->getHeaders();
    }	public function getRequestParameter($name)
{
    return $this->getRequest()->getParameter($name);
}

    public function hasCookie($name)
    {
        return $this->getRequest()->hasCookie($name);
    }

    public function getCookie($name)
    {
        return $this->getRequest()->getCookie($name);
    }

    public function getRequestMethod()
    {
        return $this->getRequest()->getMethod();
    }

    public function isRequestGet()
    {
        return $this->getRequest()->isGet();
    }

    public function isRequestPost()
    {
        return $this->getRequest()->isPost();
    }

    public function isRequestPut()
    {
        return $this->getRequest()->isPut();
    }

    public function isRequestDelete()
    {
        return $this->getRequest()->isDelete();
    }

    public function isRequestHead()
    {
        return $this->getRequest()->isHead();
    }

    public function getUserAgent()
    {
        return $this->getRequest()->getUserAgent();
    }

    public function getQueryString()
    {
        return $this->getRequest()->getQueryString();
    }

    public function getRequestURI()
    {
        return $this->getRequest()->getRequestURI();
    }

    public function setCookie($name, $value = null, $expire = null,
                              $domainPath = null, $https = null, $httpOnly = null)
    {
        return $this->getResponse()->setCookie($name, $value, $expire, $domainPath, $https, $httpOnly);
    }

    public function setContentHeader($contentType, $charset = 'UTF-8')
    {
        $this->getResponse()->setContentHeader($contentType, $charset);
    }

    public function getContentType()
    {
        $this->getResponse()->getContentType();
    }

    public function getCharset()
    {
        $this->getCharset();
    }

    public function hasSessionAttribute($name)
    {
        return \PHPSession::singleton()->hasAttribute($name);
    }

    public function getSessionAttribute($name)
    {
        return \PHPSession::singleton()->getAttribute($name);
    }

    public function setSessionAttribute($name, $value)
    {
        \PHPSession::singleton()->setAttribute($name, $value);
    }

    public function removeSessionAttribute($name){
        \PHPSession::singleton()->removeAttribute($name);
    }

    public function clearSession($global = true)
    {
        \PHPSession::singleton()->clear($global);
    }

}