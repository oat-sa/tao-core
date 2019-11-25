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
 */

namespace oat\tao\model\http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use OAT\Library\CorrelationIdsGuzzle\Middleware\CorrelationIdsGuzzleMiddleware;
use oat\tao\model\mvc\CorrelationIdsService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class HttpClientFactory implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    const OPTION_HANDLER_STACK = 'handler';
    const CORRELATION_ID_HANDLER_NAME = 'correlation_ids';

    /**
     * @param $options
     * @return Client
     */
    public function create(array $options = [])
    {
        $handlerStack = $this->createOrRetrieveHandlerStack($options);
        $correlationIdsMiddleware = $this->createCorrelationIdsMiddleware();
        $this->insertCorrelationIdsMiddleware($handlerStack, $correlationIdsMiddleware);

        $options[self::OPTION_HANDLER_STACK] = $handlerStack;
        return new Client($options);
    }

    /**
     * @param array $options
     * @return HandlerStack|mixed
     */
    private function createOrRetrieveHandlerStack(array $options)
    {
        return isset($options[self::OPTION_HANDLER_STACK]) && $options[self::OPTION_HANDLER_STACK] instanceof HandlerStack
            ? $options[self::OPTION_HANDLER_STACK]
            : HandlerStack::create();
    }

    /**
     * @return CorrelationIdsGuzzleMiddleware
     */
    private function createCorrelationIdsMiddleware(): CorrelationIdsGuzzleMiddleware
    {
        $registry = $this->getCorrelationIdsService()->getRegistry();
        return new CorrelationIdsGuzzleMiddleware($registry);
    }

    /**
     * @param HandlerStack $handlerStack
     * @param CorrelationIdsGuzzleMiddleware $correlationIdsMiddleware
     */
    private function insertCorrelationIdsMiddleware(HandlerStack $handlerStack, CorrelationIdsGuzzleMiddleware $correlationIdsMiddleware): void
    {
        // Adds correlation ids.
        $handlerStack->push(Middleware::mapRequest($correlationIdsMiddleware), self::CORRELATION_ID_HANDLER_NAME);
    }

    /**
     * @return CorrelationIdsService
     */
    private function getCorrelationIdsService()
    {
        return $this->getServiceLocator()->get(CorrelationIdsService::class);
    }
}
