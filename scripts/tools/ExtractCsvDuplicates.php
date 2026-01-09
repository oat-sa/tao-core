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

/**
 * Script to extract duplicate rows from an indexed CSV file.
 *
 * The script enables you to extract duplicate entries in a CSV file.
 */
class ExtractCsvDuplicates extends AbstractIndexedCsv
{
    /**
     * Duplicate extraction logic.
     *
     * Extract duplicate rows from the source CSV file to the
     * destination CSV file.
     *
     * @see \oat\tao\scripts\tools\AbstractIndexedCsv
     */
    protected function process()
    {
        $sourceFp = $this->getSourceFp();
        $destinationFp = $this->getDestinationFp();
        $index = $this->getIndex();

        // Extract duplicates in a separate file.
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

        return new Report(
            Report::TYPE_INFO,
            "{$duplicateCount} duplicate records extracted in file '" . realpath($this->getDestination()) . "'."
        );
    }
}
