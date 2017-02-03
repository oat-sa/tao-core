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

use \common_report_Report as Report;
use oat\oatbox\action\Action;

/**
 * CSV Merge Script.
 * 
 * This script aims at providing a tool to merge two CSV files together.
 * 
 * * Parameter 1: The path to the first source CSV file.
 * * Parameter 2: The path to the second source CSV file.
 * * Parameter 3: The path to the destination CSV file.
 * * Parameter 4: (integer 0|1) Wheter or not the second source CSV file has its first row describing column names.
 */
class MergeCsv implements Action
{
    public function __invoke($params)
    {
        // -- Deal with parameters.
        // Files
        if (!empty($params[0])) {
            $source1 = $params[0];
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'Source 1' parameter not provided."
            );
        }
        
        if (!empty($params[1])) {
            $source2 = $params[1];
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'Source 2' parameter not provided."
            );
        }
        
        if (!empty($params[2])) {
           $destination = $params[2];
        } else {
            return new Report(
                Report::TYPE_ERROR,
                "'Destination' parameter not provided."
            );
        }
        
        // Flags
        $withHeader2 = false;
        
        if (isset($params[3])) {
            $withHeader2 = boolval($params[3]);
        }
        
        // -- Deal with file handling.
        $source1Fp = @fopen($source1, 'r');
        $source2Fp = @fopen($source2, 'r');
        $destinationFp = @fopen($destination, 'w');
        
        if ($source1Fp === false) {
            return new Report(
                Report::TYPE_ERROR,
                "Source file 1 '" . $source1 . "' could not be open."
            );
        }
        
        if ($source2Fp === false) {
            return new Report(
                Report::TYPE_ERROR,
                "Source file 2 '" . $source2 . "' could not be open."
            );
        }
        
        if ($destinationFp === false) {
            return new Report(
                Report::TYPE_ERROR,
                "Destination file '" . $destination . "' could not be open."
            );
        }
        
        // -- Deal with headers.
        $firstColumn1 = fgetcsv($source1Fp);
        $firstColumn2 = fgetcsv($source2Fp);
        
        rewind($source1Fp);
        rewind($source2Fp);
        
        if (count($firstColumn1) !== count($firstColumn2)) {
            return new Report(
                Report::TYPE_ERROR,
                "Source files have a different column count."
            );
        }
        
        if ($withHeader2) {
            // Consume second file header.
            fgetcsv($source2Fp);
        }
        
        $sourceFps = [$source1Fp, $source2Fp];
        $writtenCounts = [0, 0];
        
        for ($i = 0; $i < count($sourceFps); $i++) {
            while ($sourceData = fgetcsv($sourceFps[$i])) {
                fputcsv($destinationFp, $sourceData);
                $writtenCounts[$i]++;
            }
        }
        
        return new Report(
            Report::TYPE_SUCCESS,
            array_sum($writtenCounts) . ' (' . $writtenCounts[0] . ' + ' . $writtenCounts[1] . ") lines written in file '" . realpath($destination) . "'."
        );
    }
}
