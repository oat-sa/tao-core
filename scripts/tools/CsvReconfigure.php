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
 * CSV Reconfiguration Script.
 *
 * This script aims at providing a tool enabling you to reconfigure a CSV file in terms of:
 *
 * * Delimiter character
 * * Enclosure character
 * * Escape character
 *
 * Parameters are:
 *
 * Parameter 1: The either absolute or relative path to the source CSV file.
 * Parameter 2: The either absolute or relative path to the destination CSV file.
 * Parameter 3: (optional) The output CSV delimiter (default is ",").
 * Parameter 4: (optional) The output CSV enclosure (default is '"').
 * Parameter 5: (optional) The output CSV escape character (default is "\").
 * Parameter 6: (optional) The input CSV delimiter (default is ",").
 * Parameter 7: (optional) The input CSV enclosure (default is '"').
 * Parameter 8: (optional) The input CSV escape character (default is "\").
 *
 * Bash example usage:
 *
 * Reconfigure output CSV with delimiter character = ";", enclosure character = '"', escape character = "\".
 *
 * sudo -u www-data php index.php "oat\tao\scripts\tools\CsvReconfigure" /some/path/original.csv
 *      /some/path/reconfigured.csv \; \" \\
 */
class CsvReconfigure implements Action
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

        if (!empty($params[1])) {
            $destination = $params[1];
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'Destination' parameter not provided."
            );
        }

        // Defaults.
        $outputDelimiter = isset($params[2]) ? $params[2] : ',';
        $outputEnclosure = isset($params[3]) ? $params[3] : '"';
        $outputEscapeChar = isset($params[4]) ? $params[4] : "\\";
        $inputDelimiter = isset($params[5]) ? $params[5] : ',';
        $inputEnclosure = isset($params[6]) ? $params[6] : '"';
        $inputEscapeChar = isset($params[7]) ? $params[7] : "\\";

        // -- Deal with file handling.
        $sourceFp = @fopen($source, 'r');
        $destinationFp = @fopen($destination, 'w');

        if ($sourceFp === false) {
            return new Report(
                Report::TYPE_ERROR,
                "Source file '" . $source . "' could not be open."
            );
        }

        if ($destinationFp === false) {
            return new Report(
                Report::TYPE_ERROR,
                "Destination file '" . $destination . "' could not be open."
            );
        }

        $report->add(
            new Report(
                Report::TYPE_INFO,
                "output delimiter: '{$outputDelimiter}', output enclosure: '{$outputEnclosure}', output escape "
                    . "character: '{$outputEscapeChar}'"
            )
        );

        $report->add(
            new Report(
                Report::TYPE_INFO,
                "input delimiter: '{$inputDelimiter}', input enclosure: '{$inputEnclosure}', input escape "
                    . "character: '{$inputEscapeChar}'"
            )
        );

        $rowCount = 0;
        while ($sourceData = fgetcsv($sourceFp, 0, $inputDelimiter, $inputEnclosure, $inputEscapeChar)) {
            fputcsv($destinationFp, $sourceData, $outputDelimiter, $outputEnclosure, $outputEscapeChar);

            $rowCount++;
        }

        @fclose($sourceFp);
        @fclose($destinationFp);

        $report->setType(Report::TYPE_SUCCESS);
        $report->setMessage($rowCount . " lines written in file '" . realpath($destination) . "'.");

        return $report;
    }
}
