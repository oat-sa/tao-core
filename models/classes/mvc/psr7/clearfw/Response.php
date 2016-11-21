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
 * mapping between clear fw response and psr7 response
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class Response {
    
    /**
     * @var \GuzzleHttp\Psr7\Response
     */
    protected $psrResponse;

    public function setPsrResponse($response) {
        $this->psrResponse = $response;
        return $this;
    }
    
    public function getPsrResponse($response) {
        return $this->psrResponse;
    }
    
    public function getCharset() {
        $contentType = explode(',' , $this->psrResponse->getHeader('Content-Type'));
        
        if(count($contentType) > 1) {
            $charset  = explode('=' , $contentType[1]);
            return $charset[1]; 
        }
        
        return '';
    }
    
    public function getContentType() {
        $contentType = explode(',' , $this->psrResponse->getHeader('Content-Type'));
        return $contentType[0];
    }
    
    public function setContentHeader($contentType, $charset = 'UTF-8'){
        return $this->psrResponse->withHeader('Content-Type', $contentType . '; charset=' . $charset);
    }
    
    public function setCookie($name, $value = null, $expire = null, $domainPath = null, $https = null, $httpOnly = null) {
        return setcookie($name, $value, $expire, $domainPath, $https, $httpOnly);
    }
    
}
