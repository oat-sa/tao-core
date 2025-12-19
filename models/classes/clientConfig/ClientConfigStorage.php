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

namespace oat\tao\model\clientConfig;

use common_ext_Extension;
use common_ext_ExtensionException;
use common_ext_ExtensionsManager;
use Exception;
use oat\generis\model\user\UserRdf;
use oat\oatbox\session\SessionService;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\helpers\dateFormatter\DateFormatterFactory;
use oat\tao\model\asset\AssetService;
use oat\tao\model\ClientLibRegistry;
use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepositoryInterface;
use oat\tao\model\menu\MenuService;
use oat\tao\model\routing\ResolverFactory;
use oat\tao\model\security\xsrf\TokenService;
use oat\tao\model\session\Context\UserDataSessionContext;
use Psr\Log\LoggerInterface;
use tao_helpers_Date;
use tao_helpers_Mode;
use Throwable;

class ClientConfigStorage
{
    private TokenService $tokenService;
    private ClientLibRegistry $clientLibRegistry;
    private FeatureFlagConfigSwitcher $featureFlagConfigSwitcher;
    private AssetService $assetService;
    private common_ext_ExtensionsManager $extensionsManager;
    private ClientConfigService $clientConfigService;
    private UserLanguageServiceInterface $userLanguageService;
    private FeatureFlagRepositoryInterface $featureFlagRepository;
    private ResolverFactory $resolverFactory;
    private LoggerInterface $logger;
    private SessionService $sessionService;
    private tao_helpers_Mode $modeHelper;
    private DateFormatterFactory $dateFormatterFactory;
    private MenuService $menuService;

    private array $config = [];

    public function __construct(
        TokenService $tokenService,
        ClientLibRegistry $clientLibRegistry,
        FeatureFlagConfigSwitcher $featureFlagConfigSwitcher,
        AssetService $assetService,
        common_ext_ExtensionsManager $extensionsManager,
        ClientConfigService $clientConfigService,
        UserLanguageServiceInterface $userLanguageService,
        FeatureFlagRepositoryInterface $featureFlagRepository,
        ResolverFactory $resolverFactory,
        LoggerInterface $logger,
        SessionService $sessionService,
        tao_helpers_Mode $modeHelper,
        DateFormatterFactory $dateFormatterFactory,
        MenuService $menuService
    ) {
        $this->tokenService = $tokenService;
        $this->clientLibRegistry = $clientLibRegistry;
        $this->featureFlagConfigSwitcher = $featureFlagConfigSwitcher;
        $this->assetService = $assetService;
        $this->extensionsManager = $extensionsManager;
        $this->clientConfigService = $clientConfigService;
        $this->userLanguageService = $userLanguageService;
        $this->featureFlagRepository = $featureFlagRepository;
        $this->resolverFactory = $resolverFactory;
        $this->logger = $logger;
        $this->sessionService = $sessionService;
        $this->modeHelper = $modeHelper;
        $this->dateFormatterFactory = $dateFormatterFactory;
        $this->menuService = $menuService;
    }

    /**
     * Using the DI container you can set configs by providing config path and it's values using environment variables
     * and ect.
     *
     * $services
     *     ->get(ClientConfigStorage::class)
     *     ->call(
     *         'setConfigByPath',
     *         [
     *             [
     *                 'libConfigs' => [
     *                     'somePath' => [
     *                         'someProp' => env('SOME_ENV_VARIABLE')->string(),
     *                     ],
     *                 ],
     *             ],
     *         ]
     *     )
     *     ->call(
     *         'setConfigByPath',
     *         [
     *             [
     *                 'context' => [
     *                     'somePath' => [
     *                         'someProp' => env('ANOTHER_ENV_VARIABLE')->int(),
     *                     ],
     *                 ],
     *             ],
     *         ]
     *     );
     */
    public function setConfigByPath(array $path): void
    {
        $this->config = array_merge_recursive($this->config, $path);
    }

    public function getConfig(GetConfigQuery $query): array
    {
        $resolver = $this->resolverFactory->create([
            'extension' => $query->getExtension(),
            'action' => $query->getAction(),
            'module' => $query->getModule(),
        ]);

        $taoBaseWww = $this->assetService->getJsBaseWww('tao');
        $langCode = $this->sessionService->getCurrentSession()->getInterfaceLanguage();
        $timeout = $this->getClientTimeout();
        $extensionId = $resolver->getExtensionId();

        $this->config = array_merge_recursive(
            [
                TokenService::JS_DATA_KEY => $this->getEncodedValue($this->tokenService->getClientConfig()),
                'extensionsAliases' => $this->clientLibRegistry->getLibAliasMap(),
                'libConfigs' => $this->getLibConfigs(),
                'buster' => $this->assetService->getCacheBuster(),
                'locale' => $langCode,
                'client_timeout' => $timeout,
                'crossorigin' => $this->isCrossorigin(),
                'tao_base_www' => $taoBaseWww,
                'context' => $this->getEncodedValue(
                    [
                        'root_url' => ROOT_URL,
                        'base_url' => $this->getExtension($extensionId)->getConstant('BASE_URL'),
                        'taobase_www' => $taoBaseWww,
                        'base_www' => $this->assetService->getJsBaseWww($extensionId),
                        'base_lang' => $this->getLang($langCode),
                        'locale' => $langCode,
                        'base_authoring_lang' => $this->userLanguageService->getAuthoringLanguage(),
                        'timeout' => $timeout,
                        'extension' => $extensionId,
                        'module' => $resolver->getControllerShortName(),
                        'action' => $resolver->getMethodName(),
                        'shownExtension' => $this->getShownExtension($query),
                        'shownStructure' => $this->getShownStructure($query),
                        'bundle' => $this->modeHelper->isMode(tao_helpers_Mode::PRODUCTION),
                        'featureFlags' => $this->featureFlagRepository->list(),
                        'currentUser' => $this->getUserData($this->sessionService->getCurrentSession()),
                    ]
                ),
            ],
            $this->config
        );

        foreach ($this->clientConfigService->getExtendedConfig() as $key => $value) {
            $this->config[$key] = $this->getEncodedValue($value);
        }

        return $this->config;
    }

    private function getClientTimeout(): int
    {
        $config = $this->getExtension('tao')->getConfig('js');

        return (int) ($config['timeout'] ?? 30);
    }

    private function isCrossOrigin(): bool
    {
        $config = $this->getExtension('tao')->getConfig('js');

        return (bool) ($config['crossorigin'] ?? false);
    }

    private function getExtension(string $extensionId): common_ext_Extension
    {
        try {
            return $this->extensionsManager->getExtensionById($extensionId);
        } catch (common_ext_ExtensionException $e) {
            throw new Exception(__('Wrong parameter shownExtension'), $e);
        }
    }

    private function getShownExtension(GetConfigQuery $command): ?string
    {
        $shownExtension = $command->getShownExtension();

        return empty($shownExtension) ? null : $this->getExtension($shownExtension)->getName();
    }

    private function getShownStructure(GetConfigQuery $command): ?string
    {
        $shownStructure = $command->getShownStructure();

        if ($shownStructure === null) {
            return null;
        }

        foreach ($this->menuService->retrieveAllPerspectives() as $perspective) {
            if ($perspective->getId() === $shownStructure) {
                return $perspective->getId();
            }
        }

        return null;
    }

    private function getLibConfigs(): array
    {
        $libConfigs = $this->featureFlagConfigSwitcher->getSwitchedClientConfig();

        $libConfigs['util/locale']['dateTimeFormat'] = $this->dateFormatterFactory->create()->getJavascriptFormat(
            tao_helpers_Date::FORMAT_LONG
        );

        return $libConfigs;
    }

    private function getLang(string $langCode): string
    {
        $langCodeDashPosition = strpos($langCode, '-');

        return $langCodeDashPosition > 0
            ? strtolower(substr($langCode, 0, $langCodeDashPosition))
            : strtolower($langCode);
    }

    private function getEncodedValue(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            $this->logger->error('Cannot encode client config data: ' . $exception->getMessage());

            return '';
        }
    }

    private function getUserData(\common_session_Session $currentSession): array
    {
        $uri = $currentSession->getUserUri();
        $id = $uri;
        $login = $currentSession->getUserLabel();

        foreach ($currentSession->getUser()->getPropertyValues(UserRdf::PROPERTY_LOGIN) as $rdfLogin) {
            if (!empty($rdfLogin)) {
                $login = $rdfLogin;
            }
        }

        /** @var UserDataSessionContext $context */
        foreach ($currentSession->getContexts(UserDataSessionContext::class) as $context) {
            if ($context->getUserId()) {
                $id = $context->getUserId();
            }

            if ($context->getUserLogin()) {
                $login = $context->getUserLogin();
            }
        }

        return [
            'id' => $id,
            'uri' => $uri,
            'login' => $login,
        ];
    }
}
