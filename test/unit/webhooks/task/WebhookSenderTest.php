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

namespace oat\tao\test\unit\webhooks\task;

use GuzzleHttp\Exception\GuzzleException;
use oat\generis\test\ServiceManagerMockTrait;
use oat\taoOauth\model\bootstrap\OAuth2AuthType;
use oat\taoOauth\model\Oauth2Service;
use oat\taoOauth\model\OAuthClient;
use oat\taoOauth\model\storage\grant\OauthCredentials;
use oat\taoOauth\model\storage\OauthCredentialsFactory;
use PHPUnit\Framework\TestCase;
use oat\tao\model\webhooks\configEntity\WebhookAuthInterface;
use oat\tao\model\webhooks\task\WebhookSender;
use Psr\Http\Message\RequestInterface;
use oat\generis\test\MockObject;
use Psr\Http\Message\ResponseInterface;

class WebhookSenderTest extends TestCase
{
    use ServiceManagerMockTrait;

    /**
     * @throws GuzzleException
     * @throws \common_exception_InvalidArgumentType
     */
    public function testPerformRequestWithAuth()
    {
        /** @var MockObject|RequestInterface $request */
        $request = $this->createMock(RequestInterface::class);
        $OauthCredentialsFactoryMock = $this->createMock(OauthCredentialsFactory::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $OauthCredentialsMock = $this->createMock(OauthCredentials::class);
        $OAuthClientMock = $this->createMock(OAuthClient::class);
        $Oauth2ServiceMock = $this->createMock(Oauth2Service::class);
        $OauthCredentialsFactoryMock->method('getCredentialTypeByCredentials')->willReturn($OauthCredentialsMock);
        $Oauth2ServiceMock->method('getClient')->willReturn($OAuthClientMock);
        $OAuthClientMock->method('request')->willReturn($responseMock);

        /** @var MockObject|WebhookAuthInterface $authConfig */
        $authConfig = $this->createMock(WebhookAuthInterface::class);

        $authConfig
            ->expects(self::once())
            ->method('getAuthClass')
            ->willReturn(OAuth2AuthType::class);

        $authConfig
            ->expects(self::once())
            ->method('getCredentials')
            ->willReturn(['c' => 'v']);

        $sender = new WebhookSender();
        $sender->setServiceLocator($this->getServiceManagerMock([
            OauthCredentialsFactory::class => $OauthCredentialsFactoryMock,
            Oauth2Service::SERVICE_ID => $Oauth2ServiceMock
        ]));

        $sender->performRequest($request, $authConfig);
    }
}
