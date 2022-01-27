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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\StatisticalMetadata\Import\Builder;

use Throwable;
use oat\oatbox\reporting\Report;
use oat\tao\model\StatisticalMetadata\Import\Reporter\ImportReporter;
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\HeaderValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\WarningValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\AbstractValidationException;

class ReportBuilder
{
    public function buildByReporter(ImportReporter $reporter): Report
    {
        $reportType = $this->getReportType($reporter);

        $subReport = $this->createReportByResults($reportType, $reporter);
        $report = $this
            ->createReportByResults($reportType, $reporter)
            ->add($subReport);

        $onlyWarningReports = [];
        $warningAndErrorReports = [];

        /** @var WarningValidationException[]|ErrorValidationException[] $exceptions */
        foreach ($reporter->getWarningsAndErrors() as $line => $exceptions) {
            $headerExceptionReports = [];
            $warningReports = [];
            $errorReports = [];

            foreach ($exceptions as $exception) {
                if ($exception instanceof HeaderValidationException) {
                    $headerExceptionReports[] = $this->createReportByException($exception);

                    continue;
                }

                if ($exception instanceof WarningValidationException) {
                    $warningReports[] = $this->createReportByException($exception);

                    continue;
                }

                if ($exception instanceof ErrorValidationException) {
                    $errorReports[] = $this->createReportByException($exception);
                }
            }

            if (empty($headerExceptionReports) && empty($errorReports) && empty($warningReports)) {
                continue;
            }

            $lineMainReportType = empty($headerExceptionReports) && empty($errorReports)
                ? Report::TYPE_WARNING
                : Report::TYPE_ERROR;
            $lineMainReport = Report::create($lineMainReportType, 'line %s: %s', [$line, '']);

            foreach ($headerExceptionReports as $headerExceptionReport) {
                $lineMainReport->add($headerExceptionReport);
            }

            foreach ($warningReports as $warningReport) {
                $lineMainReport->add($warningReport);
            }

            foreach ($errorReports as $errorReport) {
                $lineMainReport->add($errorReport);
            }

            if (empty($errorReports)) {
                $onlyWarningReports[$line] = $lineMainReport;
            }

            if (!empty($errorReports)) {
                $warningAndErrorReports[$line] = $lineMainReport;
            }
        }

        if (!empty($headerExceptionReports)) {
            $this->createAndAddSubReport(
                $report,
                $headerExceptionReports,
                Report::TYPE_ERROR,
                'Header contain an error(s)'
            );

            return $report;
        }

        if (!empty($warningAndErrorReports)) {
            $this->createAndAddSubReport(
                $report,
                $warningAndErrorReports,
                Report::TYPE_ERROR,
                '%s line(s) contain an error(s) and cannot be imported'
            );
        }

        if (!empty($onlyWarningReports)) {
            $this->createAndAddSubReport(
                $report,
                $onlyWarningReports,
                Report::TYPE_WARNING,
                '%s line(s) are imported with warning(s)'
            );
        }

        return $report;
    }

    public function buildByException(Throwable $exception): Report
    {
        $report = Report::create(Report::TYPE_ERROR, 'CSV import failed');
        $report->add(
            Report::create(
                Report::TYPE_ERROR,
                'An unexpected error occurred during the CSV import. The system returned the following error: %s',
                [
                    $exception->getMessage(),
                ]
            )
        );

        return $report;
    }

    private function createAndAddSubReport(
        Report $mainReport,
        array $reports,
        string $reportType,
        string $message
    ): void {
        $newReport = Report::create($reportType, $message, [count($reports)]);

        foreach ($reports as $subReport) {
            $newReport->add($subReport);
        }

        $mainReport->add($newReport);
    }

    private function createReportByResults(string $type, ImportReporter $reporter): Report
    {
        if ($reporter->getTotalImportedRecords() === 0  || $reporter->getTotalScannedRecords() === 0) {
            return Report::create(
                $type,
                'CSV import failed: %s/%s line(s) are imported',
                [
                    $reporter->getTotalImportedRecords(), $reporter->getTotalScannedRecords(),
                ]
            );
        }

        if ($reporter->getTotalErrors() === 0 && $reporter->getTotalWarnings() === 0) {
            return Report::create(
                $type,
                'CSV import successful: %s/%s line(s) are imported',
                [
                    $reporter->getTotalImportedRecords(),
                    $reporter->getTotalScannedRecords(),
                ]
            );
        }

        return Report::create(
            $type,
            'CSV import partially successful: %s/%s line(s) are imported (%s warning(s), %s error(s))',
            [
                $reporter->getTotalImportedRecords(),
                $reporter->getTotalScannedRecords(),
                $reporter->getTotalWarnings(),
                $reporter->getTotalErrors(),
            ]
        );
    }

    private function createReportByException(AbstractValidationException $exception): Report
    {
        if ($exception instanceof HeaderValidationException) {
            return Report::create(
                Report::TYPE_ERROR,
                $exception->getMessage(),
                $exception->getInterpolationData()
            );
        }

        if ($exception instanceof WarningValidationException) {
            return Report::create(
                Report::TYPE_WARNING,
                $exception->getMessage(),
                $exception->getInterpolationData()
            );
        }

        if ($exception instanceof ErrorValidationException) {
            return Report::create(
                Report::TYPE_ERROR,
                $exception->getMessage(),
                $exception->getInterpolationData()
            );
        }
    }

    private function getReportType(ImportReporter $reporter): string
    {
        if ($reporter->getTotalImportedRecords() === 0) {
            return Report::TYPE_ERROR;
        }

        if ($reporter->getTotalWarnings() === 0 && $reporter->getTotalErrors() === 0) {
            return Report::TYPE_SUCCESS;
        }

        return Report::TYPE_WARNING;
    }
}
