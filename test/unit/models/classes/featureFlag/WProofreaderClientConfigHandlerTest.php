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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\featureFlag;

use oat\oatbox\session\SessionService;
use oat\tao\model\tenantConfiguration\Service\ConfigurationService;
use oat\tao\model\featureFlag\WProofreaderClientConfigHandler;
use oat\tao\model\user\TaoRoles;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class WProofreaderClientConfigHandlerTest extends TestCase
{
    private const TENANT_ID = 'tenant-id';
    private const CLIENT_MODULE = 'tao/ckeditor/wproofreaderBootstrap';
    private const ORIGINAL_CONFIG = [
        self::CLIENT_MODULE => [
            'spellCheckConfig' => [
                'enabled' => true,
                'providerId' => 'wproofreader',
                'providerConfig' => [
                    'serviceId' => 'installed-service-id',
                    'lang' => 'fr_FR',
                    'autoSearch' => false,
                    'ignoreClasses' => ['math'],
                    'srcUrl' => 'https://installed.example/wscbundle.js',
                ],
            ],
            'otherOption' => true,
        ],
        'other/module' => ['enabled' => true],
    ];

    private ConfigurationService&MockObject $configurationService;
    private SessionService&MockObject $sessionService;
    private WProofreaderClientConfigHandler $subject;

    protected function setUp(): void
    {
        $this->configurationService = $this->createMock(ConfigurationService::class);
        $this->sessionService = $this->createMock(SessionService::class);
        $this->sessionService
            ->method('getTenantId')
            ->willReturn(self::TENANT_ID);
        $session = $this->createMock(\common_session_Session::class);
        $session->method('getUserRoles')->willReturn([TaoRoles::BACK_OFFICE]);
        $this->sessionService->method('getCurrentSession')->willReturn($session);

        $this->subject = new WProofreaderClientConfigHandler($this->configurationService, $this->sessionService);
    }

    public function testAppliesValidatedTenantConfiguration(): void
    {
        $this->configurationService
            ->expects(self::once())
            ->method('fetch')
            ->with('spellchecker.external_providers', self::TENANT_ID, true)
            ->willReturn([
                ['providerName' => 'other', 'enabled' => true],
                [
                    'providerName' => 'wproofreader',
                    'enabled' => true,
                    'config' => [
                        'serviceId' => ' service-id ',
                        'ignored' => true,
                    ],
                ],
            ]);

        $result = ($this->subject)(self::ORIGINAL_CONFIG);

        self::assertSame(
            [
                'enabled' => true,
                'providerId' => 'wproofreader',
                'providerConfig' => [
                    'serviceId' => 'service-id',
                    'lang' => 'fr_FR',
                    'autoSearch' => false,
                    'ignoreClasses' => ['math'],
                    'srcUrl' => 'https://installed.example/wscbundle.js',
                ],
            ],
            $result[self::CLIENT_MODULE]['spellCheckConfig']
        );
        self::assertTrue($result[self::CLIENT_MODULE]['otherOption']);
        self::assertSame(self::ORIGINAL_CONFIG['other/module'], $result['other/module']);
    }

    /**
     * @dataProvider invalidConfigurationProvider
     */
    public function testUsesNativeSpellcheckForInvalidTenantConfiguration(mixed $providers): void
    {
        $this->configurationService
            ->method('fetch')
            ->willReturn($providers);

        $result = ($this->subject)(self::ORIGINAL_CONFIG);

        self::assertSame(
            [
                'enabled' => true,
                'providerId' => 'native',
                'providerConfig' => [],
            ],
            $result[self::CLIENT_MODULE]['spellCheckConfig']
        );
    }

    public function invalidConfigurationProvider(): array
    {
        return [
            'non-array configuration' => ['invalid'],
            'empty providers' => [[]],
            'unrelated provider' => [[['providerName' => 'other', 'enabled' => true]]],
            'disabled provider' => [[[
                'providerName' => 'wproofreader',
                'enabled' => false,
                'config' => ['serviceId' => 'service-id'],
            ]]],
            'missing provider config' => [[[
                'providerName' => 'wproofreader',
                'enabled' => true,
            ]]],
            'blank service ID' => [[[
                'providerName' => 'wproofreader',
                'enabled' => true,
                'config' => ['serviceId' => ' '],
            ]]],
            'non-string service ID' => [[[
                'providerName' => 'wproofreader',
                'enabled' => true,
                'config' => ['serviceId' => 123],
            ]]],
        ];
    }

    public function testPreservesInstalledConfigurationWhenTenantConfigurationIsUnavailable(): void
    {
        $this->configurationService
            ->method('fetch')
            ->willThrowException(new RuntimeException('Configuration not found.', 404));

        self::assertSame(self::ORIGINAL_CONFIG, ($this->subject)(self::ORIGINAL_CONFIG));
    }

    public function testPreservesInstalledConfigurationWithoutFetcher(): void
    {
        $subject = new WProofreaderClientConfigHandler(new ConfigurationService(), $this->sessionService);

        self::assertSame(self::ORIGINAL_CONFIG, $subject(self::ORIGINAL_CONFIG));
    }

    public function testSkipsAnonymousRequests(): void
    {
        $this->sessionService->method('isAnonymous')->willReturn(true);
        $this->configurationService->expects(self::never())->method('fetch');

        self::assertSame(self::ORIGINAL_CONFIG, ($this->subject)(self::ORIGINAL_CONFIG));
    }

    public function testIgnoresMalformedInstalledProviderConfiguration(): void
    {
        $configs = self::ORIGINAL_CONFIG;
        $configs[self::CLIENT_MODULE]['spellCheckConfig']['providerConfig'] = 'invalid';
        $this->configurationService->method('fetch')->willReturn([[
            'providerName' => 'wproofreader',
            'enabled' => true,
            'config' => ['serviceId' => 'tenant-service-id'],
        ]]);

        $result = ($this->subject)($configs);

        self::assertSame(
            ['serviceId' => 'tenant-service-id'],
            $result[self::CLIENT_MODULE]['spellCheckConfig']['providerConfig']
        );
    }

    public function testSkipsRequestsWithoutAuthoringModule(): void
    {
        $configs = ['other/module' => ['enabled' => true]];
        $this->configurationService->expects(self::never())->method('fetch');

        self::assertSame($configs, ($this->subject)($configs));
    }

    public function testSkipsUsersWithoutBackofficeRole(): void
    {
        $sessionService = $this->createMock(SessionService::class);
        $session = $this->createMock(\common_session_Session::class);
        $session->method('getUserRoles')->willReturn(['delivery-role']);
        $sessionService->method('getCurrentSession')->willReturn($session);
        $this->configurationService->expects(self::never())->method('fetch');
        $subject = new WProofreaderClientConfigHandler($this->configurationService, $sessionService);

        self::assertSame(self::ORIGINAL_CONFIG, $subject(self::ORIGINAL_CONFIG));
    }
}
