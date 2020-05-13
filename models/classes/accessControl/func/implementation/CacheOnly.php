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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\accessControl\func\implementation;

use oat\tao\model\accessControl\func\FuncAccessControl;
use oat\tao\model\accessControl\func\AccessRule;
use common_ext_ExtensionsManager;
use oat\oatbox\user\User;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\controllerMap\Factory;
use oat\oatbox\cache\SimpleCache;
use oat\tao\model\accessControl\AccessControl;

/**
 * Simple function access controll implementation, that builds the access
 * right cache based on the extension definitions.
 * Does not require any update script to maintain
 * @author Joel Bout, <joel@taotesting.com>
 */
class CacheOnly extends ConfigurableService implements FuncAccessControl, AccessControl
{
    private const CACHE_PREFIX = 'funcacl::';

    /**
     * (non-PHPdoc)
     * @see AccessControl::hasAccess()
     */
    public function hasAccess(User $user, $controller, $action, $parameters)
    {
        return self::accessPossible($user, $controller, $action);
    }

    /**
     * (non-PHPdoc)
     * @see FuncAccessControl::accessPossible()
     */
    public function accessPossible(User $user, $controllerName, $action)
    {
        $userRoles = $user->getRoles();
        try {
            $controllerAccess = $this->fromCache($controllerName);
            $allowedRoles = $controllerAccess->getAllowedRoles($action);
            $accessAllowed = count(array_intersect($userRoles, $allowedRoles)) > 0;
        } catch (\ReflectionException $e) {
            \common_Logger::i('Unknown controller ' . $controllerName);
            $accessAllowed = false;
        } catch (\common_cache_NotFoundException $e) {
            \common_Logger::i('Unknown controller ' . $controllerName);
            $accessAllowed = false;
        }
        
        return (bool) $accessAllowed;
    }
    
    /**
     * (non-PHPdoc)
     * @see FuncAccessControl::applyRule()
     */
    public function applyRule(AccessRule $rule)
    {
        // nothing to do
    }
    
    /**
     * (non-PHPdoc)
     * @see FuncAccessControl::revokeRule()
     */
    public function revokeRule(AccessRule $rule)
    {
        // nothing to do
    }
    
    public function buildCache(): void
    {
        $extensionManager = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
        $aclModel = new AclModel();
        foreach ($extensionManager->getInstalledExtensions() as $ext) {
            foreach ($ext->getManifest()->getAclTable() as $tableEntry) {
                $rule = new AccessRule($tableEntry[0], $tableEntry[1], $tableEntry[2]);
                $aclModel->applyRule($rule);
            }
        }
        $controllerFactory = $this->getControllerMapFactory();
        foreach ($extensionManager->getInstalledExtensions() as $ext) {
            foreach ($controllerFactory->getControllers($ext->getId()) as $controller) {
                $controllerName = $controller->getClassName();
                $this->toCache($aclModel->getControllerAcl($controllerName));
            }
        }
    }
    
    protected function fromCache($controllerName): ControllerAccessRight
    {
        $cache = $this->getCache()->get(self::CACHE_PREFIX.$controllerName);
        if (is_null($cache)) {
            if (!$this->getControllerMapFactory()->isControllerClassNameValid($controllerName)) {
                // do not rebuild cache if controller is invalid, to prevent attacks
                return new ControllerAccessRight($controllerName);
            }
            $this->buildCache();
            $cache = $this->getCache()->get(self::CACHE_PREFIX.$controllerName);
        }
        return ControllerAccessRight::fromJson($cache);
    }

    protected function toCache(ControllerAccessRight $controller): void 
    {
        $data = json_encode($controller);
        $this->getCache()->set(self::CACHE_PREFIX.$controller->getClassName(), $data);
    }

    protected function getControllerMapFactory(): Factory
    {
        return new Factory();
    }

    protected function getCache(): SimpleCache
    {
        return $this->getServiceLocator()->get(SimpleCache::SERVICE_ID);
    }
}
