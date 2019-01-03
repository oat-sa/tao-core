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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\tao\model\actionQueue\event;

use oat\oatbox\event\Event;
use oat\oatbox\user\User;
use oat\tao\model\actionQueue\QueuedAction;

/**
 * Event triggered whenever a new instant action is initialised
 */
class InstantActionOnQueueEvent implements Event
{
    const EVENT_NAME = __CLASS__;

    /**
     * (non-PHPdoc)
     * @see \oat\oatbox\event\Event::getName()
     */
    public function getName()
    {
        return self::EVENT_NAME;
    }

    /**
     * @var string
     */
    private $instantQueueKey = '';

    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $positions = [];

    /**
     * @var QueuedAction
     */
    private $queuedAction = '';

    /**
     * @var string
     */
    private $actionType;

    /**
     * InstantActionOnQueueEvent constructor.
     * @param string $instantQueueKey
     * @param User $user
     * @param array $positions
     * @param string $actionType
     * @param QueuedAction $action
     */
    public function __construct($instantQueueKey, User $user, $positions, $actionType = '', QueuedAction $action = null)
    {
        $this->instantQueueKey = $instantQueueKey;
        $this->user = $user;
        $this->positions = $positions;
        $this->queuedAction = $action;
        $this->actionType = $actionType;
    }

    /**
     * @return string
     */
    public function getInstantQueueKey()
    {
        return $this->instantQueueKey;
    }

    /**
     * @return \oat\oatbox\user\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * @return QueuedAction
     */
    public function getQueuedAction()
    {
        return $this->queuedAction;
    }
}