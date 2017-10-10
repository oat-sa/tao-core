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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\actionQueue\implementation;

use oat\tao\model\actionQueue\ActionQueue;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\actionQueue\QueuedAction;
use oat\tao\model\actionQueue\ActionQueueException;
use oat\oatbox\user\User;

/**
 *
 * Interface InstantActionQueue
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package oat\tao\model\actionQueue
 */
class InstantActionQueue extends ConfigurableService implements ActionQueue
{

    /**
     * @param QueuedAction $action
     * @param User $user
     * @return boolean
     * @throws
     */
    public function perform(QueuedAction $action, User $user = null)
    {
        if ($user === null) {
            $user = \common_session_SessionManager::getSession()->getUser()->getIdentifier();
        }
        $result = false;
        $actionConfig = $this->getActionConfig($action);
        $limit = intval(isset($actionConfig[self::ACTION_PARAM_LIMIT]) ? $actionConfig[self::ACTION_PARAM_LIMIT] : 0);
        if ($limit === 0 || $limit > $action->getNumberOfActiveActions()) {
            $actionResult = $action();
            $action->setResult($actionResult);
            $result = true;
            $this->dequeue($action, $user);
        } else {
            $this->queue($action, $user);
        }
        return $result;
    }

    /**
     * Note that this method is not transaction safe so there may be collisions.
     * This implementation supposed to provide approximate position in the queue
     * @param QueuedAction $action
     * @param User $user
     * @return integer
     * @throws
     */
    public function getPosition(QueuedAction $action, User $user = null)
    {
        $positions = unserialize($this->getPersistence()->get($this->getQueueKey($action)));
        return count($positions);
    }

    /**
     * @inheritdoc
     */
    public function clearAbandonedPositions(QueuedAction $action)
    {
        $key = $this->getQueueKey($action);
        $positions = unserialize($this->getPersistence()->get($key));
        $edgeTime = time() - $this->getTtl($action);
        $newPositions = array_filter($positions, function ($val) use ($edgeTime) {
            return $val > $edgeTime;
        });
        $this->getPersistence()->set($key, serialize($newPositions));
        return count($positions) - count($newPositions);
    }

    /**
     * @param QueuedAction $action
     * @param User $user
     */
    protected function queue(QueuedAction $action, User $user)
    {
        $key = $this->getQueueKey($action, $user);
        $positions = unserialize($this->getPersistence()->get($key));
        $positions[$user->getIdentifier()] = time();
        $this->getPersistence()->set($key, serialize($positions));
    }

    /**
     * @param QueuedAction $action
     * @param User $user
     */
    protected function dequeue(QueuedAction $action, User $user)
    {
        $key = $this->getQueueKey($action, $user);
        $positions = unserialize($this->getPersistence()->get($key));
        unset($positions[$user->getIdentifier()]);
        $this->getPersistence()->set($key, serialize($positions));
    }

    /**
     * @return \common_persistence_KeyValuePersistence
     */
    protected function getPersistence()
    {
        $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
        return $this->getServiceManager()->get(\common_persistence_Manager::SERVICE_ID)->getPersistenceById($persistenceId);
    }

    /**
     * @param QueuedAction $action
     * @throws
     * @return integer
     */
    protected function getTtl(QueuedAction $action)
    {
        $actionConfig = $this->getActionConfig($action);
        $ttl = intval(isset($actionConfig[self::ACTION_PARAM_TTL]) ? $actionConfig[self::ACTION_PARAM_TTL] : 0);
        return $ttl;
    }

    /**
     * @param QueuedAction $action
     * @return string
     */
    protected function getQueueKey(QueuedAction $action)
    {
        return self::class . '_' . $action->getId();
    }

    /**
     * @param QueuedAction $action
     * @throws ActionQueueException in action was not registered in the config
     * @return array
     */
    protected function getActionConfig(QueuedAction $action)
    {
        $actions = $this->getOption(self::OPTION_ACTIONS);
        if (!isset($actions[$action->getId()])) {
            throw new ActionQueueException(__('Action `%s` is not configured in the action queue service', $action->getId()));
        }
        return $actions[$action->getId()];
    }
}