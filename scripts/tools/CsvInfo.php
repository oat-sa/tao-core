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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\scripts\tools;

use common_report_Report as Report;
use oat\oatbox\action\Action;

/**
 * CSV Info Script.
 *
 * This script aims at providing information about a CSV file:
 *
 * - Number of rows
 * - Number of columns
 * - Wheter or not rows all have the same number of columns.
 *
 * Please note that this script expects a default CSV delimiters format.
 * You can reconfigure these delimiters using the oat\tao\scripts\tools\CsvReconfigure
 *
 * Parameter 1: The either absolute or relative path to the source CSV file.
 *
 */
class CsvInfo implements Action
{
    public function __invoke($params)
    {
        // Main report.
        $report = new Report(
            Report::TYPE_INFO,
            'Unknown'
        );

        // -- Deal with parameters.
        if (!empty($params[0])) {
            $source = $params[0];
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'Source' parameter not provided."
            );
        }

        $sourceFp = @fopen($source, 'r');

        if ($sourceFp === false) {
            return new Report(
                Report::TYPE_ERROR,
                "Source file '{$source}' could not be open."
            );
        }

        $rowCount = 0;
        $columnCount = 0;
        $columnCountMismatch = false;

        if ($sourceData = fgetcsv($sourceFp)) {
            // First row processing.
            $rowCount++;
            $columnCount = count($sourceData);

            while ($sourceData = fgetcsv($sourceFp)) {
                // Next rows processing.
                $newColumnCount = count($sourceData);

                if ($newColumnCount !== $columnCount) {
                    // Column count mismatch.
                    $columnCountMismatch = true;
                }

                $rowCount++;
            }

            $report->add(
                new Report(
                    Report::TYPE_INFO,
                    "The source file contains {$rowCount} rows."
                )
            );

            $report->add(
                new Report(
                    Report::TYPE_INFO,
                    "The source file contains {$columnCount} columns (inferred from first row)."
                )
            );

            if (!$columnCountMismatch) {
                $report->add(
                    new Report(
                        Report::TYPE_INFO,
                        "The source file has the same amount of columns for each row."
                    )
                );
            } else {
                $report->add(
                    new Report(
                        Report::TYPE_WARNING,
                        "The source file has not the same amout of columns for each row."
                    )
                );
            }
        } else {
            $report->add(
                new Report(
                    Report::TYPE_WARNING,
                    "Source file is empty."
                )
            );
        }

        @fclose($sourceFp);

        $report->setType(Report::TYPE_INFO);
        $report->setMessage('The script ended gracefully.');

        return $report;
    }
}
