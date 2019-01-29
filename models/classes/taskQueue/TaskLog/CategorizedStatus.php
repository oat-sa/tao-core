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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\taskQueue\TaskLog;

use Exception;
use oat\tao\model\taskQueue\TaskLogInterface;

class CategorizedStatus
{
    const STATUS_CREATED = 'created';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_CANCELLED = 'cancelled';

    /** @var  string */
    private $status;

    public static $categorizeMapping = array(
        self::STATUS_CREATED => [
            TaskLogInterface::STATUS_ENQUEUED
        ],
        self::STATUS_IN_PROGRESS => [
            TaskLogInterface::STATUS_DEQUEUED,
            TaskLogInterface::STATUS_RUNNING,
            TaskLogInterface::STATUS_CHILD_RUNNING
        ],
        self::STATUS_COMPLETED   => [
            TaskLogInterface::STATUS_COMPLETED
        ],
        self::STATUS_FAILED      => [
            TaskLogInterface::STATUS_FAILED,
            TaskLogInterface::STATUS_UNKNOWN
        ],
        self::STATUS_ARCHIVED    => [
            TaskLogInterface::STATUS_ARCHIVED,
        ],
        self::STATUS_CANCELLED    => [
            TaskLogInterface::STATUS_CANCELLED,
        ],
    );

    /**
     * @param $status
     */
    protected function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * @param string $status
     * @return CategorizedStatus
     *
     * @throws Exception
     */
    public static function createFromString($status)
    {
        switch ($status) {
            case TaskLogInterface::STATUS_ENQUEUED:
                return self::created();
                break;

            case TaskLogInterface::STATUS_DEQUEUED:
            case TaskLogInterface::STATUS_RUNNING:
            case TaskLogInterface::STATUS_CHILD_RUNNING:
                return self::inProgress();
                break;

            case TaskLogInterface::STATUS_COMPLETED:
                return self::completed();
                break;

            case TaskLogInterface::STATUS_ARCHIVED:
                return self::archived();
                break;

            case TaskLogInterface::STATUS_CANCELLED:
                return self::cancelled();
                break;

            case TaskLogInterface::STATUS_FAILED:
            case TaskLogInterface::STATUS_UNKNOWN:
                return self::failed();
                break;

            default:
                throw new \Exception('Invalid status provided');
        }
    }

    /**
     * @return CategorizedStatus
     */
    public static function completed()
    {
        return new self(self::STATUS_COMPLETED);
    }

    /**
     * @return CategorizedStatus
     */
    public static function archived()
    {
        return new self(self::STATUS_ARCHIVED);
    }

    /**
     * @return CategorizedStatus
     */
    public static function cancelled()
    {
        return new self(self::STATUS_CANCELLED);
    }

    /**
     * @return CategorizedStatus
     */
    public static function failed()
    {
        return new self(self::STATUS_FAILED);
    }

    /**
     * @return CategorizedStatus
     */
    public static function created()
    {
        return new self(self::STATUS_CREATED);
    }

    /**
     * @return CategorizedStatus
     */
    public static function inProgress()
    {
        return new self(self::STATUS_IN_PROGRESS);
    }

    /**
     * @return bool
     */
    public function isCreated()
    {
        return $this->equals(self::created());
    }

    /**
     * @return bool
     */
    public function isInProgress()
    {
       return $this->equals(self::inProgress());
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->equals(self::completed());
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->equals(self::failed());
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->equals(self::archived());
    }

    /**
     * @return bool
     */
    public function isCancelled()
    {
        return $this->equals(self::cancelled());
    }

    /**
     * @param CategorizedStatus $logStatus
     *
     * @return bool
     */
    public function equals(CategorizedStatus $logStatus)
    {
       return $this->status === $logStatus->status;
    }

    /**
     * @param string $status
     * @return array
     */
    public static function getMappedStatuses($status)
    {
        return self::$categorizeMapping[$status];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->status;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        $label = '';

        switch ($this->status) {
            case self::STATUS_CREATED:
                $label = __('Queued');
                break;

            case self::STATUS_IN_PROGRESS:
                $label = __('In Progress');
                break;

            case self::STATUS_COMPLETED:
                $label = __('Completed');
                break;

            case self::STATUS_FAILED:
                $label = __('Failed');
                break;

            case self::STATUS_ARCHIVED:
                $label = __('Archived');
                break;

            case self::STATUS_CANCELLED:
                $label = __('Cancelled');
                break;
        }

        return $label;
    }
}
