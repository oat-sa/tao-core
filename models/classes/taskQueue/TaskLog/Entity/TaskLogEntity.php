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

namespace oat\tao\model\taskQueue\TaskLog\Entity;

use common_report_Report as Report;
use DateTime;
use Exception;
use oat\tao\model\taskQueue\TaskLog\Broker\TaskLogBrokerInterface;
use oat\tao\model\taskQueue\TaskLog\CategorizedStatus;

class TaskLogEntity implements EntityInterface
{
    /** @var string */
    private $id;

    /** @var string */
    private $parentId;

    /** @var string */
    private $taskName;

    /** @var array */
    private $parameters;

    /** @var  string */
    private $label;

    /** @var CategorizedStatus */
    private $status;

    /** @var bool  */
    private $masterStatus;

    /** @var string */
    private $owner;

    /** @var  Report */
    private $report;

    /** @var  DateTime */
    private $createdAt;

    /** @var  DateTime */
    private $updatedAt;

    /**
     * TaskLogEntity constructor.
     *
     * @param string                   $id
     * @param string                   $parentId
     * @param string                   $taskName
     * @param CategorizedStatus        $status
     * @param boolean                  $masterStatus
     * @param array                    $parameters
     * @param string                   $label
     * @param string                   $owner
     * @param DateTime|null            $createdAt
     * @param DateTime|null            $updatedAt
     * @param Report|null              $report
     */
    public function __construct(
        $id,
        $parentId,
        $taskName,
        CategorizedStatus $status,
        array $parameters,
        $label,
        $owner,
        DateTime $createdAt = null,
        DateTime $updatedAt = null,
        Report $report = null,
        $masterStatus = false
    ) {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->taskName = $taskName;
        $this->status = $status;
        $this->parameters = $parameters;
        $this->label = $label;
        $this->owner = $owner;
        $this->report = $report;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->masterStatus = $masterStatus;
    }

    /**
     * @param array $row
     * @return TaskLogEntity
     * @throws Exception
     */
    public static function createFromArray(array $row)
    {
        return new self(
            $row[TaskLogBrokerInterface::COLUMN_ID],
            $row[TaskLogBrokerInterface::COLUMN_PARENT_ID],
            $row[TaskLogBrokerInterface::COLUMN_TASK_NAME],
            CategorizedStatus::createFromString($row[TaskLogBrokerInterface::COLUMN_STATUS]),
            isset($row[TaskLogBrokerInterface::COLUMN_PARAMETERS]) ? json_decode($row[TaskLogBrokerInterface::COLUMN_PARAMETERS], true) : [],
            isset($row[TaskLogBrokerInterface::COLUMN_LABEL]) ? $row[TaskLogBrokerInterface::COLUMN_LABEL] : '',
            isset($row[TaskLogBrokerInterface::COLUMN_OWNER]) ? $row[TaskLogBrokerInterface::COLUMN_OWNER] : '',
            isset($row[TaskLogBrokerInterface::COLUMN_CREATED_AT]) ? DateTime::createFromFormat('Y-m-d H:i:s', $row[TaskLogBrokerInterface::COLUMN_CREATED_AT], new \DateTimeZone('UTC')) : null,
            isset($row[TaskLogBrokerInterface::COLUMN_UPDATED_AT]) ? DateTime::createFromFormat('Y-m-d H:i:s', $row[TaskLogBrokerInterface::COLUMN_UPDATED_AT], new \DateTimeZone('UTC')) : null,
            Report::jsonUnserialize($row[TaskLogBrokerInterface::COLUMN_REPORT]),
            isset($row[TaskLogBrokerInterface::COLUMN_MASTER_STATUS]) ? $row[TaskLogBrokerInterface::COLUMN_MASTER_STATUS] : false
        );
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @return string
     */
    public function getTaskName()
    {
        return $this->taskName;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return Report|null
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }


    /**
     * @return CategorizedStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return boolean
     */
    public function isMasterStatus()
    {
        return (boolean) $this->masterStatus;
    }

    /**
     * Returns the file name from the generated report.
     *
     * NOTE: it is not 100% sure that the returned string is really a file name because different reports set different values as data.
     * So this return value can be any kind of string. Please check the file whether it exist or not before usage.
     *
     * @return string
     */
    public function getFileNameFromReport()
    {
        $filename = '';

        if ($this->getStatus()->isFailed() || is_null($this->getReport())) {
            return $filename;
        }

        /** @var Report  $successReport */
        foreach ($this->getReport()->getSuccesses() as $successReport) {
            $data = $successReport->getData();
            if (is_string($data)) {
                $filename = $data;
                break;
            }

            if (is_array($data) && isset($data['uriResource'])) {
                $filename = $data['uriResource'];
            }
        }

        return $filename;
    }


    public function getResourceUriFromReport()
    {
        $uri = '';

        if ($this->getStatus()->isFailed() || is_null($this->getReport())) {
            return $uri;
        }

        /** @var Report  $successReport */
        foreach ($this->getReport()->getSuccesses(true) as $successReport) {
            $data = $successReport->getData();
            if (is_array($data) && isset($data['uriResource'])) {
                $uri = $data['uriResource'];
                break;
            }
        }

        return $uri;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        // add basic fields which always have values
        $rs = [
            'id' => $this->id,
            'taskName' => $this->taskName,
            'status' => (string) $this->status,
            'masterStatus' => (boolean) $this->masterStatus,
            'statusLabel' => $this->status->getLabel()
        ];

        // add other fields only if they have values
        if ($this->label) {
            $rs['taskLabel'] = $this->label;
        }

        if ($this->createdAt instanceof \DateTime) {
            $rs['createdAt'] = $this->createdAt->getTimestamp();
            $rs['createdAtElapsed'] = (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp() - $this->createdAt->getTimestamp();
        }

        if ($this->updatedAt instanceof \DateTime) {
            $rs['updatedAt'] = $this->updatedAt->getTimestamp();
            $rs['updatedAtElapsed'] = (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp() - $this->updatedAt->getTimestamp();
        }

        if ($this->report instanceof Report) {
            $rs['report'] = $this->report->toArray();
        }

        return $rs;
    }
}