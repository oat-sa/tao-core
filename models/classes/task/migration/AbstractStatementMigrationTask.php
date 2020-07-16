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
use Iterator;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\oatbox\log\LoggerAwareTrait;
use oat\tao\model\task\migration\service\PositionTracker;
use oat\tao\model\task\migration\service\StatementLastIdRetriever;
use oat\tao\model\task\migration\service\StatementTaskIterator;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\tao\model\taskQueue\Task\TaskAwareInterface;
use oat\tao\model\taskQueue\Task\TaskAwareTrait;
use Throwable;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

abstract class AbstractStatementMigrationTask implements Action, ServiceLocatorAwareInterface, TaskAwareInterface
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
    private $anomalies;

    abstract protected function getTargetClasses(): array;

    abstract protected function processUnit(array $unit): void;

    public function __invoke($params)
    {
        if (count($params) < 4) {
            throw new common_exception_MissingParameter();
        }
        $start = array_shift($params);
        $chunkSize = array_shift($params);
        $this->pickSize = $pickSize = array_shift($params);
        $slideToTheStatementsEnd = (bool)array_shift($params);

        $max = $this->getLastRowNumber();

        $this->getServiceLocator()->get(PositionTracker::class)->keepCurrentPosition(static::class, $start);

        $end = $start + $chunkSize;

        if ($end >= $max) {
            $end = $max;
        }

        $targetClasses = $this->getTargetClasses();
        $this->initAnomaliesCollector();
        $iterator = $this->getServiceLocator()->get(StatementTaskIterator::class)->getIterator($targetClasses, $start, $end);

        iterator_apply($iterator, [$this, 'applyProcessor'], [$iterator, $pickSize, $this->affected]);

        if ($slideToTheStatementsEnd) {
            $nStart = $end + 1;
            if ($nStart + $chunkSize <= $max) {
                $this->respawnTask($nStart, $chunkSize, $pickSize);
            }
        }

        $report = common_report_Report::createSuccess(
            sprintf("Units in range from %s to %s proceeded in amount of %s", $start, $end, $this->affected)
        );

        if ($this->anomalies->hasChildren()) {
            $report->add($this->anomalies);
        }

        return $report;
    }

    private function applyProcessor(Iterator $iterator): bool
    {
        /** @var array $unit */
        $unit = $iterator->current();

        $id = $unit['id'];
        $subject = $unit['subject'];

        try {
            $this->logDebug(sprintf('%s processing %s as %s', static::class, $id, $subject));

            $this->processUnit($unit);

            ++$this->affected;
        } catch (Throwable $exception) {
            $this->addAnomaly($id, $subject, $exception->getMessage());
        }

        return $this->pickSize ? $this->affected < $this->pickSize : true;
    }

    private function respawnTask(int $start, int $chunkSize, int $pickSize, bool $repeat = true): CallbackTaskInterface
    {
        /** @var QueueDispatcherInterface $queueDispatcher */
        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
        return $queueDispatcher->createTask(
            new static(),
            [$start, $chunkSize, $pickSize, $repeat],
            sprintf(
                'Unit processing by %s started from %s with chunk size of %s',
                self::class,
                $start,
                $chunkSize
            )
        );
    }

    private function getLastRowNumber(): int
    {
        return $this->getServiceLocator()->get(StatementLastIdRetriever::class)->retrieve();
    }

    private function addAnomaly(string $id, string $uri, string $reason): void
    {
        $this->anomalies->add(new common_report_Report(common_report_Report::TYPE_WARNING, $reason, [$id, $uri]));
    }

    private function initAnomaliesCollector(): void
    {
        $this->anomalies = common_report_Report::createInfo('Anomalies found');
    }
}
