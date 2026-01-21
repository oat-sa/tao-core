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
 * Copyright (c) 2023-2024 (original work) Open Assessment Technologies SA.
 *
 * @author Andrei Shapiro <andrei.shapiro@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\clientConfig;

use common_ext_Extension;
use common_ext_ExtensionsManager;
use common_session_Session;
use oat\generis\model\user\UserRdf;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\User;
use oat\oatbox\user\UserLanguageService;
use oat\tao\helpers\dateFormatter\DateFormatterFactory;
use oat\tao\helpers\dateFormatter\DateFormatterInterface;
use oat\tao\model\asset\AssetService;
use oat\tao\model\clientConfig\ClientConfigService;
use oat\tao\model\clientConfig\ClientConfigStorage;
use oat\tao\model\clientConfig\GetConfigQuery;
use oat\tao\model\ClientLibRegistry;
use oat\tao\model\CookiePolicy\Entity\CookiePolicyConfiguration;
use oat\tao\model\CookiePolicy\Service\CookiePolicyConfigurationRetriever;
use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepositoryInterface;
use oat\tao\model\menu\MenuService;
use oat\tao\model\menu\Perspective;
use oat\tao\model\routing\Resolver;
use oat\tao\model\routing\ResolverFactory;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\model\session\Context\UserDataSessionContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use tao_helpers_Date;
use tao_helpers_Mode;

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

    /** @var UserLanguageService|MockObject */
    private UserLanguageService $userLanguageService;

    /** @var FeatureFlagRepositoryInterface|MockObject */
    private FeatureFlagRepositoryInterface $featureFlagRepository;

    /** @var ResolverFactory|MockObject */
    private ResolverFactory $resolverFactory;

    /** @var LoggerInterface|MockObject */
    private LoggerInterface $logger;

    /** @var SessionService|MockObject */
    private SessionService $sessionService;

    /** @var tao_helpers_Mode|MockObject */
    private tao_helpers_Mode $modeHelper;

    /** @var DateFormatterFactory|MockObject */
    private DateFormatterFactory $dateFormatterFactory;

    /** @var MenuService|MockObject */
    private MenuService $menuService;

    /** @var CookiePolicyConfigurationRetriever|MockObject */
    private CookiePolicyConfigurationRetriever $cookiePolicyConfigurationRetriever;

    private ClientConfigStorage $sut;

    protected function setUp(): void
    {
        // Define ROOT_URL constant if not already defined
        if (!defined('ROOT_URL')) {
            define('ROOT_URL', 'http://demo.taotesting.com/');
        }

        $this->tokenService = $this->createMock(TokenService::class);
        $this->clientLibRegistry = $this->createMock(ClientLibRegistry::class);
        $this->featureFlagConfigSwitcher = $this->createMock(FeatureFlagConfigSwitcher::class);
        $this->assetService = $this->createMock(AssetService::class);
        $this->extensionsManager = $this->createMock(common_ext_ExtensionsManager::class);
        $this->clientConfigService = $this->createMock(ClientConfigService::class);
        $this->userLanguageService = $this->createMock(UserLanguageService::class);
        $this->featureFlagRepository = $this->createMock(FeatureFlagRepositoryInterface::class);
        $this->resolverFactory = $this->createMock(ResolverFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sessionService = $this->createMock(SessionService::class);
        $this->modeHelper = $this->createMock(tao_helpers_Mode::class);
        $this->dateFormatterFactory = $this->createMock(DateFormatterFactory::class);
        $this->menuService = $this->createMock(MenuService::class);
        $this->cookiePolicyConfigurationRetriever = $this->createMock(CookiePolicyConfigurationRetriever::class);

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
            $this->logger,
            $this->sessionService,
            $this->modeHelper,
            $this->dateFormatterFactory,
            $this->menuService,
            $this->cookiePolicyConfigurationRetriever
        );
    }

    public function testGetConfig(): void
    {
        $locale = 'en-US';

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

        $resolver = $this->createMock(Resolver::class);
        $resolver
            ->method('getExtensionId')
            ->willReturn('tao');
        $resolver
            ->method('getControllerShortName')
            ->willReturn('controllerShortName');
        $resolver
            ->method('getMethodName')
            ->willReturn('methodName');

        $this->resolverFactory
            ->expects($this->once())
            ->method('create')
            ->with([
                'extension' => 'extension',
                'action' => 'action',
                'module' => 'module',
            ])
            ->willReturn($resolver);

        $this->assetService
            ->method('getJsBaseWww')
            ->with('tao')
            ->willReturn('JsBaseWww');

        $user = $this->createMock(User::class);
        $user
            ->expects($this->once())
            ->method('getPropertyValues')
            ->with(UserRdf::PROPERTY_LOGIN)
            ->willReturn(['myAdminLogin']);

        $session = $this->createMock(common_session_Session::class);
        $session
            ->expects($this->once())
            ->method('getInterfaceLanguage')
            ->willReturn('en-US');
        $session
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $session
            ->expects($this->once())
            ->method('getUserUri')
            ->willReturn('https://user.taotesting.com');
        $session
            ->expects($this->once())
            ->method('getContexts')
            ->willReturn([
                new UserDataSessionContext('myUserId', 'adminLogin')
            ]);

        $this->sessionService
            ->expects($this->once())
            ->method('getCurrentSession')
            ->willReturn($session);

        $this->sessionService
            ->expects($this->once())
            ->method('getTenantId')
            ->willReturn('777');

        $this->cookiePolicyConfigurationRetriever
            ->expects($this->once())
            ->method('retrieve')
            ->willReturn(
                new CookiePolicyConfiguration(
                    'https://privacyPolicyUrl.taotesting.com',
                    'https://cookiePolicyUrl.taotesting.com',
                    true
                )
            );

        $taoExtension = $this->createMock(common_ext_Extension::class);
        $taoExtension
            ->method('getConfig')
            ->with('js')
            ->willReturn([
                'timeout' => 10,
                'crossorigin' => true,
            ]);
        $taoExtension
            ->method('getConstant')
            ->with('BASE_URL')
            ->willReturn('baseUrl');

        $shownExtension = $this->createMock(common_ext_Extension::class);
        $shownExtension
            ->method('getName')
            ->willReturn('shownExtensionName');

        $this->extensionsManager
            ->method('getExtensionById')
            ->willReturnMap([
                ['tao', $taoExtension],
                ['shownExtension', $shownExtension],
            ]);

        $this->tokenService
            ->expects($this->once())
            ->method('getClientConfig')
            ->willReturn([
                'clientConfigKey' => 'clientConfigValue',
            ]);

        $this->clientLibRegistry
            ->expects($this->once())
            ->method('getLibAliasMap')
            ->willReturn([
                'alias' => 'aliasPath',
            ]);

        $this->featureFlagConfigSwitcher
            ->expects($this->once())
            ->method('getSwitchedClientConfig')
            ->willReturn([
                'clientConfig' => 'clientConfigValue',
            ]);

        $dateFormatter = $this->createMock(DateFormatterInterface::class);
        $dateFormatter
            ->method('getJavascriptFormat')
            ->with(tao_helpers_Date::FORMAT_LONG)
            ->willReturn('DD/MM/YYYY HH:mm:ss');

        $this->dateFormatterFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($dateFormatter);

        $this->assetService
            ->expects($this->once())
            ->method('getCacheBuster')
            ->willReturn('cacheBuster');
        $this->assetService
            ->method('getJsBaseWww')
            ->with('tao')
            ->willReturn('jsBaseWww');

        $this->userLanguageService
            ->method('getAuthoringLanguage')
            ->willReturn('en-US');

        $perspective = $this->createMock(Perspective::class);
        $perspective
            ->method('getId')
            ->willReturn('shownStructure');

        $this->menuService
            ->expects($this->once())
            ->method('retrieveAllPerspectives')
            ->willReturn([$perspective]);

        $this->modeHelper
            ->method('isMode')
            ->with(tao_helpers_Mode::PRODUCTION)
            ->willReturn(false);

        $this->featureFlagRepository
            ->expects($this->once())
            ->method('list')
            ->willReturn([
                'FEATURE_FLAG' => false,
            ]);

        $this->clientConfigService
            ->expects($this->once())
            ->method('getExtendedConfig')
            ->willReturn([
                'extendedConfigKey' => ['extendedConfigValue'],
            ]);
        $this->assertEquals(
            [
                'tokenHandler' => json_encode(['clientConfigKey' => 'clientConfigValue'], JSON_THROW_ON_ERROR),
                'extensionsAliases' => [
                    'alias' => 'aliasPath',
                ],
                'libConfigs' => [
                    'clientConfig' => 'clientConfigValue',
                    'util/locale' => [
                        'dateTimeFormat' => 'DD/MM/YYYY HH:mm:ss',
                    ],
                ],
                'buster' => 'cacheBuster',
                'locale' => $locale,
                'client_timeout' => 10,
                'crossorigin' => true,
                'tao_base_www' => 'JsBaseWww',
                'context' => json_encode(
                    [
                        'tenantId' => '777',
                        'root_url' => 'http://demo.taotesting.com/',
                        'base_url' => 'baseUrl',
                        'taobase_www' => 'JsBaseWww',
                        'base_www' => 'JsBaseWww',
                        'base_lang' => 'en',
                        'locale' => 'en-US',
                        'base_authoring_lang' => 'en-US',
                        'timeout' => 10,
                        'extension' => 'tao',
                        'module' => 'controllerShortName',
                        'action' => 'methodName',
                        'shownExtension' => 'shownExtensionName',
                        'shownStructure' => 'shownStructure',
                        'bundle' => false,
                        'featureFlags' => [
                            'FEATURE_FLAG' => false,
                        ],
                        'cookiePolicy' => [
                            'privacyPolicyUrl' => 'https://privacyPolicyUrl.taotesting.com',
                            'cookiePolicyUrl' => 'https://cookiePolicyUrl.taotesting.com',
                            'display' => true
                        ],
                        'currentUser' => [
                            'id' => 'myUserId',
                            'uri' => 'https://user.taotesting.com',
                            'login' => 'adminLogin'
                        ]
                    ],
                    JSON_THROW_ON_ERROR
                ),
                'extendedConfigKey' => json_encode(['extendedConfigValue'], JSON_THROW_ON_ERROR),
            ],
            $this->sut->getConfig($query)
        );
    }
}
