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
 * CSV Pattern Removal Script.
 *
 * This script aims at providing a tool enabling you to remove rows in a CSV files
 * by PCRE pattern.
 *
 * Parameters are:
 *
 * Parameter 1: The either absolute or relative path to the source CSV file.
 * Parameter 2: The either absolute or relative path to the destination CSV file.
 * Parameter 3: The numeric index of the CSV column on which the PCRE pattern will operate (see Parameter 4).
 * Parameter 4: A PCRE pattern.
 * Parameter 5: A numeric (0|1) value describing whether or not the source CSV file contains a first row containing
 *              column names.
 */
class RemoveCsvRowsByPattern implements Action
{
    public function __invoke($params)
    {
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

        if (isset($params[2])) {
            $columnIndex = intval($params[2]);
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'Column Index' parameter not provided."
            );
        }

        if (isset($params[3])) {
            $pattern = $params[3];
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'PCRE Pattern' parameter not provided."
            );
        }

        $withHeader = false;

        if (isset($params[4])) {
            $withHeader = boolval($params[4]);
        }

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

        // -- Deal with headers.

        if ($withHeader) {
            // Consume file header and include it in destination.
            $header = fgetcsv($sourceFp);
            fputcsv($destinationFp, $header);
        }

        $writtenCount = 0;
        $rowCount = 0;
        $matchCount = 0;

        while ($sourceData = fgetcsv($sourceFp)) {
            if (!isset($sourceData[$columnIndex])) {
                return new Report(
                    Report::TYPE_ERROR,
                    "No data found at row {$rowCount}, index {$columnIndex}"
                );
            }

            if (($match = preg_match($pattern, $sourceData[$columnIndex])) !== false) {
                if ($match === 0) {
                    // No match, the record is not excluded from destination file.
                    fputcsv($destinationFp, $sourceData);
                    $writtenCount++;
                } else {
                    $matchCount++;
                }
            } else {
                return new Report(
                    Report::TYPE_ERROR,
                    "A PCRE engine error occured while processing pattern '{$pattern}'."
                );
            }

            $rowCount++;
        }

        @fclose($sourceFp);
        @fclose($destinationFp);

        return new Report(
            Report::TYPE_SUCCESS,
            $writtenCount . " lines written in file '" . realpath($destination)
                . "'. {$matchCount} lines were ignored because they were matching the provided pattern."
        );
    }
}
