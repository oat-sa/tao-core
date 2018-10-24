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

use oat\oatbox\user\User;

/**
 * Interface ActionQueue
 *
 * configuration example:
 * ```php
 * new InstantActionQueue([
 *      //persistence to store actions in queue
 *      InstantActionQueue::OPTION_PERSISTENCE => 'key_value_persistence',
 *
 *      //registered actions
 *      InstantActionQueue::OPTION_ACTIONS => [
 *          SomeAction::class => [
 *              //limit of active actions. 0 means that queue is disabled for this action
 *              InstantActionQueue::ACTION_PARAM_LIMIT => 10,
 *              //time to live
 *              InstantActionQueue::ACTION_PARAM_TTL => 30,
 *          ]
 *      ]
 *  ]);
 * ```
 *
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package oat\tao\model\actionQueue
 */
interface ActionQueue
{

    const SERVICE_ID = 'tao/ActionQueue';

    /**
     * List of registered actions to be performed using action queue
     */
    const OPTION_ACTIONS = 'actions';

    /**
     * Persistence identifier
     */
    const OPTION_PERSISTENCE = 'persistence';

    /**
     * Limit of actions in progress.
     * If number of active actions will be more that this value then action will be put into queue
     */
    const ACTION_PARAM_LIMIT = 'limit';

    /**
     * Time to live for place in the queue (seconds). Configures per actions.
     */
    const ACTION_PARAM_TTL = 'ttl';

    /**
     * @param QueuedAction $action
     * @param User $user user which tries to perform action
     * @return boolean
     */
    public function perform(QueuedAction $action, User $user = null);

    /**
     * @param QueuedAction $action
     * @param User $user
     * @return integer
     */
    public function getPosition(QueuedAction $action, User $user = null);

    /**
     * @param QueuedAction $action
     * @return integer Number of removed positions
     */
    public function clearAbandonedPositions(QueuedAction $action);

    /**
     * @param QueuedAction $action
     * @return integer Number of limits
     */
    public function getLimits(QueuedAction $action);

}