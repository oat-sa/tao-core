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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 *
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use oat\generis\test\TestCase;
use oat\tao\model\Lists\Business\Domain\Value;
use RuntimeException;

class RemoteSourceTest extends TestCase
{
    /** @var MockHandler */
    private $clientMock;
    /** @var Client */
    private $guzzleClient;

    protected function setUp(): void
    {
        $this->clientMock = new MockHandler();

        $handlerStack = HandlerStack::create($this->clientMock);

        $this->guzzleClient = new Client(['handler' => $handlerStack]);

    }

    public function testNoParsers(): void
    {
        $this->expectException(RuntimeException::class);

//        $parser = $this->createMock(RemoteSourceParserInterface::class);

        $this->clientMock->append(new Response(200, [], '{}'));

        $resource = new RemoteSource([], $this->guzzleClient);
        $result = $resource->fetch('', '', '', '');

        iterator_to_array($result);
    }

    public function testFetch(): void
    {
        $parser = $this->createMock(RemoteSourceParserInterface::class);
        $parser->expects(self::once())->method('iterate')->willReturn([new Value(1, 'url', 'label')]);

        $this->clientMock->append(new Response(200, [], '{}'));

        $resource = new RemoteSource(['parser_name' => $parser], $this->guzzleClient);
        $result = $resource->fetch('', '', '', 'parser_name');

        $array = iterator_to_array($result);

        $this->assertEquals(new Value(1, 'url', 'label'), $array[0]);
    }
}
