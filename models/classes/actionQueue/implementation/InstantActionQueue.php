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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\actionQueue\implementation;

use oat\oatbox\event\EventManagerAwareTrait;
use oat\tao\model\actionQueue\ActionQueue;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\actionQueue\QueuedAction;
use oat\tao\model\actionQueue\ActionQueueException;
use oat\oatbox\user\User;
use oat\tao\model\actionQueue\restriction\basicRestriction;
use oat\tao\model\actionQueue\event\InstantActionOnQueueEvent;

/**
 *
 * Interface InstantActionQueue
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package oat\tao\model\actionQueue
 */
class InstantActionQueue extends ConfigurableService implements ActionQueue
{

    use EventManagerAwareTrait;

    const QUEUE_TREND = 'queue_trend';

    /**
     * @param QueuedAction $action
     * @param User $user
     * @return boolean
     * @throws
     */
    public function perform(QueuedAction $action, User $user = null)
    {
        $action->setServiceLocator($this->getServiceManager());
        if ($user === null) {
            $user = \common_session_SessionManager::getSession()->getUser();
        }
        $result = false;
        $actionConfig = $this->getActionConfig($action);
        $restrictions = $this->getRestrictions($actionConfig);
        $allowExecution = $this->checkRestrictions($restrictions);

        if ($allowExecution) {
            $actionResult = $action([]);
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
        $action->setServiceLocator($this->getServiceManager());
        $positions = $this->getPositions($action);
        return count($positions);
    }

    /**
     * @inheritdoc
     */
    public function clearAbandonedPositions(QueuedAction $action)
    {
        $action->setServiceLocator($this->getServiceManager());
        $key = $this->getQueueKey($action);
        $positions = $this->getPositions($action);
        $edgeTime = time() - $this->getTtl($action);
        $newPositions = array_filter($positions, function ($val) use ($edgeTime) {
            return $val > $edgeTime;
        });
        $this->getPersistence()->set($key, json_encode($newPositions));
        $this->getPersistence()->set(get_class($action) . self::QUEUE_TREND, 0);
        return count($positions) - count($newPositions);
    }

    /**
     * @return int
     */
    public function getTrend(QueuedAction $action)
    {
        $trend = $this->getPersistence()->get(get_class($action) . self::QUEUE_TREND);
        return (int)$trend;
    }

    /**
     * @param QueuedAction $action
     * @return int
     * @throws ActionQueueException
     */
    public function getLimits(QueuedAction $action)
    {
        $actionConfig = $this->getActionConfig($action);
        $restrictions = $this->getRestrictions($actionConfig);
        $limit = $restrictions ? array_sum($restrictions) : 0;
        return $limit;
    }

    /**
     * @param QueuedAction $action
     * @param User $user
     * @throws \common_Exception
     */
    protected function queue(QueuedAction $action, User $user)
    {
        $key = $this->getQueueKey($action);
        $positions = $this->getPositions($action);
        $positions[$user->getIdentifier()] = time();
        $this->getPersistence()->set($key, json_encode($positions));
        $this->getEventManager()->trigger(new InstantActionOnQueueEvent($key, $user, $positions, 'queue', $action));
        if ($this->getTrend($action) >= 0) {
            $this->getPersistence()->set(get_class($action) . self::QUEUE_TREND, -1);
        }
    }

    /**
     * @param QueuedAction $action
     * @param User $user
     * @throws \common_Exception
     */
    protected function dequeue(QueuedAction $action, User $user)
    {
        $key = $this->getQueueKey($action);
        $positions = $this->getPositions($action);
        if (array_key_exists($user->getIdentifier(), $positions)) {
            // now we sure that this user has been queued
            unset($positions[$user->getIdentifier()]);
            $this->getEventManager()->trigger(new InstantActionOnQueueEvent($key, $user, $positions, 'dequeue', $action));
            $this->getPersistence()->set($key, json_encode($positions));
            
            if ($this->getTrend($action) <= 0) {
                $this->getPersistence()->set(get_class($action) . self::QUEUE_TREND, 1);
            }
        }
    }

    /**
     * @return \common_persistence_KeyValuePersistence
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
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
        $ttl = (int) (isset($actionConfig[self::ACTION_PARAM_TTL]) ? $actionConfig[self::ACTION_PARAM_TTL] : 0);
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

    /**
     * @param QueuedAction $action
     * @return array
     */
    protected function getPositions(QueuedAction $action)
    {
        $key = $this->getQueueKey($action);
        $positions = json_decode($this->getPersistence()->get($key), true);
        if (!$positions) {
            $positions = [];
        }
        return $positions;
    }

    /**
     * @param array $actionConfig
     * @return array
     */
    private function getRestrictions(array $actionConfig)
    {
        return array_key_exists('restrictions', $actionConfig) ? $actionConfig['restrictions'] : [];
    }

    /**
     * @param array $restrictions
     * @return bool
     */
    private function checkRestrictions(array $restrictions)
    {
        $allowExecution = true;

        foreach ($restrictions as $restriction => $value) {
            if (class_exists($restriction) && is_subclass_of($restriction, basicRestriction::class)) {
                /** @var basicRestriction $r */
                $r = new $restriction();
                $this->propagate($r);
                $allowExecution = $allowExecution && $r->doesComplies($value);
            }
        }
        return $allowExecution;
    }
}