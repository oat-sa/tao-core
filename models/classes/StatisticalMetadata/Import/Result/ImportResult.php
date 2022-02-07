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

namespace oat\tao\model\StatisticalMetadata\Import\Result;

use Throwable;
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\HeaderValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\WarningValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\AbstractValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\AggregatedValidationException;

class ImportResult
{
    /** @var HeaderValidationException[] */
    private $headerErrors;

    /** @var WarningValidationException[] */
    private $warnings;

    /** @var ErrorValidationException[] */
    private $errors;

    /** @var WarningValidationException[] */
    private $warningsAndErrors;

    /** @var int */
    private $totalHeaderErrors;

    /** @var int */
    private $totalWarnings;

    /** @var int */
    private $totalErrors;

    /** @var int */
    private $totalScannedRecords;

    /** @var int */
    private $totalImportedRecords;

    /** @var string */
    private $importedRecords;

    public function __construct()
    {
        $this->headerErrors = [];
        $this->warnings = [];
        $this->errors = [];
        $this->warningsAndErrors = [];
        $this->totalHeaderErrors = 0;
        $this->totalWarnings = 0;
        $this->totalErrors = 0;
        $this->totalScannedRecords = 0;
        $this->totalImportedRecords = 0;
        $this->importedRecords = [];
    }

    public function getHeaderErrors(): array
    {
        return $this->headerErrors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarningsAndErrors(): array
    {
        return $this->warningsAndErrors;
    }

    public function getTotalHeaderErrors(): int
    {
        return $this->totalHeaderErrors;
    }

    public function getTotalWarnings(): int
    {
        return $this->totalWarnings;
    }

    public function getTotalErrors(): int
    {
        return $this->totalErrors;
    }

    public function getTotalScannedRecords(): int
    {
        return $this->totalScannedRecords;
    }

    public function increaseTotalScannedRecords(): void
    {
        ++$this->totalScannedRecords;
    }

    public function getTotalImportedRecords(): int
    {
        return $this->totalImportedRecords;
    }

    public function increaseTotalImportedRecords(): void
    {
        ++$this->totalImportedRecords;
    }

    public function addImportedRecord(array $record): void
    {
        $this->importedRecords[] = $record;
    }

    public function getImportedRecords(): array
    {
        return $this->importedRecords;
    }

    public function addException(int $line, Throwable $exception): self
    {
        if ($exception instanceof AggregatedValidationException) {
            return $this->addAggregatedException($line, $exception);
        }

        if ($exception instanceof AbstractValidationException) {
            return $this->addInternalException($line, $exception);
        }

        return $this->addInternalException($line, new ErrorValidationException($exception->getMessage()));
    }

    private function addAggregatedException(int $line, AggregatedValidationException $exception): self
    {
        foreach ($exception->getWarnings() as $warningException) {
            $this->addInternalException($line, $warningException);
        }

        foreach ($exception->getErrors() as $errorException) {
            $this->addInternalException($line, $errorException);
        }

        return $this;
    }

    private function addInternalException(int $line, AbstractValidationException $exception): self
    {
        $this->warningsAndErrors[$line] = $this->warningsAndErrors[$line] ?? [];
        $this->warningsAndErrors[$line][] = $exception;

        if ($exception instanceof HeaderValidationException) {
            ++$this->totalHeaderErrors;

            $this->headerErrors[$line] = $this->headerErrors[$line] ?? [];
            $this->headerErrors[$line][] = $exception;
        }

        if ($exception instanceof WarningValidationException) {
            ++$this->totalWarnings;

            $this->warnings[$line] = $this->warnings[$line] ?? [];
            $this->warnings[$line][] = $exception;
        }

        if ($exception instanceof ErrorValidationException) {
            ++$this->totalErrors;

            $this->errors[$line] = $this->errors[$line] ?? [];
            $this->errors[$line][] = $exception;
        }

        return $this;
    }
}
