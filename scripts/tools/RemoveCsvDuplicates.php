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
 * Script to remove duplicate rows from an indexed CSV file.
 *
 * The script enables you to remove duplicate entries in a CSV file. In
 * other words, only non duplicated rows will be written in a destination
 * CSV file.
 */
class RemoveCsvDuplicates extends AbstractIndexedCsv
{
    /**
     * Duplicate removal logic.
     *
     * Remove duplicate rows from the source CSV file and write
     * them in the destination CSV file. In other words, only
     * non duplicated rows will be written in the destination CSV
     * file.
     *
     * @see \oat\tao\scripts\tools\AbstractIndexedCsv
     */
    protected function process()
    {
        $sourceFp = $this->getSourceFp();
        $destinationFp = $this->getDestinationFp();
        $index = $this->getIndex();

        $writtenCount = 0;
        foreach ($index as $identifier => $positions) {
            if (count($positions) === 1) {
                // No duplicate.
                rewind($sourceFp);
                fseek($sourceFp, $positions[0]);
                $sourceData = fgetcsv($sourceFp);
                fputcsv($destinationFp, $sourceData);
                $writtenCount++;
            }
        }

        return new Report(
            Report::TYPE_INFO,
            "{$writtenCount} records written in file '" . realpath($this->getDestination()) . "'."
        );
    }
}
