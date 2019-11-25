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

namespace oat\tao\test\unit\model\http;

use GuzzleHttp\Client;
use function GuzzleHttp\choose_handler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use oat\generis\test\TestCase;
use OAT\Library\CorrelationIds\Provider\CorrelationIdsHeaderNamesProviderInterface;
use OAT\Library\CorrelationIds\Registry\CorrelationIdsRegistry;
use OAT\Library\CorrelationIds\Registry\CorrelationIdsRegistryInterface;
use oat\tao\model\http\HttpClientFactory;
use oat\tao\model\mvc\CorrelationIdsService;

class HttpClientFactoryTest extends TestCase
{
    public function testCreateWithoutHandlerCreatesDefaultHandlerStack()
    {
        $key = 'one key';
        $value = 'one value';

        $registry = $this->createMock(CorrelationIdsRegistryInterface::class);
        $correlationIdsService = $this->createConfiguredMock(CorrelationIdsService::class, ['getRegistry' => $registry]);
        $serviceLocator = $this->getServiceLocatorMock([CorrelationIdsService::class => $correlationIdsService]);

        $subject = new HttpClientFactory();
        $subject->setServiceLocator($serviceLocator);

        $client = $subject->create([$key => $value]);

        $this->assertInstanceOf(Client::class, $client);
        /** @var Client $client */

        $this->assertInstanceOf(HandlerStack::class, $client->getConfig('handler'));
        $this->assertEquals($value, $client->getConfig($key));
    }

    public function testCreateWithExistingHandlerAddsCorrelationIdsHandler()
    {
        $currentId = 'current id';
        $parentId = 'the parent id';
        $rootId = 'id of the root';

        $registry = new CorrelationIdsRegistry($currentId, $parentId, $rootId);
        $correlationIdsService = $this->createConfiguredMock(CorrelationIdsService::class, ['getRegistry' => $registry]);
        $serviceLocator = $this->getServiceLocatorMock([CorrelationIdsService::class => $correlationIdsService]);

        $subject = new HttpClientFactory();
        $subject->setServiceLocator($serviceLocator);

        $calls = [];
        $stack = HistoryAwareHandlerStack::create();
        $stack->push(Middleware::history($calls), HistoryAwareHandlerStack::HISTORY_HANDLER_NAME);

        $client = $subject->create([HttpClientFactory::OPTION_HANDLER_STACK => $stack]);
        
        $client->request('GET', 'http://example.com/');

        $this->assertCount(1, $calls);
        /** @var Request $request */
        $request = $calls[0]['request'];

        $this->assertTrue($request->hasHeader(CorrelationIdsHeaderNamesProviderInterface::DEFAULT_ROOT_CORRELATION_ID_HEADER_NAME));
        $this->assertTrue($request->hasHeader(CorrelationIdsHeaderNamesProviderInterface::DEFAULT_PARENT_CORRELATION_ID_HEADER_NAME));
        
        $actualRootId = $request->getHeader(CorrelationIdsHeaderNamesProviderInterface::DEFAULT_ROOT_CORRELATION_ID_HEADER_NAME);
        $this->assertEquals($rootId, $actualRootId[0]);

        // Parent id for new request has to be this request current id.
        $actualParentId = $request->getHeader(CorrelationIdsHeaderNamesProviderInterface::DEFAULT_PARENT_CORRELATION_ID_HEADER_NAME);        
        $this->assertEquals($currentId, $actualParentId[0]);
    }
}

class HistoryAwareHandlerStack extends HandlerStack
{
    public const HISTORY_HANDLER_NAME = 'history';

    public static function create(callable $handler = null)
    {
        return new self(choose_handler());
    }
    
    /**
     * If an history middleware is present, insert before it, so that it can actually see the effects of the other middlewares inserted.
     * @param callable $middleware
     * @param string   $name
     */
    public function push(callable $middleware, $name = '')
    {
        if ($name === self::HISTORY_HANDLER_NAME) {
            parent::push($middleware, $name);
        } else {
            $this->before(self::HISTORY_HANDLER_NAME, $middleware, $name);
        }
    }
}
