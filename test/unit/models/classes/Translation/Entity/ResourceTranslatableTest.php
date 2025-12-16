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
use oat\tao\model\Translation\Entity\ResourceTranslatable;
use PHPUnit\Framework\TestCase;

class ResourceTranslatableTest extends TestCase
{
    private ResourceTranslatable $sut;

    protected function setUp(): void
    {
        $this->sut = new ResourceTranslatable('resourceUri', 'resourceLabel');
        $this->sut->addMetadata(TaoOntology::PROPERTY_LANGUAGE, 'languageUri', 'en-US');
        $this->sut->addMetadata(TaoOntology::PROPERTY_UNIQUE_IDENTIFIER, 'abc123', null);
        $this->sut->addMetadata(TaoOntology::PROPERTY_TRANSLATION_STATUS, 'statusUri', null);
    }

    public function testGetters(): void
    {
        $this->assertSame('en-US', $this->sut->getLanguageCode());
        $this->assertSame('languageUri', $this->sut->getLanguageUri());
        $this->assertSame('statusUri', $this->sut->getStatusUri());
        $this->assertSame(
            [
                'resourceUri' => 'resourceUri',
                'resourceLabel' => 'resourceLabel',
                'metadata' => [
                    TaoOntology::PROPERTY_LANGUAGE => [
                        'value' => 'languageUri',
                        'literal' => 'en-US',
                    ],
                    TaoOntology::PROPERTY_UNIQUE_IDENTIFIER => [
                        'value' => 'abc123',
                        'literal' => null,
                    ],
                    TaoOntology::PROPERTY_TRANSLATION_STATUS => [
                        'value' => 'statusUri',
                        'literal' => null,
                    ]
                ],
            ],
            $this->sut->jsonSerialize()
        );
    }
}
