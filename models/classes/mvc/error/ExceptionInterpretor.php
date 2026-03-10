<?php

/**
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

use common_exception_MethodNotAllowed;
use common_exception_RestApi;
use common_exception_ValidationFailed;
use Exception;
use common_exception_MissingParameter;
use common_exception_BadRequest;
use common_exception_ResourceNotFound;
use oat\tao\model\mvc\error\HttpStatusCode;
use tao_models_classes_MissingRequestParameterException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use oat\tao\model\exceptions\UserErrorException;

/**
 * Description of ExceptionInterpretor
 *
 * @author Christophe GARCIA <christopheg@taotesting.com>
 */
class ExceptionInterpretor implements ServiceLocatorAwareInterface
{
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
     * @var string[]|null
     */
    protected $allowedRequestMethods;

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
    public function setException(Exception $exception)
    {
        $this->exception = $exception;
        $this->interpretError();
        return $this;
    }
    /**
     * interpret exception type and set up render responseClassName
     * and http status to return
     */
    protected function interpretError()
    {
        $this->responseClassName = 'MainResponse';

        if ($this->exception instanceof common_exception_RestApi) {
            $this->returnHttpCode = $this->exception->getCode() ?: HttpStatusCode::HTTP_BAD_REQUEST;

            return $this;
        }

        switch (get_class($this->exception)) {
            case UserErrorException::class:
            case tao_models_classes_MissingRequestParameterException::class:
            case common_exception_MissingParameter::class:
            case common_exception_BadRequest::class:
            case common_exception_ValidationFailed::class:
                $this->returnHttpCode = HttpStatusCode::HTTP_BAD_REQUEST;
                break;
            case 'tao_models_classes_AccessDeniedException':
            case 'ResolverException':
                $this->returnHttpCode    = HttpStatusCode::HTTP_FORBIDDEN;
                $this->responseClassName = 'RedirectResponse';
                break;
            case 'tao_models_classes_UserException':
                $this->returnHttpCode = HttpStatusCode::HTTP_FORBIDDEN;
                break;
            case 'ActionEnforcingException':
            case 'tao_models_classes_FileNotFoundException':
            case common_exception_ResourceNotFound::class:
                $this->returnHttpCode = HttpStatusCode::HTTP_NOT_FOUND;
                break;
            case common_exception_MethodNotAllowed::class:
                $this->returnHttpCode = HttpStatusCode::HTTP_METHOD_NOT_ALLOWED;
                /** @var common_exception_MethodNotAllowed $exception */
                $exception = $this->exception;
                $this->allowedRequestMethods = $exception->getAllowedMethods();
                break;
            default:
                $this->returnHttpCode = HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR;
                break;
        }
        return $this;
    }

    public function getTrace()
    {
        return $this->exception ? $this->exception->getMessage() : '';
    }

    /**
     * @return integer
     */
    public function getHttpCode()
    {
        return $this->returnHttpCode;
    }
    /**
     * return string
     */
    public function getResponseClassName()
    {
        return __NAMESPACE__ . '\\' . $this->responseClassName;
    }

    /**
     * return an instance of ResponseInterface
     *
     * @return ResponseAbstract
     */
    public function getResponse()
    {
        $class = $this->getResponseClassName();
        /** @var $response ResponseAbstract */
        $response = new $class();
        $response->setServiceLocator($this->getServiceLocator());
        $response->setException($this->exception)
            ->setHttpCode($this->returnHttpCode)
            ->setAllowedMethods($this->allowedRequestMethods)
            ->trace();

        return $response;
    }
}
