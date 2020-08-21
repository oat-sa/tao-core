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
use oat\tao\model\task\migration\service\MigrationConfigFactoryInterface;
use oat\tao\model\task\migration\service\QueueMigrationService;
use oat\tao\model\task\migration\service\ResultFilterFactoryInterface;
use oat\tao\model\task\migration\service\ResultSearcherInterface;
use oat\tao\model\task\migration\service\ResultUnitProcessorInterface;
use oat\tao\model\task\migration\service\SpawnMigrationConfigServiceInterface;
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

    /**
     * @param $params
     * @throws common_exception_MissingParameter
     * @return common_report_Report
     */
    public function __invoke($params)
    {
        $report = common_report_Report::createInfo('Statement Migration Task');

        $migrationConfig = $this->getMigrationConfigFactory()->create($params);
        $resultFilter = $this->getResultFilterFactory()->create($migrationConfig);

        $respawnTaskConfig = $this->getQueueMigrationService()->migrate(
            $migrationConfig,
            $this->getUnitProcessor(),
            $this->getResultSearcher(),
            $resultFilter,
            $this->getSpawnMigrationConfigService(),
            $report
        );

        if ($respawnTaskConfig instanceof MigrationConfig) {
            $this->respawnTask($respawnTaskConfig);
        }

        return $report;
    }

    abstract protected function getUnitProcessor(): ResultUnitProcessorInterface;

    abstract protected function getResultSearcher(): ResultSearcherInterface;

    abstract protected function getSpawnMigrationConfigService(): SpawnMigrationConfigServiceInterface;

    abstract protected function getResultFilterFactory(): ResultFilterFactoryInterface;

    abstract protected function getMigrationConfigFactory(): MigrationConfigFactoryInterface;

    private function respawnTask(MigrationConfig $config): CallbackTaskInterface
    {
        return $this->getQueueDispatcher()->createTask(
            new static(),
            array_merge(
                $config->getCustomParameters(),
                [
                    'chunkSize' => $config->getChunkSize(),
                    'pickSize' => $config->getPickSize(),
                    'repeat' => $config->isProcessAll(),
                ]
            ),
            sprintf(
                'Unit processing by %s started from %s with chunk size of %s',
                self::class,
                var_export($config->getCustomParameters(), true),
                $config->getChunkSize()
            )
        );
    }

    private function getQueueMigrationService(): QueueMigrationService
    {
        return $this->getServiceLocator()->get(QueueMigrationService::class);
    }

    private function getQueueDispatcher(): QueueDispatcherInterface
    {
        return $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
    }
}
