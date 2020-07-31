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
use oat\tao\model\task\migration\MigrationConfig;
use Throwable;

class QueueMigrationService extends ConfigurableService
{
    /** @var int */
    private $affected = 0;

    public function migrate(
        MigrationConfig $config,
        ResultUnitProcessorInterface $resultUnitProcessor,
        ResultSearcherInterface $resultSearcher,
        common_report_Report $report
    ): ?MigrationConfig
    {
        $max = $this->getStatementLastIdRetriever()->retrieve();
        $end = $this->calculateEndPosition(
            $config->getStart(),
            $config->getChunkSize(),
            $max
        );

        $results = $resultSearcher->search($config->getStart(), $end);

        foreach ($results as $unit) {
            try {
                $resultUnitProcessor->process($unit);
                $this->affected++;
            } catch (Throwable $exception) {
                $report->add(common_report_Report::createFailure($exception->getMessage()));
                return null;
            }
        }
        $report->add(common_report_Report::createSuccess(
            sprintf('Units in range from %s to %s proceeded in amount of %s',
                $config->getStart(),
                $end,
                $this->affected
            )));

        if ($config->isProcessAll()) {
            $nStart = $end + 1;
            if ($nStart + $config->getChunkSize() <= $max) {
                return new MigrationConfig(
                    $nStart,
                    $config->getChunkSize(),
                    $config->getPickSize(),
                    $config->isProcessAll()
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

    private function getStatementLastIdRetriever(): StatementLastIdRetriever
    {
        return $this->getServiceLocator()->get(StatementLastIdRetriever::class);
    }

    public function getResultProcessor(): ResultUnitProcessorInterface
    {
        return $this->getServiceLocator()->get(ResultUnitProcessorInterface::class);
    }
}
