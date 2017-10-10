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

use oat\oatbox\action\Action as TaoAction;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Interface QueuedAction
 *
 * Action to be performed in action queue
 *
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package oat\tao\model\actionQueue
 */
interface QueuedAction extends TaoAction, ServiceLocatorAwareInterface
{

    /**
     * Get action identifier
     * @return mixed
     */
    public function getId();

    /**
     * Get number of actions in progress.
     * Based on this value action queue will decide whether action should be performed or not.
     * @return integer
     */
    public function getNumberOfActiveActions();

    /**
     * Return result of action execution
     * @return mixed
     */
    public function getResult();

    /**
     * Set result of action
     * @param mixed $result
     */
    public function setResult($result);
}