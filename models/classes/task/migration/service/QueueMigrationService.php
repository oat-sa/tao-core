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
    public function migrate(
        MigrationConfig $config,
        ResultUnitProcessorInterface $resultUnitProcessor,
        ResultSearcherInterface $resultSearcher,
        ResultFilter $filter,
        SpawnMigrationConfigServiceInterface $configFactory,
        common_report_Report $report
    ): ?MigrationConfig
    {
        $results = $resultSearcher->search($filter);

        foreach ($results as $unit) {
            try {
                $resultUnitProcessor->process($unit);
            } catch (Throwable $exception) {
                $report->add(common_report_Report::createFailure($exception->getMessage()));
                return null;
            }
        }

        if ($config->isProcessAll()) {
            $config = $configFactory->spawn($config, $filter);

            if ($config) {
                $report->add(
                    common_report_Report::createInfo('Respawning additional task')
                );

                return $config;
            }
        }

        $report->add(common_report_Report::createSuccess('To repeat this process to other statements please provide -rp flag'));

        return null;
    }
}
