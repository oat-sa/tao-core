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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\test\unit\model\Translation\Service;

use oat\generis\model\data\Ontology;
use oat\tao\model\Translation\Query\ResourceTranslationQuery;
use oat\tao\model\Translation\Service\ResourceTranslationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResourceTranslationStatusServiceTest extends TestCase
{
    /** @var ResourceTranslationRepository */
    private $sut;

    /** @var Ontology|MockObject */
    private $ontology;

    public function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);

        $this->sut = new ResourceTranslationRepository($this->ontology);
    }

    public function testGetStatus(): void
    {
        $status = $this->sut->find(new ResourceTranslationQuery('abc123'));

        $this->assertSame(
            [
                'originResourceUri' => 'abc123',
                'translations' => [],
            ],
            $status->jsonSerialize()
        );
    }
}
