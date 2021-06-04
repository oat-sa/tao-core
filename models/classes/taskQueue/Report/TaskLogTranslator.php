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
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\taskQueue\TaskLog\Entity\EntityInterface;

class TaskLogTranslator extends ConfigurableService
{
    public function translate(EntityInterface $taskLogEntity): EntityInterface
    {
        if (!$taskLogEntity->getReport()) {
            return $taskLogEntity;
        }

        $report = $taskLogEntity->getReport();

        $this->translateReport($report);

        return $taskLogEntity;
    }

    private function translateReport(common_report_Report $report): void
    {
        $message = $report->getMessage();

        if (strpos($message, '__(|||') === 0) {
            $messageData = str_replace('__(|||', '', $message);
            $messageData = explode('|||', $messageData);

            $translatedMessage = call_user_func_array('__', $messageData);

            $report->setMessage($translatedMessage);
        }

        /** @var common_report_Report $subReport */
        foreach ($report->getChildren() as $subReport) {
            $this->translateReport($subReport);
        }
    }
}
