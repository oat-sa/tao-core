<?php
use oat\tao\helpers\RestExceptionHandler;

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 */

abstract class tao_actions_RestController extends \tao_actions_CommonModule
{
    const CLASS_URI_PARAM = 'class-uri';
    const CLASS_LABEL_PARAM = 'class-label';

    /**
     * @var array
     * @deprecated since 4.3.0
     */
    private $acceptedMimeTypes = array("application/json", "text/xml", "application/xml", "application/rdf+xml");

    /**
     * @var NULL|string
     */
    private $responseEncoding = "application/json";

    /**
     * Check response encoding requested
     *
     * tao_actions_RestModule constructor.
     */
    public function __construct()
    {
        if ($this->hasHeader("Accept")) {
            try {
                $this->responseEncoding = (tao_helpers_Http::acceptHeader($this->getAcceptableMimeTypes(), $this->getHeader("Accept")));
            } catch (common_exception_ClientException $e) {
                $this->returnFailure($e);
            }
        }

        header('Content-Type: '.$this->responseEncoding);
    }
    
    /**
     * return http Accepted mimeTypes
     * 
     * @author Christophe GARCIA
     * @return array
     */
    protected function getAcceptableMimeTypes()
    {
        return $this->acceptedMimeTypes;
    }
    
    /**
     * Return failed Rest response
     * Set header http by using handle()
     * If $withMessage is true:
     *     Send response with success, code, message & version of TAO
     *
     * @param Exception $exception
     * @param $withMessage
     * @throws common_exception_NotImplemented
     */
    protected function returnFailure(Exception $exception, $withMessage=true)
    {
        $handler = new RestExceptionHandler();
        $handler->sendHeader($exception);

        $data = array();
        if ($withMessage) {
            $data['success']	=  false;
            $data['errorCode']	=  $exception->getCode();
            $data['errorMsg']	=  $this->getErrorMessage($exception);
            $data['version']	= TAO_VERSION;
        }

        echo $this->encode($data);
        exit(0);
    }

    /**
     * Return success Rest response
     * Send response with success, data & version of TAO
     *
     * @param array $rawData
     * @param bool $withMessage
     * @throws common_exception_NotImplemented
     */
    protected function returnSuccess($rawData = array(), $withMessage=true)
    {
        $data = array();
        if ($withMessage) {
            $data['success'] = true;
            $data['data'] 	 = $rawData;
            $data['version'] = TAO_VERSION;
        } else {
            $data = $rawData;
        }

        echo $this->encode($data);
        exit(0);
    }

    /**
     * Encode data regarding responseEncoding
     *
     * @param $data
     * @return string
     * @throws common_exception_NotImplemented
     */
    protected function encode($data)
    {
        switch ($this->responseEncoding){
            case "application/rdf+xml":
                throw new common_exception_NotImplemented();
                break;
            case "text/xml":
            case "application/xml":
                return tao_helpers_Xml::from_array($data);
            case "application/json":
            default:
                return json_encode($data);
        }
    }

    /**
     * Get class instance from request parameters
     * If more than one class with given label exists the first open will be picked up.
     * @param core_kernel_classes_Class $rootClass
     * @return core_kernel_classes_Class|null
     * @throws common_exception_RestApi
     */
    protected function getClassFromRequest(\core_kernel_classes_Class $rootClass)
    {
        $class = null;
        if ($this->hasRequestParameter(self::CLASS_URI_PARAM) && $this->hasRequestParameter(self::CLASS_LABEL_PARAM)) {
            throw new \common_exception_RestApi(
                self::CLASS_URI_PARAM . ' and ' . self::CLASS_LABEL_PARAM . ' parameters do not supposed to be used simultaneously.'
            );
        }

        if ($this->hasRequestParameter(self::CLASS_URI_PARAM)) {
            $class = new \core_kernel_classes_Class($this->getRequestParameter(self::CLASS_URI_PARAM));
        }
        if ($this->hasRequestParameter(self::CLASS_LABEL_PARAM)) {
            $label = $this->getRequestParameter(self::CLASS_LABEL_PARAM);
            foreach ($rootClass->getSubClasses(true) as $subClass) {
                if ($subClass->getLabel() === $label) {
                    $class = $subClass;
                    break;
                }
            }
        }
        if ($class === null || !$class->exists()) {
            $class = $rootClass;
        }
        return $class;
    }

    /**
     * Generate safe message preventing exposing sensitive date in non develop mode
     * @param Exception $exception
     * @return string
     */
    private function getErrorMessage(Exception $exception)
    {
        $defaultMessage =  __('Unexpected error. Please contact administrator');
        if (DEBUG_MODE) {
            $defaultMessage = $exception->getMessage();
        }
        return ($exception instanceof common_exception_UserReadableException) ? $exception->getUserMessage() :  $defaultMessage;
    }
}