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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\actionQueue\event;

use oat\oatbox\event\Event;
use oat\tao\model\actionQueue\QueuedAction;

/**
 * Class ActionQueueTrendEvent
 *
 * Action triggered when trend was changed for an action
 *
 * @package oat\tao\model\actionQueue\event
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ActionQueueTrendEvent implements Event
{
    const EVENT_NAME = __CLASS__;

    /** @var QueuedAction */
    private $action;

    /** @var boolean */
    private $queued;

    /**
     * ActionQueueTrendEvent constructor.
     * @param QueuedAction $action
     * @param boolean $queued - if action was queued or moved out from queue
     */
    public function __construct(QueuedAction $action, $queued)
    {
        $this->action = $action;
        $this->queued = $queued;
    }

    /**
     * @return QueuedAction
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @see \oat\oatbox\event\Event::getName()
     */
    public function getName()
    {
        return self::EVENT_NAME;
    }

    /**
     * @return bool
     */
    public function isQueued()
    {
        return $this->queued;
    }
}
