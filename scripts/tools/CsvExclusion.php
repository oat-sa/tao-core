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
 *
 */
class CsvExclusion extends AbstractIndexedCsv
{
    private $source2;
    private $source2Fp;

    protected function setSource2($source2)
    {
        $this->source2 = $source2;
    }

    protected function getSource2()
    {
        return $this->source2;
    }

    protected function setSource2Fp($source2Fp)
    {
        $this->source2Fp = $source2Fp;
    }

    protected function getSource2Fp()
    {
        return $this->source2Fp;
    }

    protected function beforeProcess()
    {
        $report = parent::beforeProcess();
        $params = $this->getParams();

        if (!empty($params[4])) {
            $this->setSource2($params[4]);
        } else {
            $report->add(
                new Report(
                    Report::TYPE_ERROR,
                    "'Second Source' parameter not provided."
                )
            );

            return $report;
        }

        $source2Fp = @fopen($this->getSource2(), 'r');

        if ($source2Fp === false) {
            $report->add(
                new Report(
                    Report::TYPE_ERROR,
                    "Second source file '" . $this->getSource2() . "' could not be open."
                )
            );

            return $report;
        }

        $this->setSource2Fp($source2Fp);

        return $report;
    }

    /**
     *
     *
     * @see \oat\tao\scripts\tools\AbstractIndexedCsv
     */
    protected function process()
    {
        $sourceFp = $this->getSourceFp();
        $source2Fp = $this->getSource2Fp();
        $destinationFp = $this->getDestinationFp();
        $index1 = $this->getIndex();
        $index2 = [];
        $this->fillIndex($index2, $source2Fp);

        rewind($sourceFp);
        rewind($source2Fp);

        $removedCount = 0;
        $writtenCount = 0;

        foreach ($index1 as $identifier => $positions) {
            foreach ($positions as $pos) {
                if (!isset($index2[$identifier])) {
                    // Row not referenced in second CSV file. It may be copied.
                    rewind($sourceFp);
                    fseek($sourceFp, $pos);
                    $sourceData = fgetcsv($sourceFp);
                    fputcsv($destinationFp, $sourceData);
                    $writtenCount++;
                } else {
                    $removedCount++;
                }
            }
        }

        return new Report(
            Report::TYPE_INFO,
            "{$writtenCount} record(s) written in file '" . realpath($this->getDestination())
                . "'. {$removedCount} record(s) were excluded."
        );
    }
}
