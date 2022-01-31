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

namespace oat\tao\test\unit\models\classes\Csv\Service;

use oat\generis\test\TestCase;
use oat\tao\model\Csv\Resource\Reader;
use League\Csv\Reader as LeagueCsvReader;
use PHPUnit\Framework\MockObject\MockObject;

// @TODO Test class in the next iteration
class ReaderTest extends TestCase
{
    /** @var LeagueCsvReader|MockObject */
    private $leaguesCsv;

    /** @var Reader */
    private $sut;

    public function setUp(): void
    {
        $this->leaguesCsv = $this->createMock(LeagueCsvReader::class);
        $this->sut = new Reader($this->leaguesCsv);
    }

    public function testCreateFromStream(): void
    {
        $this->markTestIncomplete('Test in the next iteration');
    }
}
