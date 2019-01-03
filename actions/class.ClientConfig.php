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
* Copyright (c) 2013-2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
*/

use oat\tao\model\ClientLibConfigRegistry;
use oat\tao\model\ClientLibRegistry;
use oat\tao\model\asset\AssetService;
use oat\tao\model\clientConfig\ClientConfigService;
use oat\tao\model\routing\Resolver;

/**
 * Generates client side configuration.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @package tao

 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class tao_actions_ClientConfig extends tao_actions_CommonModule
{

    /**
     * Get the require.js' config file
     */
    public function config()
    {
        $this->setContentHeader('application/javascript');

        //get extension paths to set up aliases dynamically
        $extensionsAliases = ClientLibRegistry::getRegistry()->getLibAliasMap();
        $this->setData('extensionsAliases', $extensionsAliases);

        $libConfigs = ClientLibConfigRegistry::getRegistry()->getMap();
        $this->setData('libConfigs', $libConfigs);

        $extendedConfig = $this->getServiceLocator()->get(ClientConfigService::SERVICE_ID)->getExtendedConfig();
        foreach ($extendedConfig as $key => $value) {
            $this->setData($key, json_encode($value));
        }

        //use the resolver in order to validate the route
        $resolver = $this->getResolver();

        //loads the URLs context
        /** @var AssetService $assetService */
        $assetService = $this->getServiceLocator()->get(AssetService::SERVICE_ID);
        $tao_base_www = $assetService->getJsBaseWww('tao');
        $this->setData('buster', $assetService->getCacheBuster());

        $base_www = $assetService->getJsBaseWww($resolver->getExtensionId());
        $base_url = $this->getExtension($resolver->getExtensionId())->getConstant('BASE_URL');

        $langCode = tao_helpers_I18n::getLangCode();
        if(strpos($langCode, '-') > 0){
            $lang = strtolower(substr($langCode, 0, strpos($langCode, '-')));
        } else {
            $lang = strtolower($langCode);
        }

        $this->setData('locale', $langCode);
        $this->setData('client_timeout', $this->getClientTimeout());
        $this->setData('crossorigin', $this->isCrossorigin());
        $this->setData('tao_base_www', $tao_base_www);

        $this->setData('context', json_encode([
            'root_url'       => ROOT_URL,
            'base_url'       => $base_url,
            'taobase_www'    => $tao_base_www,
            'base_www'       => $base_www,
            'base_lang'      => $lang,
            'locale'         => $langCode,
            'timeout'        => $this->getClientTimeout(),
            'extension'      => $resolver->getExtensionId(),
            'module'         => $resolver->getControllerShortName(),
            'action'         => $resolver->getMethodName(),
            'shownExtension' => $this->getShownExtension(),
            'shownStructure' => $this->getShownStructure(),
            'bundle'         => tao_helpers_Mode::is(tao_helpers_Mode::PRODUCTION)
        ]));

        $this->setView('client_config.tpl');
    }

    /**
     * Get an extension by it's id
     * @param string $extensionId the extension name/id
     * @return common_ext_Extension the extension
     * @throws Exception if the parameter contains an unknown extension
     */
    private function getExtension($extensionId)
    {
        try {
            return $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById($extensionId);
        } catch(common_ext_ExtensionException $cee){
            throw new Exception(__('Wrong parameter shownExtension'), $cee);
        }
    }

    /**
     * @return bool
     * @throws common_ext_ExtensionException
     */
    protected function isCrossorigin()
    {
        $ext = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('tao');
        $config = $ext->getConfig('js');
        if ($config != null && isset($config['crossorigin'])) {
            return $config['crossorigin'];
        }
        return false;
    }

    /**
     * Get and validate the extension name of the parameter 'shownExtension'
     * @return string the validated extension name
     * @throws Exception if the parameter contains an unknown extension
     */
    private function getShownExtension()
    {
        if($this->hasRequestParameter('shownExtension')){
            $shownExtension = $this->getRequestParameter('shownExtension');
            if(strlen(trim($shownExtension)) > 0){
                $extension = $this->getExtension($shownExtension);
                return $extension->getName();
            }
        }
        return null;
    }

    /**
     * Get and validate the 'shownStructure' parameter
     * @return string the structure id if found in the list
     */
    private function getShownStructure()
    {
        if($this->hasRequestParameter('shownStructure')){
            $structure = $this->getRequestParameter('shownStructure');
            $perspectives = \oat\tao\model\menu\MenuService::getAllPerspectives();
            foreach($perspectives as $perspective){
                if($perspective->getId() == $structure){
                    return $perspective->getId();
                }
            }
        }
        return null;
    }

    /**
     * Get a resolved route from the GET parameters extension/module/action
     * @return Resolver
     * @throws Exception in case a parameter is missing or if the route can't be resolved
     */
    private function getResolver()
    {
        $url = tao_helpers_Uri::url(
            $this->getRequestParameter('action'),
            $this->getRequestParameter('module'),
            $this->hasRequestParameter('extension') ? $this->getRequestParameter('extension') : \Context::getInstance()->getExtensionName()
        );
        try {
            $route = new Resolver(new common_http_Request($url));
            $this->propagate($route);
        } catch (ResolverException $re){
            throw new Exception(__('Wrong or missing parameter extension, module or action'), $re);
        }
        return $route;
    }
}