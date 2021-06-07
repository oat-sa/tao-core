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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

declare(strict_types=1);

namespace oat\tao\model\taskQueue\Report;

use common_report_Report;
use oat\oatbox\reporting\Report;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;

class TaskLogTranslator extends ConfigurableService implements TaskLogTranslatorInterface
{
    public function translate(EntityInterface $taskLogEntity): void
    {
        if (!$taskLogEntity->getReport()) {
            return;
        }

        $this->translateReport($taskLogEntity->getReport());
    }

    private function translateReport(common_report_Report $report): void
    {
        if (!$report instanceof Report) {
            return;
        }

        $report->setMessage($report->translateMessage());

        /** @var common_report_Report $subReport */
        foreach ($report->getChildren() as $subReport) {
            $this->translateReport($subReport);
        }
    }
}
