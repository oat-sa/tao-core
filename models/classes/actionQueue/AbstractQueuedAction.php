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

namespace oat\tao\model\actionQueue;

use oat\oatbox\extension\AbstractAction;
use oat\oatbox\log\LoggerAwareTrait;

/**
 * class Action
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package oat\tao\model\actionQueue
 */
abstract class AbstractQueuedAction extends AbstractAction implements QueuedAction
{

    use LoggerAwareTrait;

    /**
     * Action execution result
     * @var mixed
     */
    protected $result;

    /**
     * Whether result has been set or not
     * @var boolean
     */
    protected $resultWasSet = false;

    /**
     * @inheritdoc
     */
    abstract public function getNumberOfActiveActions();

    /**
     * Get action identifier
     * @return string
     */
    public final function getId()
    {
        return static::class;
    }

    /**
     * Return result of action execution
     * @return mixed
     * @throws ActionQueueException
     */
    public function getResult()
    {
        if (!$this->resultWasSet) {
            throw new ActionQueueException(__('Action was not executed yet'));
        }
        return $this->result;
    }

    /**
     * Set result of action
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->resultWasSet = true;
        $this->result = $result;
    }
}