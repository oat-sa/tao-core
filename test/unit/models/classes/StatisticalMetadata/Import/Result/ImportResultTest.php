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

namespace oat\tao\test\unit\models\classes\StatisticalMetadata\Import\Result;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use oat\tao\model\StatisticalMetadata\Import\Result\ImportResult;
use oat\tao\model\StatisticalMetadata\Import\Exception\ErrorValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\HeaderValidationException;
use oat\tao\model\StatisticalMetadata\Import\Exception\WarningValidationException;

class ImportResultTest extends TestCase
{
    /** @var HeaderValidationException|MockObject */
    private $headerValidationException;

    /** @var WarningValidationException|MockObject */
    private $warningValidationException;

    /** @var ErrorValidationException|MockObject */
    private $errorValidationException;

    /** @var ImportResult */
    private $sut;

    protected function setUp(): void
    {
        $this->headerValidationException = $this->createMock(HeaderValidationException::class);
        $this->warningValidationException = $this->createMock(WarningValidationException::class);
        $this->errorValidationException = $this->createMock(ErrorValidationException::class);

        $this->sut = new ImportResult();
    }

    public function testGetHeaderErrors(): void
    {
        $this->assertEmpty($this->sut->getHeaderErrors());

        $this->sut->addException(0, $this->headerValidationException);

        $this->assertEquals(
            [
                [$this->headerValidationException]
            ],
            $this->sut->getHeaderErrors()
        );
    }

    public function testGetWarnings(): void
    {
        $this->assertEmpty($this->sut->getWarnings());

        $this->sut->addException(0, $this->warningValidationException);

        $this->assertEquals(
            [
                [$this->warningValidationException]
            ],
            $this->sut->getWarnings()
        );
    }

    public function testGetErrors(): void
    {
        $this->assertEmpty($this->sut->getErrors());

        $this->sut->addException(0, $this->errorValidationException);

        $this->assertEquals(
            [
                [$this->errorValidationException]
            ],
            $this->sut->getErrors()
        );
    }

    public function testGetWarningsAndErrors(): void
    {
        $this->assertEmpty($this->sut->getWarningsAndErrors());

        $exceptions = [];
        $exceptionsToAdd = [
            $this->headerValidationException,
            $this->warningValidationException,
            $this->errorValidationException,
        ];

        foreach ($exceptionsToAdd as $exception) {
            $exceptions[] = $exception;
            $this->sut->addException(0, $exception);

            $this->assertEquals([$exceptions], $this->sut->getWarningsAndErrors());
        }
    }

    public function testGetTotalHeaderErrors(): void
    {
        $this->assertEquals(0, $this->sut->getTotalHeaderErrors());

        $this->sut->addException(0, $this->headerValidationException);
        $this->assertEquals(1, $this->sut->getTotalHeaderErrors());

        $this->sut->addException(0, $this->warningValidationException);
        $this->assertEquals(1, $this->sut->getTotalHeaderErrors());

        $this->sut->addException(0, $this->errorValidationException);
        $this->assertEquals(1, $this->sut->getTotalHeaderErrors());

        $this->sut->addException(0, $this->headerValidationException);
        $this->assertEquals(2, $this->sut->getTotalHeaderErrors());
    }

    public function testGetTotalWarnings(): void
    {
        $this->assertEquals(0, $this->sut->getTotalWarnings());

        $this->sut->addException(0, $this->warningValidationException);
        $this->assertEquals(1, $this->sut->getTotalWarnings());

        $this->sut->addException(0, $this->headerValidationException);
        $this->assertEquals(1, $this->sut->getTotalWarnings());

        $this->sut->addException(0, $this->errorValidationException);
        $this->assertEquals(1, $this->sut->getTotalWarnings());

        $this->sut->addException(0, $this->warningValidationException);
        $this->assertEquals(2, $this->sut->getTotalWarnings());
    }

    public function testGetTotalErrors(): void
    {
        $this->assertEquals(0, $this->sut->getTotalErrors());

        $this->sut->addException(0, $this->errorValidationException);
        $this->assertEquals(1, $this->sut->getTotalErrors());

        $this->sut->addException(0, $this->headerValidationException);
        $this->assertEquals(2, $this->sut->getTotalErrors());

        $this->sut->addException(0, $this->warningValidationException);
        $this->assertEquals(2, $this->sut->getTotalErrors());

        $this->sut->addException(0, $this->errorValidationException);
        $this->assertEquals(3, $this->sut->getTotalErrors());
    }

    public function testGetAndIncreaseTotalScannedRecords(): void
    {
        $this->assertEquals(0, $this->sut->getTotalScannedRecords());

        $this->sut->increaseTotalScannedRecords();

        $this->assertEquals(1, $this->sut->getTotalScannedRecords());
    }

    public function testGetAndIncreaseTotalImportedRecords(): void
    {
        $this->assertEquals(0, $this->sut->getTotalImportedRecords());

        $this->sut->increaseTotalImportedRecords();

        $this->assertEquals(1, $this->sut->getTotalImportedRecords());
    }

    public function testAddException(): void
    {
        $this->assertEmpty($this->sut->getWarningsAndErrors());

        $this->sut->addException(0, $this->errorValidationException);
        $this->assertEquals(
            [
                [$this->errorValidationException],
            ],
            $this->sut->getWarningsAndErrors()
        );

        $this->sut->addException(0, $this->warningValidationException);
        $this->assertEquals(
            [
                [
                    $this->errorValidationException,
                    $this->warningValidationException,
                ],
            ],
            $this->sut->getWarningsAndErrors()
        );

        $this->sut->addException(1, $this->headerValidationException);
        $this->assertEquals(
            [
                [
                    $this->errorValidationException,
                    $this->warningValidationException,
                ],
                [
                    $this->headerValidationException,
                ],
            ],
            $this->sut->getWarningsAndErrors()
        );
    }
}
