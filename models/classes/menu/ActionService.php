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
 * Copyright (c) 2017-2021 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\model\menu;

use Throwable;
use common_user_User;
use ResolverException;
use Psr\Log\LoggerInterface;
use oat\tao\helpers\ControllerHelper;
use oat\oatbox\log\logger\AdvancedLogger;
use oat\tao\model\accessControl\AclProxy;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\accessControl\ActionResolver;
use oat\oatbox\log\logger\extender\ContextExtenderInterface;
use oat\taoBackOffice\model\menuStructure\Action as MenuAction;

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class ActionService extends ConfigurableService
{
    public const SERVICE_ID = 'tao/menuaction';

    public const ACCESS_DENIED = 0;
    public const ACCESS_GRANTED = 1;
    public const ACCESS_UNDEFINED = 2;

    /** Keep an index of resolved actions */
    private $resolvedActions = [];

    /**
     * @return int The access level
     */
    public function hasAccess(MenuAction $action, common_user_User $user, array $node)
    {
        $resolvedAction = $this->getResolvedAction($action);
        $advancedLogger = $this->getAdvancedLogger();

        if ($resolvedAction !== null && $user !== null) {
            if ($node['type'] = $resolvedAction['context'] || $resolvedAction['context'] == 'resource') {
                foreach ($resolvedAction['required'] as $key) {
                    if (!array_key_exists($key, $node)) {
                        $advancedLogger->info(
                            sprintf(
                                'Undefined access level (%d): missing required key "%s".',
                                self::ACCESS_UNDEFINED,
                                $key
                            )
                        );

                        return self::ACCESS_UNDEFINED;
                    }
                }

                try {
                    return AclProxy::hasAccess($user, $resolvedAction['controller'], $resolvedAction['action'], $node)
                        ? self::ACCESS_GRANTED
                        : self::ACCESS_DENIED;
                } catch (Throwable $exception) {
                    $advancedLogger->error(
                        sprintf(
                            'Unable to resolve permission for action "%s": %s',
                            $action->getId(),
                            $exception->getMessage()
                        ),
                        [ContextExtenderInterface::CONTEXT_EXCEPTION => $exception]
                    );
                }
            }
        }

        $advancedLogger->info(
            sprintf(
                'Undefined access level (%d).',
                self::ACCESS_UNDEFINED
            )
        );

        return self::ACCESS_UNDEFINED;
    }

    /**
     * Compute the permissions of a node against a list of actions (as actionId => boolean)
     *
     * @param MenuAction[] $actions
     *
     * @return array
     */
    public function computePermissions(array $actions, common_user_User $user, array $node)
    {
        $permissions = [];

        foreach ($actions as $action) {
            $access = $this->hasAccess($action, $user, $node);

            if ($access !== self::ACCESS_UNDEFINED) {
                $permissions[$action->getId()] = $access === self::ACCESS_GRANTED;
            }
        }

        return $permissions;
    }

    /**
     * Get the rights required for the given action
     *
     * @return array
     */
    public function getRequiredRights(MenuAction $action)
    {
        $rights = [];
        $resolvedAction = $this->getResolvedAction($action);

        if ($resolvedAction !== null) {
            try {
                $rights = ControllerHelper::getRequiredRights(
                    $resolvedAction['controller'],
                    $resolvedAction['action']
                );
            } catch (Throwable $exception) {
                $this->getAdvancedLogger()->error(
                    sprintf(
                        'Do not handle permissions for action: %s %s',
                        $action->getName(),
                        $action->getUrl()
                    ),
                    [
                        ContextExtenderInterface::CONTEXT_EXCEPTION => $exception,
                    ]
                );
            }
        }

        return $rights;
    }


    /**
     * Get the action resolved against itself in the current context
     *
     * @return array
     */
    private function getResolvedAction(MenuAction $action)
    {
        $actionId = $action->getId();

        if (!isset($this->resolvedActions[$actionId])) {
            try {
                if ($action->getContext() == '*') {
                    // We assume the star context is not permission aware
                    $this->resolvedActions[$actionId] = null;
                } else {
                    $resolver = new ActionResolver($action->getUrl());
                    $resolvedAction = [
                        'id' => $action->getId(),
                        'context' => $action->getContext(),
                        'controller' => $resolver->getController(),
                        'action' => $resolver->getAction(),
                    ];
                    $resolvedAction['required'] = array_keys(
                        ControllerHelper::getRequiredRights($resolvedAction['controller'], $resolvedAction['action'])
                    );

                    $this->resolvedActions[$actionId] = $resolvedAction;
                }
            } catch (ResolverException | Throwable $exception) {
                $this->resolvedActions[$actionId] = null;

                $this->getAdvancedLogger()->error(
                    sprintf(
                        'Do not handle permissions for action: %s %s',
                        $action->getName(),
                        $action->getUrl()
                    ),
                    [ContextExtenderInterface::CONTEXT_EXCEPTION => $exception]
                );
            }
        }

        return $this->resolvedActions[$actionId];
    }

    private function getAdvancedLogger(): LoggerInterface
    {
        return $this->getServiceManager()->getContainer()->get(AdvancedLogger::ACL_LOGGER);
    }
}
