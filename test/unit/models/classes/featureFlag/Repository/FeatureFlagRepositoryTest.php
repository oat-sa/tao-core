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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\unit\test\model\featureFlag\Repository;

use oat\generis\model\data\Ontology;
use oat\oatbox\cache\SimpleCache;
use oat\tao\model\featureFlag\Repository\FeatureFlagRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FeatureFlagRepositoryTest extends TestCase
{
    /** @var FeatureFlagRepository */
    private $subject;

    /** @var SimpleCache|MockObject */
    private $simpleCache;

    /** @var Ontology|MockObject */
    private $ontology;

    public function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->simpleCache = $this->createMock(SimpleCache::class);
        $this->subject = new FeatureFlagRepository($this->ontology, $this->simpleCache, []);
    }

    public function testSave(): void
    {
        $this->markTestIncomplete();
    }

    public function testList(): void
    {
        $this->markTestIncomplete();
    }

    public function testGet(): void
    {
        $this->markTestIncomplete();
    }

    public function testClearCache(): void
    {
        $this->markTestIncomplete();
    }
}
