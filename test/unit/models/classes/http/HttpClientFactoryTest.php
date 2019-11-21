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
use oat\generis\test\TestCase;
use OAT\Library\CorrelationIds\Registry\CorrelationIdsRegistryInterface;
use oat\tao\model\mvc\CorrelationIdsService;

class HttpClientFactoryTest extends TestCase
{
    public function testCreate()
    {
        $options = ['key' => 'value'];

        $registry = $this->getMock(CorrelationIdsRegistryInterface::class);
        
        $correlationIdsService = $this->getMockBuilder(CorrelationIdsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRegistry'])
            ->getMock();
        $correlationIdsService->method('getRegistry')->willReturn($registry);
        
        $serviceLocator = $this->getServiceLocatorMock([CorrelationIdsService::class => $correlationIdsService]);

        $subject = new HttpClientFactory();
        $subject->setServiceLocator($serviceLocator);

        $client = $subject->create($options);
        
        $this->assertInstanceOf(Client::class, $client);
    }
}
