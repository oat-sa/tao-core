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
use oat\tao\model\actionQueue\Action;
use oat\tao\model\actionQueue\ActionQueueException;
/**
 *
 *
 * Interface InstantActionQueue
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package oat\tao\model\actionQueue
 */
class InstantActionQueue extends ConfigurableService implements ActionQueue
{

    /**
     * @param Action $action
     * @return boolean
     * @throws
     */
    public function perform(Action $action)
    {
        $result = false;
        $actionConfig = $this->getActionConfig($action);
        $limit = intval(isset($actionConfig[self::ACTION_PARAM_LIMIT]) ? $actionConfig[self::ACTION_PARAM_LIMIT] : 0);
        if ($limit === 0 || $limit > $action->getNumberOfActiveActions()) {
            $actionResult = $action();
            $action->setResult($actionResult);
            $result = true;
            $this->dequeue($action);
        } else {
            $this->queue($action);
        }
        return $result;
    }

    /**
     * @param Action $action
     * @return integer
     */
    public function getPosition(Action $action)
    {
        return intval($this->getPersistence()->get($this->getPositionKey($action)));
    }

    /**
     * @param Action $action
     */
    protected function queue(Action $action)
    {
        $position = $this->getPersistence()->get($this->getPositionKey($action));
        if (!$position) {
            $position = 0;
        }
        $position++;
        $this->getPersistence()->set($this->getPositionKey($action), $position);
    }

    /**
     * @param Action $action
     */
    protected function dequeue(Action $action)
    {
        $position = $this->getPersistence()->get($this->getPositionKey($action));
        if (is_integer($position) && $position > 0) {
            $position--;
            $this->getPersistence()->set($this->getPositionKey($action), $position);
        }
    }

    /**
     * @return \common_persistence_KeyValuePersistence
     */
    protected function getPersistence()
    {
        $persistenceId = $this->getOption(self::OPTION_PERSISTENCE);
        return $this->getServiceManager()->get('generis/persistences')->getPersistenceById($this->getOption(self::OPTION_PERSISTENCE));
    }

    /**
     * @param Action $action
     * @return string
     */
    protected function getPositionKey(Action $action)
    {
        return self::class . '_' . $action->getId() . '_' .'_position';
    }

    /**
     * @param Action $action
     * @throws ActionQueueException in action was not registered in the config
     * @return array
     */
    protected function getActionConfig(Action $action)
    {
        $actions = $this->getOption(self::OPTION_ACTIONS);
        if (!isset($actions[$action->getId()])) {
            throw new ActionQueueException(__('Action `%s` is not configured in the action queue service', $action->getId()));
        }
        return $actions[$action->getId()];
    }
}