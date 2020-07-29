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

namespace oat\tao\model\task\migration\service;


use common_report_Report;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\task\migration\StatementMigrationConfig;
use oat\tao\model\task\migration\StatementUnit;
use Throwable;

class QueueMigrationService extends ConfigurableService
{
    /** @var int */
    private $affected;

    public function migrate(StatementMigrationConfig $config, ResultUnitProcessorInterface $resultUnitProcessor, common_report_Report $report): ?StatementMigrationConfig
    {
        $max = $this->getLastRowNumber();
        $end = $this->calculateEndPosition(
            $config->getStart(),
            $config->getChunkSize(),
            $max
        );
        $config->setEnd($end);

        $results = $this->getResultSearcher()->search($config);

        foreach ($results as $unit) {
            try {
                $unit = new StatementUnit($unit['subject']);
                $resultUnitProcessor->process($unit);
                $this->affected++;
            } catch (Throwable $exception) {
                $report->add(common_report_Report::createFailure($exception->getMessage()));
                return null;
            }
        }
        $report->add(common_report_Report::createSuccess(
            sprintf('Units in range from %s to %s proceeded in amount of %s', $config->getStart(), $end, $this->affected)));

        if ($config->isProcessAllStatements()) {
            $nStart = $end + 1;
            if ($nStart + $config->getChunkSize() <= $max) {
                return new StatementMigrationConfig(
                    $nStart,
                    $config->getChunkSize(),
                    $config->getPickSize(),
                    $config->isProcessAllStatements(),
                    $resultUnitProcessor->getTargetClasses()
                );
            }
        }

        return null;
    }

    private function calculateEndPosition(int $start, int $chunkSize, int $max): int
    {
        $end = $start + $chunkSize;

        if ($end >= $max) {
            $end = $max;
        }
        return $end;
    }

    public function getResultSearcher(): ResultSearcherInterface
    {
        return $this->getServiceLocator()->get(ResultSearcherService::class);
    }

    public function getResultProcessor(): ResultUnitProcessorInterface
    {
        return $this->getServiceLocator()->get(ResultUnitProcessorInterface::class);
    }

    private function getLastRowNumber(): int
    {
        return $this->getServiceLocator()->get(StatementLastIdRetriever::class)->retrieve();
    }
}