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
* Copyright (c) 2013-2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
*/

declare(strict_types=1);

use oat\tao\model\featureFlag\FeatureFlagConfigSwitcher;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepositoryInterface;
use oat\tao\model\menu\MenuService;
use oat\tao\model\routing\Resolver;
use tao_helpers_Date as DateHelper;
use oat\tao\model\ClientLibRegistry;
use oat\tao\model\asset\AssetService;
use oat\oatbox\user\UserLanguageService;
use oat\tao\model\security\xsrf\TokenService;
use oat\oatbox\user\UserLanguageServiceInterface;
use oat\tao\model\clientConfig\ClientConfigService;

/**
 * Generates client side configuration.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 *
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class tao_actions_ClientConfig extends tao_actions_CommonModule
{
    /**
     * Get the require.js' config file
     */
    public function config(): void
    {
        $this->setContentHeader('application/javascript');

        $this->setData(
            TokenService::JS_DATA_KEY,
            json_encode($this->getTokenService()->getClientConfig())
        );

        // Get extension paths to set up aliases dynamically
        $extensionsAliases = ClientLibRegistry::getRegistry()->getLibAliasMap();
        $this->setData('extensionsAliases', $extensionsAliases);

        $featureVisibility = $this->getFeatureFlagConfigSwitcher();
        $libConfigs = $featureVisibility->getSwitchedClientConfig();
        // Dynamically adds the date format.
        $formatter = DateHelper::getDateFormatter();
        $libConfigs['util/locale']['dateTimeFormat'] = $formatter->getJavascriptFormat(DateHelper::FORMAT_LONG);
        $this->setData('libConfigs', $libConfigs);

        foreach ($this->getClientConfigService()->getExtendedConfig() as $key => $value) {
            $this->setData($key, json_encode($value));
        }

        // Use the resolver in order to validate the route
        $resolver = $this->getResolver();

        // Loads the URLs context
        $assetService = $this->getAssetService();
        $taoBaseWww = $assetService->getJsBaseWww('tao');
        $this->setData('buster', $assetService->getCacheBuster());

        $baseWww = $assetService->getJsBaseWww($resolver->getExtensionId());
        $baseUrl = $this->getExtension($resolver->getExtensionId())->getConstant('BASE_URL');

        $langCode = tao_helpers_I18n::getLangCode();
        $langCodeDashPosition = strpos($langCode, '-');
        $lang = $langCodeDashPosition > 0
            ? strtolower(substr($langCode, 0, $langCodeDashPosition))
            : strtolower($langCode);

        $this->setData('locale', $langCode);
        $this->setData('client_timeout', $this->getClientTimeout());
        $this->setData('crossorigin', $this->isCrossorigin());
        $this->setData('tao_base_www', $taoBaseWww);

        $this->setData('context', json_encode([
            'root_url' => ROOT_URL,
            'base_url' => $baseUrl,
            'taobase_www' => $taoBaseWww,
            'base_www' => $baseWww,
            'base_lang' => $lang,
            'locale' => $langCode,
            'base_authoring_lang' => $this->getUserLanguageService()->getAuthoringLanguage(),
            'timeout' => $this->getClientTimeout(),
            'extension' => $resolver->getExtensionId(),
            'module' => $resolver->getControllerShortName(),
            'action' => $resolver->getMethodName(),
            'shownExtension' => $this->getShownExtension(),
            'shownStructure' => $this->getShownStructure(),
            'bundle' => tao_helpers_Mode::is(tao_helpers_Mode::PRODUCTION),
            'featureFlags' => $this->getFeatureFlagRepository()->list(),
        ]));

        $this->setView('client_config.tpl');
    }

    /**
     * @return bool
     *
     * @throws common_ext_ExtensionException
     */
    protected function isCrossorigin()
    {
        $config = $this->getExtensionManager()->getExtensionById('tao')->getConfig('js');

        return $config['crossorigin'] ?? false;
    }

    /**
     * @param string $extensionId
     */
    private function getExtension($extensionId): common_ext_Extension
    {
        try {
            return $this->getExtensionManager()->getExtensionById($extensionId);
        } catch (common_ext_ExtensionException $cee) {
            throw new Exception(__('Wrong parameter shownExtension'), $cee);
        }
    }

    private function getShownExtension(): ?string
    {
        if ($this->hasRequestParameter('shownExtension')) {
            $shownExtension = $this->getRequestParameter('shownExtension');

            if (strlen(trim($shownExtension)) > 0) {
                $extension = $this->getExtension($shownExtension);

                return $extension->getName();
            }
        }

        return null;
    }

    private function getShownStructure(): ?string
    {
        if ($this->hasRequestParameter('shownStructure')) {
            $structure = $this->getRequestParameter('shownStructure');

            foreach (MenuService::getAllPerspectives() as $perspective) {
                if ($perspective->getId() === $structure) {
                    return $perspective->getId();
                }
            }
        }

        return null;
    }

    private function getResolver(): Resolver
    {
        $extension = $this->hasRequestParameter('extension')
            ? $this->getRequestParameter('extension')
            : Context::getInstance()->getExtensionName();

        $url = tao_helpers_Uri::url(
            $this->getRequestParameter('action'),
            $this->getRequestParameter('module'),
            $extension
        );

        try {
            $route = new Resolver(new common_http_Request($url));
            $this->propagate($route);
        } catch (ResolverException $exception) {
            throw new Exception(__('Wrong or missing parameter extension, module or action'), $exception);
        }

        return $route;
    }

    private function getTokenService(): TokenService
    {
        return $this->getPsrContainer()->get(TokenService::SERVICE_ID);
    }

    private function getClientConfigService(): ClientConfigService
    {
        return $this->getPsrContainer()->get(ClientConfigService::SERVICE_ID);
    }

    private function getAssetService(): AssetService
    {
        return $this->getPsrContainer()->get(AssetService::SERVICE_ID);
    }

    private function getUserLanguageService(): UserLanguageServiceInterface
    {
        return $this->getPsrContainer()->get(UserLanguageService::SERVICE_ID);
    }

    private function getExtensionManager(): common_ext_ExtensionsManager
    {
        return $this->getPsrContainer()->get(common_ext_ExtensionsManager::SERVICE_ID);
    }

    private function getFeatureFlagRepository(): FeatureFlagRepositoryInterface
    {
        return $this->getPsrContainer()->get(FeatureFlagRepositoryInterface::class);
    }

    private function getFeatureFlagConfigSwitcher(): FeatureFlagConfigSwitcher
    {
        return $this->getPsrContainer()->get(FeatureFlagConfigSwitcher::class);
    }
}
