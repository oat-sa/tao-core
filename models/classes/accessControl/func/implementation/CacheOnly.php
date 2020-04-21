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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\accessControl\func\implementation;

use oat\tao\model\accessControl\func\FuncAccessControl;
use oat\tao\model\accessControl\func\AccessRule;
use common_ext_ExtensionsManager;
use oat\oatbox\user\User;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\controllerMap\Factory;

/**
 * Simple ACL Implementation deciding whenever or not to allow access
 * strictly by the BASEUSER role and a whitelist
 *
 * Not to be used in production, since testtakers cann access the backoffice
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 * @package tao

 */
class CacheOnly extends ConfigurableService implements FuncAccessControl
{
    private const CACHE_PREFIX = 'funcacl::';

    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\accessControl\func\FuncAccessControl::accessPossible()
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
     * @see \oat\tao\model\accessControl\func\FuncAccessControl::applyRule()
     */
    public function applyRule(AccessRule $rule)
    {
        // nothing to do
    }
    
    /**
     * (non-PHPdoc)
     * @see \oat\tao\model\accessControl\func\FuncAccessControl::revokeRule()
     */
    public function revokeRule(AccessRule $rule)
    {
        // nothing to do
    }
    
    public function buildCache()
    {
        $extensionManager = $this->getServiceLocator()->get(common_ext_ExtensionsManager::SERVICE_ID);
        $aclModel = new AclModel();
        foreach ($extensionManager->getInstalledExtensions() as $ext) {
            foreach ($ext->getManifest()->getAclTable() as $tableEntry) {
                $rule = new AccessRule($tableEntry[0], $tableEntry[1], $tableEntry[2]);
                $aclModel->applyRule($rule);
            }
        }
        $controllerFactory = new Factory();
        foreach ($extensionManager->getInstalledExtensions() as $ext) {
            foreach ($controllerFactory->getControllers($ext->getId()) as $controller) {
                $controllerName = $controller->getClassName();
                $this->toCache($aclModel->getControllerAcl($controllerName));
            }
        }
    }
    
    protected function fromCache($controllerName): ControllerAccessRight
    {
        try {
            return ControllerAccessRight::fromJson($this->getCache()->get(self::CACHE_PREFIX.$controllerName));
        } catch (\common_cache_NotFoundException $e) {
            $this->buildCache();
        }
    }

    protected function toCache(ControllerAccessRight $controller): void 
    {
        $data = json_encode($controller);
        $this->getCache()->put($data, self::CACHE_PREFIX.$controller->getClassName());
    }

    protected function getCache(): \common_cache_Cache
    {
        return $this->getServiceLocator()->get(\common_cache_Cache::SERVICE_ID);
    }
}
