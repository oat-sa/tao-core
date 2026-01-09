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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\test\unit\helpers;

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * @return array[]
     */
    public function zipBombCandidatesProvider()
    {
        return [
            ['file' => 'bomb-1GiB', 'shouldBeConsideredAsABomb' => true,],
            ['file' => 'bomb-4GiB', 'shouldBeConsideredAsABomb' => true,],
            ['file' => 'chocolate_factory_1539704410', 'shouldBeConsideredAsABomb' => false,],
            ['file' => 'planets_and_moons_1539704402', 'shouldBeConsideredAsABomb' => false,],
        ];
    }

    /**
     * @dataProvider zipBombCandidatesProvider
     * @param string $file
     * @param bool $shouldBeConsideredAsABomb
     * @throws \common_Exception
     */
    public function testCheckWhetherArchiveIsBomb($file, $shouldBeConsideredAsABomb)
    {
        $bombCandidatesSampleDir = __DIR__ . '/../samples/zipBombCandidates/';
        $archiveObj = new \ZipArchive();
        $openingResult = $archiveObj->open($bombCandidatesSampleDir . $file . '.zip');
        $this->assertTrue($openingResult);

        $result = \tao_helpers_File::checkWhetherArchiveIsBomb($archiveObj);
        $this->assertEquals($shouldBeConsideredAsABomb, $result);
    }
}
