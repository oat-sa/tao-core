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

namespace oat\tao\test\unit\model\Translation\Entity;

use oat\tao\model\Translation\Entity\ResourceTranslatable;
use PHPUnit\Framework\TestCase;

class ResourceTranslatableTest extends TestCase
{
    /** @var ResourceTranslatable */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new ResourceTranslatable(
            'resourceUri',
            'uniqueId',
            'status',
            'statusUri',
            'languageCode',
            'languageUri'
        );
    }

    public function testGetters(): void
    {
        $this->assertSame(
            [
                'resourceUri' => 'resourceUri',
                'uniqueId' => 'uniqueId',
                'status' => 'status',
                'statusUri' => 'statusUri',
                'languageCode' => 'languageCode',
                'languageUri' => 'languageUri'
            ],
            $this->sut->jsonSerialize()
        );
    }
}
