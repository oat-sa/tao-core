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

use oat\oatbox\action\Action;

class ExtractCsvDuplicates implements Action
{
    public function __invoke($params)
    {
        $source = $params[0];
        $destination = $params[1];
        $indexColumn = intval($params[2]);
        $firstRowColumnNames = boolval($params[3]);
        
        // Index the file by ID.
        $index = [];
        
        $sourceFp = @fopen($source, 'r');
        $destinationFp = @fopen($destination, 'w');
        
        if ($sourceFp === false) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Source file '" . $source . "' could not be open."
            );
        }
        
        if ($destinationFp === false) {
            return new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                "Destination file '" . $source . "' could not be open."
            );
        }
        
        // Pass trough first row if neeed.
        if ($firstRowColumnNames) {
            $headers = fgetcsv($sourceFp);
        }
        
        while (!feof($sourceFp)) {
            $position = ftell($sourceFp);
            $sourceData = fgetcsv($sourceFp);
            if($sourceData !== false && !isset($sourceData[$indexColumn])){
                return new \common_report_Report(
                    \common_report_Report::TYPE_ERROR,
                    $indexColumn . " is not a valid offset for the source. It should be one of : ".implode(', ',array_keys($sourceData))
                );
            }
            $index[$sourceData[$indexColumn]][] = $position;
        }
        
        ksort($index);
        
        
        // Extract duplicates in a separate file.
        if ($firstRowColumnNames) {
            fputcsv($destinationFp, $headers);
        }
        
        $duplicateCount = 0;
        foreach ($index as $identifier => $positions) {
            if (count($positions) > 1) {
                // We have a duplicate.
                foreach ($positions as $pos) {
                    rewind($sourceFp);
                    fseek($sourceFp, $pos);
                    $sourceData = fgetcsv($sourceFp);
                    fputcsv($destinationFp, $sourceData);
                    $duplicateCount++;
                }
            }
        }
        
        return new \common_report_Report(
            \common_report_Report::TYPE_INFO,
            "${duplicateCount} duplicate records extracted in file '" . realpath($destination) . "'."
        );
    }
}
