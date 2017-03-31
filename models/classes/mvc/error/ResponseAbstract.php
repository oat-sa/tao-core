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

namespace oat\tao\model\mvc\error;

use common_Logger;
use Context;
use Exception;
use HTTPToolkit;
use tao_helpers_Request;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Description of ResponseAbstract
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
abstract class ResponseAbstract implements ResponseInterface, ServiceLocatorAwareInterface {
    
    use ServiceLocatorAwareTrait;
    
    /**
     * http response code
     * @var integer 
     */
    protected $httpCode;
    /**
     * content type to use into response header
     * @var string 
     */
    protected $contentType = '';
    
    /**
     * @var Exception
     */
    protected $exception;


    protected $rendererClassList =
            [
                'html' => 'HtmlResponse',
                'json' => 'JsonResponse',
                'none' => 'NonAcceptable',
                'ajax' => 'AjaxResponse',
            ];


    /**
     * search rendering method in function of request accept header
     * @param array $accept
     * @return ResponseAbstract
     */
    protected function chooseRenderer(array $accept) {
        $renderClass = 'none';
        foreach ($accept as $mimeType) {

            switch (trim(strtolower($mimeType))) {
                case 'text/html' : 
                case 'application/xhtml+xml':
                case '*/*':
                    $renderClass = 'html';
                    break 2;
                case 'application/json' :
                case 'text/json' : 
                    $renderClass = 'json';
                    break 2;
            }
            
        }

        if(tao_helpers_Request::isAjax()) {
            $renderClass = 'ajax';
        }

        $className = __NAMESPACE__ . '\\' . $this->rendererClassList[$renderClass];
        
        $renderer = new $className();
        
        return $renderer->setServiceLocator($this->getServiceLocator());
    }
    /**
     * send headers
     * @return $this
     */
    protected function sendHeaders() {
        $context = Context::getInstance();
        $context->getResponse()->setContentHeader($this->contentType);
        header(HTTPToolkit::statusCodeHeader($this->httpCode));
        return $this;
    }
    /**
     * set response http status code
     * @param int $code
     * @return $this
     */
    public function setHttpCode($code) {
        $this->httpCode = $code;
        return $this;
    }
    
    /**
     * @inherit
     */
    public function send()
    {
        $accept = array_key_exists('HTTP_ACCEPT', $_SERVER) ? explode(',' , $_SERVER['HTTP_ACCEPT']) : [];
        $renderer = $this->chooseRenderer($accept);

        return $renderer->setException($this->exception)->setHttpCode($this->httpCode)->sendHeaders()->send();
    }
    
    /**
     * @inherit
     */
    public function trace($message) {
        
        common_Logger::i($message);
        
        return $this;
    }

    /**
     * set up exception
     * @param Exception $exception
     * @return $this
     */
    public function setException(Exception $exception) {
        $this->exception = $exception;
        return $this;
    }
    
}
