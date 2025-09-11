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
 * Copyright (c) 2019-2025 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\webhooks\task;

use common_exception_InvalidArgumentType;
use GuzzleHttp\Exception\GuzzleException;
use oat\generis\test\ServiceManagerMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\webhooks\configEntity\WebhookAuthInterface;
use oat\tao\model\webhooks\task\WebhookSender;
use Psr\Http\Message\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;

class WebhookSenderTest extends TestCase
{
    use ServiceManagerMockTrait;

    /**
     * @throws GuzzleException
     * @throws common_exception_InvalidArgumentType
     */
    public function testPerformRequestWithAuth()
    {
        /** @var MockObject|RequestInterface $request */
        $request = $this->createMock(RequestInterface::class);

        /** @var MockObject|WebhookAuthInterface $authConfig */
        $authConfig = $this->createMock(WebhookAuthInterface::class);
        $authConfig->method('getAuthClass')->willReturn(AuthTypeFake::class);
        $authConfig->method('getCredentials')->willReturn(['c' => 'v']);

        $sender = new WebhookSender();
        $sender->setServiceLocator($this->getServiceManagerMock());
        /** @var \stdClass $response */
        $response = $sender->performRequest($request, $authConfig);

        self::assertSame($request, $response->callRequest);
        self::assertSame(['c' => 'v'], $response->credentials);
    }
}
