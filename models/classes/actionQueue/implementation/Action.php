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

use oat\tao\model\actionQueue\Action as ActionInterface;
use oat\tao\model\actionQueue\ActionQueueException;

/**
 * class Action
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package oat\tao\model\actionQueue
 */
class Action implements ActionInterface
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * Action execution result
     * @var mixed
     */
    private $result;

    /**
     * Whether result has been set or not
     * @var boolean
     */
    private $resultWasSet = false;

    /**
     * Action constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * Get action identifier
     * Created based on given callable.
     * @return string
     */
    public function getId()
    {
        if (is_string($this->callable)) {
            $result = $this->callable;
        } else if (is_array($this->callable)) {
            $result = is_object($this->callable[0]) ? get_class($this->callable[0]) : $this->callable[0];
            if (isset($this->callable[1])) {
                $result .= '::' . $this->callable[1];
            }
        } else if (is_object($this->callable)) {
            $result = get_class($this->callable) . '::__invoke';
        }
        return $result;
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
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
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