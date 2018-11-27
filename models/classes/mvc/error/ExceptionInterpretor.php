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

use Exception;
use common_exception_MissingParameter;
use common_exception_BadRequest;
use tao_models_classes_MissingRequestParameterException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use oat\tao\model\exceptions\UserErrorException;

/**
 * Description of ExceptionInterpretor
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class ExceptionInterpretor implements ServiceLocatorAwareInterface {
    
    use ServiceLocatorAwareTrait;
    
    /**
     *
     * @var Exception 
     */
    protected $exception;
    /**
     *
     * @var integer
     */
    protected $returnHttpCode;
    
    /**
     *
     * @var string 
     */
    protected $responseClassName;
    
    /**
     * 
     * @var string 
     */
    protected $trace = '';

    /**
     * set exception to interpet
     * @param Exception $exception
     * @return ExceptionInterpretor
     */
    public function setException(Exception $exception){
        $this->exception = $exception;
        $this->interpretError();
        return $this;    
    }
    /**
     * interpret exception type and set up render responseClassName
     * and http status to return
     */
    protected function interpretError() {
        $this->trace = $this->exception->getMessage();
        switch (get_class($this->exception)) {
            case UserErrorException::class:
            case tao_models_classes_MissingRequestParameterException::class:
            case common_exception_MissingParameter::class:
            case common_exception_BadRequest::class:
                $this->returnHttpCode = 400;
                $this->responseClassName = 'MainResponse';
            break;
            case 'tao_models_classes_AccessDeniedException':
            case 'ResolverException':
                $this->returnHttpCode    = 403;
                $this->responseClassName = 'RedirectResponse';
            break;
            case 'tao_models_classes_UserException':
                $this->returnHttpCode    = 403;
                $this->responseClassName = 'MainResponse';
            break;
            case 'ActionEnforcingException':
            case 'tao_models_classes_FileNotFoundException':
                $this->returnHttpCode    = 404;
                $this->responseClassName = 'MainResponse';
            break;
            default :
                $this->responseClassName = 'MainResponse';
                $this->returnHttpCode    = 500;
            break;
            
        }
        return $this;
    }
    
    public function getTrace() {
        return $this->trace;
    }

        /**
     * @return integer
     */
    public function getHttpCode(){
        return $this->returnHttpCode;
    }
    /**
     * return string
     */
    public function getResponseClassName() {
        return __NAMESPACE__ . '\\' .$this->responseClassName;
    }
    /**
     *  return an instance of ResponseInterface
     * @return \oat\tao\model\mvc\error\class
     */
    public function getResponse() {
        $class = $this->getResponseClassName();
        /*@var $response ResponseAbstract */
        $response = new $class;
        $response->setServiceLocator($this->getServiceLocator())
                ->setException($this->exception)
                ->setHttpCode($this->returnHttpCode)
                ->trace($this->trace);
        return $response;
    }
    
}
