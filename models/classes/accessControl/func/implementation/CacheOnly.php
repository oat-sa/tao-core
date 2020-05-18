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
            $controllerAccess = $this->getController($controllerName);
            $allowedRoles = $controllerAccess->getAllowedRoles($action);
            $accessAllowed = count(array_intersect($userRoles, $allowedRoles)) > 0;
        } catch (\ReflectionException $e) {
            $this->logInfo('Unknown controller ' . $controllerName);
            $accessAllowed = false;
        } catch (\common_cache_NotFoundException $e) {
            $this->logInfo('Unknown controller ' . $controllerName);
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
        $aclModel = $this->buildModel();
        $this->cacheModel($aclModel);
    }

    /**
     * Returns the access rights of a controller, either read from cache
     * or triggers a regeneration ofthe cache
     */
    protected function getController($controllerName): ControllerAccessRight
    {
        $cache = $this->getCache()->get(self::CACHE_PREFIX.$controllerName);
        if (is_null($cache)) {
            if (!$this->getControllerMapFactory()->isControllerClassNameValid($controllerName)) {
                // do not rebuild cache if controller is invalid, to prevent CPU consumtion attacks
                // return empty controller instead
                return new ControllerAccessRight($controllerName);
            }
            // as we need to parse all manifests, it is easier to write whole cache in one go
            $this->buildCache();
            $cache = $this->getCache()->get(self::CACHE_PREFIX.$controllerName);
        }
        return ControllerAccessRight::fromJson($cache);
    }

    protected function buildModel(): AclModel
    {
        $aclModel = new AclModel();
        foreach ($this->getExtensionManager()->getInstalledExtensions() as $ext) {
            foreach ($ext->getManifest()->getAclTable() as $tableEntry) {
                $rule = new AccessRule($tableEntry[0], $tableEntry[1], $tableEntry[2]);
                $aclModel->applyRule($rule);
            }
        }
        return $aclModel;
    }

    /**
     * Cache the acl model, ensuring to write all controllers,
     * not just controllers with access rights to prevent
     * unncesessary regeneration of the cache
     */
    protected function cacheModel(AclModel $aclModel): void
    {
        $controllerFactory = $this->getControllerMapFactory();
        foreach ($this->getExtensionManager()->getInstalledExtensions() as $ext) {
            foreach ($controllerFactory->getControllers($ext->getId()) as $controller) {
                $controllerName = $controller->getClassName();
                $this->cacheController($aclModel->getControllerAcl($controllerName, $ext->getId()));
            }
        }
    }

    private function cacheController(ControllerAccessRight $controller): void
    {
        $data = json_encode($controller);
        $this->getCache()->set(self::CACHE_PREFIX.$controller->getClassName(), $data);
    }

    private function getExtensionManager(): common_ext_ExtensionsManager
    {
        return $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
    }

    private function getControllerMapFactory(): Factory
    {
        return new Factory();
    }

    private function getCache(): SimpleCache
    {
        return $this->getServiceLocator()->get(SimpleCache::SERVICE_ID);
    }
}
