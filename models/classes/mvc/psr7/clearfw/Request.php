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

namespace oat\tao\model\mvc\psr7\clearfw;

/**
 * mapping between clear fw request and psr7 request
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class Request {

    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';
    const HTTP_HEAD = 'HEAD';

    /**
     *
     * @var \GuzzleHttp\Psr7\ServerRequest
     */
    protected $psrRequest;

    /**
     * 
     * @param \GuzzleHttp\Psr7\ServerRequest $request
     * @return $this
     */
    public function setPsrRequest(\GuzzleHttp\Psr7\ServerRequest $request) {
        $this->psrRequest = $request;
        return $this;
    }

    /**
     * @return \GuzzleHttp\Psr7\ServerRequest
     */
    public function getPsrRequest() {
        return $this->psrRequest;
    }

    public function getParameter($name) {
        $params = array_merge((array) $this->psrRequest->getParsedBody(), $this->psrRequest->getQueryParams());
        if(array_key_exists($name, $params)) {
            return $params[$name];
        }
        return null;
    }

    public function hasParameter($name) {
        $params = array_merge((array) $this->psrRequest->getParsedBody(), $this->psrRequest->getQueryParams());
        return array_key_exists($name, $params);
    }

    public function addParameters($parameters) {
        var_dump($parameters);die();
    }

    public function getParameters() {
        return array_merge((array) $this->psrRequest->getParsedBody(), $this->psrRequest->getQueryParams());
    }

    public function getHeader($string) {
        return $this->psrRequest->getHeader($string);
    }

    public function getHeaders() {
        return $this->psrRequest->getHeaders();
    }

    public function hasHeader($string) {
        return $this->psrRequest->hasHeader($string);
    }

    public function hasCookie($name) {
        return array_key_exists($name, $_COOKIE);
    }

    public function getCookie($name) {
        return $_COOKIE[$name];
    }

    public function getMethod() {
        return $this->psrRequest->getMethod();
    }

    public function isGet() {
        return ($this->psrRequest->getMethod() === self::HTTP_GET);
    }

    public function isPost() {
        return ($this->psrRequest->getMethod() === self::HTTP_POST);
    }

    public function isPut() {
        return ($this->psrRequest->getMethod() === self::HTTP_PUT);
    }

    public function isDelete() {
        return ($this->psrRequest->getMethod() === self::HTTP_DELETE);
    }

    public function isHead() {
        return ($this->psrRequest->getMethod() === self::HTTP_HEAD);
    }

    public function getUserAgent() {
        return $this->psrRequest->getHeader('USER_AGENT');
    }

    public function getQueryString() {
        return $this->psrRequest->getHeader('QUERY_STRING');
    }

    public function getRequestURI() {
        return $this->psrRequest->getHeader('REQUEST_URI');
    }

    public function getRawParameters() {
        return $this->getParameters();
    }

    public function accept($mime) {
        //extract the mime-types
        $accepts = array_map(function($value) {
            if (strpos($value, ';')) {
                //remove the priority ie. q=0.3
                $value = substr($value, 0, strrpos($value, ';'));
            }
            return trim($value);
        }, explode(',', $this->psrRequest->getHeader('HTTP_ACCEPT')));

        foreach ($accepts as $accept) {
            if ($accept == $mime) {
                return true;
            }
            //check the star type
            if (preg_match("/^\*\//", $accept)) {
                return true;
            }
            //check the star sub-type
            if (preg_match("/\/\*$/", $accept)) {
                $acceptType = substr($accept, 0, strpos($accept, '/'));
                $checkType = substr($mime, 0, strpos($mime, '/'));
                if ($acceptType == $checkType) {
                    return true;
                }
            }
        }
        return false;
    }

}
