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

use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Entity\ResourceTranslatableStatus;
use PHPUnit\Framework\TestCase;

class ResourceTranslatableStatusTest extends TestCase
{
    private ResourceTranslatableStatus $sut;

    protected function setUp(): void
    {
        $this->sut = new ResourceTranslatableStatus(
            'resourceUri',
            TaoOntology::CLASS_URI_ITEM,
            'languageUri',
            true,
            false
        );
    }

    public function testGetters(): void
    {
        $this->assertSame('resourceUri', $this->sut->getUri());
        $this->assertSame(TaoOntology::CLASS_URI_ITEM, $this->sut->getType());
        $this->assertSame('languageUri', $this->sut->getLanguageUri());
        $this->assertFalse($this->sut->isEmpty());
        $this->assertTrue($this->sut->isReadyForTranslation());
        $this->assertSame(
            [
                'uri' => 'resourceUri',
                'type' => TaoOntology::CLASS_URI_ITEM,
                'languageUri' => 'languageUri',
                'isReadyForTranslation' => true,
                'isEmpty' => false
            ],
            $this->sut->jsonSerialize()
        );
    }
}
