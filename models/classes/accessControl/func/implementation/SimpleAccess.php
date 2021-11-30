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
 * Copyright (c) 2013-2021 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\accessControl\func\implementation;

use oat\oatbox\user\User;
use Psr\Log\LoggerInterface;
use oat\tao\model\user\TaoRoles;
use oat\generis\model\GenerisRdf;
use common_ext_ExtensionsManager;
use oat\tao\helpers\ControllerHelper;
use oat\oatbox\log\logger\AdvancedLogger;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\FuncHelper;
use oat\tao\model\accessControl\func\FuncAccessControl;

/**
 * Simple ACL Implementation deciding whenever or not to allow access
 * strictly by the BASE_USER role and a whitelist
 *
 * Not to be used in production, since test-takers can access the backoffice
 *
 * @author Joel Bout, <joel@taotesting.com>
 */
class SimpleAccess extends ConfigurableService implements FuncAccessControl
{
    public const WHITELIST_KEY = 'SimpleAclWhitelist';

    /** @var array */
    private $controllers = [];

    public function __construct($options = [])
    {
        parent::__construct($options);

        $data = common_ext_ExtensionsManager::singleton()
            ->getExtensionById('tao')
            ->getConfig(self::WHITELIST_KEY);

        if (is_array($data)) {
            $this->controllers = $data;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function accessPossible(User $user, $controller, $action)
    {
        foreach ($user->getRoles() as $role) {
            if ($role === TaoRoles::BASE_USER) {
                return true;
            }
        }

        $inWhiteList = $this->inWhiteList($controller, $action);

        if ($inWhiteList === false) {
            $this->getAdvancedLogger()->info('Access denied.');
        }

        return $inWhiteList;
    }

    public function applyRule(AccessRule $rule)
    {
        if ($rule->getRole()->getUri() == GenerisRdf::INSTANCE_ROLE_ANONYMOUS) {
            $mask = $rule->getMask();

            if (is_string($mask)) {
                if (strpos($mask, '@') == false) {
                    $this->whiteListController($mask);
                } else {
                    [$controller, $action] = explode('@', $mask, 2);
                    $this->whiteListAction($controller, $action);
                }
            } else {
                if (isset($mask['ext']) && !isset($mask['mod'])) {
                    $this->whiteListExtension($mask['ext']);
                } elseif (isset($mask['ext']) && isset($mask['mod']) && !isset($mask['act'])) {
                    $this->whiteListController(FuncHelper::getClassName($mask['ext'], $mask['mod']));
                } elseif (isset($mask['ext']) && isset($mask['mod']) && isset($mask['act'])) {
                    $this->whiteListAction(FuncHelper::getClassName($mask['ext'], $mask['mod']), $mask['act']);
                } elseif (isset($mask['controller'])) {
                    $this->whiteListController($mask['controller']);
                } elseif (isset($mask['act']) && strpos($mask['act'], '@') !== false) {
                    [$controller, $action] = explode('@', $mask['act'], 2);
                    $this->whiteListAction($controller, $action);
                } else {
                    $this->getAdvancedLogger()->warning(
                        sprintf(
                            'Unrecognised mask keys: %s',
                            implode(',', array_keys($mask))
                        )
                    );
                }
            }
        }
    }

    public function revokeRule(AccessRule $rule)
    {
        if ($rule->getRole()->getUri() === GenerisRdf::INSTANCE_ROLE_ANONYMOUS) {
            $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');

            $this->controllers = $ext->hasConfig(self::WHITELIST_KEY) ? $ext->getConfig(self::WHITELIST_KEY) : [];
            $mask = $rule->getMask();

            if (isset($mask['ext']) && !isset($mask['mod'])) {
                foreach (ControllerHelper::getControllers($mask['ext']) as $controllerClassName) {
                    unset($this->controllers[$controllerClassName]);
                }
            } elseif (isset($mask['ext']) && isset($mask['mod']) && !isset($mask['act'])) {
                unset($this->controllers[FuncHelper::getClassName($mask['ext'], $mask['mod'])]);
            } elseif (isset($mask['ext']) && isset($mask['mod']) && isset($mask['act'])) {
                $controller = FuncHelper::getClassName($mask['ext'], $mask['mod']);

                if (isset($this->controllers[$controller])) {
                    unset($this->controllers[$controller][$mask['act']]);

                    if (0 === count($this->controllers[$controller])) {
                        unset($this->controllers[$controller]);
                    }
                }
            } elseif (isset($mask['controller'])) {
                unset($this->controllers[$mask['controller']]);
            } elseif (isset($mask['act']) && strpos($mask['act'], '@') !== false) {
                [$controller, $action] = explode('@', $mask['act'], 2);

                if (isset($this->controllers[$controller])) {
                    unset($this->controllers[$controller][$action]);

                    if (0 === count($this->controllers[$controller])) {
                        unset($this->controllers[$controller]);
                    }
                }
            } else {
                $this->getAdvancedLogger()->warning(
                    sprintf(
                        'Unrecognised mask keys: %s',
                        implode(',', array_keys($mask))
                    )
                );
            }

            $ext->setConfig(self::WHITELIST_KEY, $this->controllers);
        }
    }

    private function inWhiteList($controllerName, $action)
    {
        return isset($this->controllers[$controllerName])
            && (
                !is_array($this->controllers[$controllerName])
                || isset($this->controllers[$controllerName][$action])
            );
    }

    private function whiteListExtension($extensionId)
    {
        foreach (ControllerHelper::getControllers($extensionId) as $controllerClassName) {
            $this->whiteListController($controllerClassName);
        }
    }

    private function whiteListController($controller)
    {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        // reread controllers to reduce collision risk
        $this->controllers = $ext->hasConfig(self::WHITELIST_KEY) ? $ext->getConfig(self::WHITELIST_KEY) : [];
        $this->controllers[$controller] = '*';
        $ext->setConfig(self::WHITELIST_KEY, $this->controllers);
    }

    private function whiteListAction($controller, $action)
    {
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        // reread controllers to reduce collision risk
        $this->controllers = $ext->hasConfig(self::WHITELIST_KEY) ? $ext->getConfig(self::WHITELIST_KEY) : [];

        if (!isset($this->controllers[$controller]) || is_array($this->controllers[$controller])) {
            $this->controllers[$controller][$action] = '*';
        }

        $ext->setConfig(self::WHITELIST_KEY, $this->controllers);
    }

    private function getAdvancedLogger(): LoggerInterface
    {
        return $this->getServiceManager()->getContainer()->get(AdvancedLogger::ACL_SERVICE_ID);
    }
}
