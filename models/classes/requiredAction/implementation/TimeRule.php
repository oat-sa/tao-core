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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\model\requiredAction\implementation;

use oat\tao\model\requiredAction\RequiredActionRuleInterface;
use \DateTime;
use \DateInterval;

/**
 * Class TimeRule
 * @author Aleh Hutnilau <hutnikau@1pt.com>
 */
class TimeRule implements RequiredActionRuleInterface
{

    /**
     * @var string|DateTime
     */
    private $executionTime;

    /**
     * @var string|DateInterval
     */
    private $interval;

    /**
     * TimeRule constructor.
     * @param DateTime|null $executionTime Time when the action was executed last time
     * @param DateInterval|null $interval Interval to specify how often action should be performed
     */
    public function __construct(DateTime $executionTime = null, DateInterval $interval = null)
    {
        $this->executionTime = $executionTime;
        $this->interval = $interval;
    }

    /**
     * Execute the rule.
     * @return bool
     */
    public function execute()
    {
        return $this->checkTime();
    }

    /**
     * Check if it is time to perform an action.
     * If `$this->lastExecution` is null (action has never been executed)
     * or since the last execution took time more than specified interval (`$this->interval`) then action must be performed.
     * @return bool
     */
    private function checkTime()
    {
        $result = false;

        $lastExecution = $this->getExecutionTime();
        $interval = $this->getInterval();

        if ($lastExecution === null) {
            $result = true;
        } elseif($lastExecution !== null && $interval !== null) {
            $mustBeExecutedAt = clone($lastExecution);
            $mustBeExecutedAt->add($interval);
            $now = new DateTime('now');
            $result = ($mustBeExecutedAt < $now);
        }

        return $result;
    }

    /**
     * Get last execution time. If an action was not executed before returns `null`
     * @return DateTime|null
     */
    private function getExecutionTime()
    {
        return $this->executionTime;
    }

    /**
     * @return DateInterval|null
     */
    private function getInterval()
    {
        if (is_string($this->interval)) {
            $this->interval = new DateInterval($this->interval);
        }
        return $this->interval instanceof DateInterval ? $this->interval : null;
    }

    /**
     * @return User
     */
    private function getUser()
    {
        $user = common_session_SessionManager::getSession()->getUser();
        return $user;
    }
}