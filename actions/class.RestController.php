<?php
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
    /**
     * @var array
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
                $this->responseEncoding = (tao_helpers_Http::acceptHeader($this->acceptedMimeTypes, $this->getHeader("Accept")));
            } catch (common_exception_ClientException $e) {
                $this->returnFailure($e);
            }
        }

        header('Content-Type: '.$this->responseEncoding);
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
        if (is_subclass_of($exception, common_Exception::class)) {
            $handler = new tao_helpers_RestExceptionHandler();
            $handler->handle($exception);
        }

        $data = array();
        if ($withMessage) {
            $data['success']	=  false;
            $data['errorCode']	=  $exception->getCode();
            $data['errorMsg']	=  ($exception instanceof common_exception_UserReadableException) ? $exception->getUserMessage() : $exception->getMessage();
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
}