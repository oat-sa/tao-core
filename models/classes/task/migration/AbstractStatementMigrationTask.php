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
    private $errorReport;

    abstract protected function getUnitProcessor(): StatementUnitProcessorInterface;

    public function __invoke($params)
    {
        if (
            !array_key_exists('start', $params) &&
            !array_key_exists('chunkSize', $params) &&
            !array_key_exists('pickSize', $params) &&
            !array_key_exists('repeat', $params)
        ) {
            throw new common_exception_MissingParameter();
        }

        $this->errorReport = common_report_Report::createInfo('Watching for an error');

        $start = (int)$params['start'];
        $chunkSize = (int )$params['chunkSize'];
        $this->pickSize = (int)$params['pickSize'];
        $processAllStatements = (bool)$params['repeat'];

        $max = $this->getLastRowNumber();

        $this->getPositionTracker()->keepCurrentPosition(static::class, $start);

        $end = $this->calculateEndPosition($start, $chunkSize, $max);
        $targetClasses = $this->getUnitProcessor()->getTargetClasses();

        $iterator = $this->getStatementTaskIterator()->getIterator($targetClasses, $start, $end);

        iterator_apply($iterator, [$this, 'applyProcessor'], [$iterator, $this->pickSize, $this->affected]);

        if ($$processAllStatements) {
            $nStart = $end + 1;
            if ($nStart + $chunkSize <= $max) {
                $this->respawnTask($nStart, $chunkSize, $this->pickSize);
            }
        }

        $report = common_report_Report::createSuccess(
            sprintf("Units in range from %s to %s proceeded in amount of %s", $start, $end, $this->affected)
        );

        if ($this->errorReport->containsError()) {
            $report->add($this->errorReport);
        }

        return $report;
    }

    private function applyProcessor(Iterator $iterator): bool
    {
        /** @var array $unit */
        $unit = $iterator->current();

        $id = $unit['id'];
        $subject = $unit['subject'];

        $this->logDebug(sprintf('%s processing %s as %s', static::class, $id, $subject));

        try {
            $this->processUnit($unit);
        } catch (Throwable $exception) {
            $this->errorReport->add(
                new common_report_Report(
                    common_report_Report::TYPE_WARNING, $exception->getMessage(), [$id, $subject]
                )
            );
        }

        return $this->pickSize ? $this->affected < $this->pickSize : true;
    }

    private function respawnTask(int $start, int $chunkSize, int $pickSize, bool $repeat = true): CallbackTaskInterface
    {
        /** @var QueueDispatcherInterface $queueDispatcher */
        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
        return $queueDispatcher->createTask(
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

    private function processUnit(array $unit): void
    {
        $this->getUnitProcessor()->process(new StatementUnit($unit['subject']));
        ++$this->affected;
    }

    private function calculateEndPosition(int $start, int $chunkSize, int $max): int
    {
        $end = $start + $chunkSize;

        if ($end >= $max) {
            $end = $max;
        }
        return $end;
    }

    private function getLastRowNumber(): int
    {
        return $this->getServiceLocator()->get(StatementLastIdRetriever::class)->retrieve();
    }

    private function getStatementTaskIterator(): StatementTaskIterator
    {
        return $this->getServiceLocator()->get(StatementTaskIterator::class);
    }

    private function getPositionTracker(): PositionTracker
    {
        return $this->getServiceLocator()->get(PositionTracker::class);
    }
}
