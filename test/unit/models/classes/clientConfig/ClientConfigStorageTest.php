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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\clientConfig;

use common_ext_ExtensionsManager;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\asset\AssetService;
use oat\tao\model\clientConfig\ClientConfigService;
use oat\tao\model\clientConfig\ClientConfigStorage;
use oat\tao\model\clientConfig\GetConfigQuery;
use oat\tao\model\ClientLibRegistry;
use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepositoryInterface;
use oat\tao\model\routing\ResolverFactory;
use oat\tao\model\security\xsrf\TokenService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClientConfigStorageTest extends TestCase
{
    /** @var TokenService|MockObject */
    private TokenService $tokenService;

    /** @var ClientLibRegistry|MockObject */
    private ClientLibRegistry $clientLibRegistry;

    /** @var FeatureFlagConfigSwitcher|MockObject */
    private FeatureFlagConfigSwitcher $featureFlagConfigSwitcher;

    /** @var AssetService|MockObject */
    private AssetService $assetService;

    /** @var common_ext_ExtensionsManager|MockObject */
    private common_ext_ExtensionsManager $extensionsManager;

    /** @var ClientConfigService|MockObject */
    private ClientConfigService $clientConfigService;

    /** @var UserLanguageServiceInterface|MockObject */
    private UserLanguageServiceInterface $userLanguageService;

    /** @var FeatureFlagRepositoryInterface|MockObject */
    private FeatureFlagRepositoryInterface $featureFlagRepository;

    /** @var ResolverFactory|MockObject */
    private ResolverFactory $resolverFactory;

    /** @var LoggerInterface|MockObject */
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->tokenService = $this->createMock(TokenService::class);
        $this->clientLibRegistry = $this->createMock(ClientLibRegistry::class);
        $this->featureFlagConfigSwitcher = $this->createMock(FeatureFlagConfigSwitcher::class);
        $this->assetService = $this->createMock(AssetService::class);
        $this->extensionsManager = $this->createMock(common_ext_ExtensionsManager::class);
        $this->clientConfigService = $this->createMock(ClientConfigService::class);
        $this->userLanguageService = $this->createMock(UserLanguageServiceInterface::class);
        $this->featureFlagRepository = $this->createMock(FeatureFlagRepositoryInterface::class);
        $this->resolverFactory = $this->createMock(ResolverFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->sut = new ClientConfigStorage(
            $this->tokenService,
            $this->clientLibRegistry,
            $this->featureFlagConfigSwitcher,
            $this->assetService,
            $this->extensionsManager,
            $this->clientConfigService,
            $this->userLanguageService,
            $this->featureFlagRepository,
            $this->resolverFactory,
            $this->logger
        );
    }

    public function testGetConfig(): void
    {
        $query = $this->createMock(GetConfigQuery::class);
        $query
            ->method('getExtension')
            ->willReturn('extension');
        $query
            ->method('getAction')
            ->willReturn('action');
        $query
            ->method('getModule')
            ->willReturn('module');
        $query
            ->method('getShownExtension')
            ->willReturn('shownExtension');
        $query
            ->method('getShownStructure')
            ->willReturn('shownStructure');
    }
}