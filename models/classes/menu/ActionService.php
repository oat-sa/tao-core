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
 * Copyright (c) 2017 Open Assessment Technologies SA;
 */

namespace oat\tao\model\menu;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\accessControl\ActionResolver;
use \common_user_User;
use oat\tao\helpers\ControllerHelper;
use oat\taoBackOffice\model\menuStructure\Action as MenuAction;


/**
 * Manage Menu Actions
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class ActionService extends ConfigurableService
{
    const SERVICE_ID = 'tao/menuaction';

    /**
     * Expose the access levels
     */
    const ACCESS_DENIED    = 0;
    const ACCESS_GRANTED   = 1;
    const ACCESS_UNDEFINED = 2;

    /**
     * Keep an index of resolved actions
     */
    private $resolvedActions = [];

    /**
     * Has a node access to an action
     * @param MenuAction        $action the menu action
     * @param common_user_User  $user   the user to check
     * @param array             $node   the node/resource to verify
     * @return int                      the access level
     */
    public function hasAccess(MenuAction $action, common_user_User $user, array $node){

        $resolvedAction = $this->getResolvedAction($action);
        if(!is_null($resolvedAction) && !is_null($user)){
            if($node['type'] = $resolvedAction['context'] || $resolvedAction['context'] == 'resource') {
                foreach($resolvedAction['required'] as $key){
                    if(!array_key_exists($key, $node)){
                        //missing required key
                        return self::ACCESS_UNDEFINED;
                    }
                }
                try {
                    if(AclProxy::hasAccess($user, $resolvedAction['controller'], $resolvedAction['action'], $node)){
                        return self::ACCESS_GRANTED;
                    }
                    return self::ACCESS_DENIED;
                } catch(\Exception $e){

                    \common_Logger::w('Unable to resolve permission for action ' . $action->getId() . ' : ' . $e->getMessage() );
                }
            }
        }
        return self::ACCESS_UNDEFINED;
    }

    /**
     * Compute the permissions of a node against a list of actions
     * @param MenuAction[]      $actions the menu actions
     * @param common_user_User  $user   the user to check
     * @param array             $node   the node/resource to verify
     * @return array                    the computed permissions as actionId => boolean
     */
    public function computePermissions(array $actions, \common_user_User $user, array $node)
    {
        $permissions = [];
        foreach($actions as $action){
            $access = $this->hasAccess($action, $user, $node);
            if($access != self::ACCESS_UNDEFINED){
                $permissions[$action->getId()] = ($access == self::ACCESS_GRANTED);
            }
        }
        return $permissions;
    }

    /**
     * Get the rights required for the given action
     * @param MenuAction $action
     * @return array the required rights
     */
    public function getRequiredRights(MenuAction $action)
    {
        $rights = [];
        $resolvedAction = $this->getResolvedAction($action);
        if(!is_null($resolvedAction)){
            try {
                $rights  = ControllerHelper::getRequiredRights(
                    $resolvedAction['controller'],
                    $resolvedAction['action']
                );
            } catch(\Exception $e){
                \common_Logger::d('do not handle permissions for action : ' . $action->getName() . ' ' . $action->getUrl());
            }
        }

        return $rights;
    }


    /**
     * Get the action resolved against itself in the current context
     * @param MenuAction $action the action
     * @return array the resolved action
     */
    private function getResolvedAction(MenuAction $action)
    {
        $actionId = $action->getId();
        if(!isset($this->resolvedActions[$actionId])){
            try{
                if($action->getContext() == '*'){
                    //we assume the star context is not permission aware
                    $this->resolvedActions[$actionId] = null;
                }  else {

                    $resolver = new ActionResolver($action->getUrl());
                    $resolvedAction = [
                        'id'         => $action->getId(),
                        'context'    => $action->getContext(),
                        'controller' => $resolver->getController(),
                        'action'     => $resolver->getAction(),
                    ];
                    $resolvedAction['required'] = array_keys(
                        ControllerHelper::getRequiredRights($resolvedAction['controller'], $resolvedAction['action'])
                    );

                    $this->resolvedActions[$actionId] = $resolvedAction;
                }
            } catch(\ResolverException $re) {
                $this->resolvedActions[$actionId] = null;
                \common_Logger::d('do not handle permissions for action : ' . $action->getName() . ' ' . $action->getUrl());
            } catch(\Exception $e){

                $this->resolvedActions[$actionId] = null;
                \common_Logger::d('do not handle permissions for action : ' . $action->getName() . ' ' . $action->getUrl());
            }
        }

        return $this->resolvedActions[$actionId];
    }

}
