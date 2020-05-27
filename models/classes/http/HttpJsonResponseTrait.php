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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\http;

use oat\tao\model\http\formatter\ResponseFormatter;
use oat\tao\model\http\response\ErrorJsonResponse;
use oat\tao\model\http\response\SuccessJsonResponse;
use Psr\Http\Message\ResponseInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

trait HttpJsonResponseTrait
{
    /** @var ResponseInterface */
    protected $response;

    protected function setSuccessJsonResponse(array $data, int $statusCode = 200): void
    {
        $this->response = $this->getResponseFormatter()
            ->withJsonHeader()
            ->withStatusCode($statusCode)
            ->withBody(new SuccessJsonResponse($data))
            ->format($this->response);
    }

    protected function setErrorJsonResponse(
        string $errorMessage,
        int $errorCode = 0,
        array $data = [],
        int $statusCode = 400
    ): void
    {
        $this->response = $this->getResponseFormatter()
            ->withJsonHeader()
            ->withStatusCode($statusCode)
            ->withBody(new ErrorJsonResponse($errorCode, $errorMessage, $data))
            ->format($this->response);
    }

    protected function getResponseFormatter(): ResponseFormatter
    {
        return $this->getServiceLocator()->get(ResponseFormatter::class);
    }

    /**
     * @return ServiceLocatorInterface
     */
    abstract public function getServiceLocator();
}
