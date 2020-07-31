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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\task\migration;

use common_exception_MissingParameter;
use common_report_Report;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\task\migration\service\QueueMigrationService;
use oat\tao\model\task\migration\service\ResultSearcherInterface;
use oat\tao\model\task\migration\service\ResultUnitProcessorInterface;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

abstract class AbstractMigrationTask implements Action, ServiceLocatorAwareInterface, TaskAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;
    use TaskAwareTrait;
    use LoggerAwareTrait;

    /** @var int */
    private $affected;

    /** @var int */
    private $pickSize;

    /** @var common_report_Report */
    private $errorReport;

    abstract protected function getUnitProcessor(): ResultUnitProcessorInterface;

    abstract protected function getResultSearcher(): ResultSearcherInterface;

    /**
     * @param $params
     *
     * @return \common_report_Report
     * @throws \common_exception_MissingParameter
     */
    public function __invoke($params)
    {
        $report = common_report_Report::createInfo('Statement Migration Task');

        if (
            !array_key_exists('start', $params) ||
            !array_key_exists('chunkSize', $params) ||
            !array_key_exists('pickSize', $params) ||
            !array_key_exists('repeat', $params)
        ) {
            throw new common_exception_MissingParameter();
        }

        $migrationConfig = new MigrationConfig(
            (int)$params['chunkSize'],
            (int)$params['start'],
            (int)$params['pickSize'],
            (bool)$params['repeat']
        );

        $respawnTaskConfig = $this->getQueueMigrationService()->migrate($migrationConfig, $this->getUnitProcessor(), $this->getResultSearcher(), $report);

        if ($respawnTaskConfig instanceof MigrationConfig) {
            $this->respawnTask(
                $respawnTaskConfig->getChunkSize(),
                $respawnTaskConfig->getStart(),
                $respawnTaskConfig->getPickSize(),
                $respawnTaskConfig->isProcessAll()
            );
        }

        return $report;
    }

    private function respawnTask(int $start, int $chunkSize, int $pickSize, bool $repeat = true): CallbackTaskInterface
    {
        return $this->getQueueDispatcher()->createTask(
            new static(),
            ['start' => $start, 'chunkSize' => $chunkSize, 'pickSize' => $pickSize, 'repeat' => $repeat],
            sprintf(
                'Unit processing by %s started from %s with chunk size of %s',
                self::class,
                $start,
                $chunkSize
            )
        );
    }

    private function getQueueMigrationService(): QueueMigrationService
    {
        return $this->getServiceLocator()->get(QueueMigrationService::class);
    }

    private function getQueueDispatcher():QueueDispatcherInterface
    {
        return $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
    }
}
