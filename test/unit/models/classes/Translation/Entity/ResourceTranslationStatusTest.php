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

use oat\tao\model\Translation\Entity\ResourceTranslationStatus;
use PHPUnit\Framework\TestCase;

class ResourceTranslationStatusTest extends TestCase
{
    /** @var ResourceTranslationStatus */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new ResourceTranslationStatus('myResourceUri');
    }

    public function testGetStatus(): void
    {
        $this->sut->addTranslation('en-US', ResourceTranslationStatus::STATUS_PENDING, 'uri1');
        $this->sut->addTranslation('fr-FR', ResourceTranslationStatus::STATUS_TRANSLATING, 'uri2');
        $this->sut->addTranslation('de-DE', ResourceTranslationStatus::STATUS_TRANSLATED, 'uri3');

        $this->assertSame(
            [
                'originResourceUri' => 'myResourceUri',
                'translations' => [
                    [
                        'status' => 'pending',
                        'locale' => 'en-US',
                        'resourceUri' => 'uri1',
                    ],
                    [
                        'status' => 'translating',
                        'locale' => 'fr-FR',
                        'resourceUri' => 'uri2'
                    ],
                    [
                        'status' => 'translated',
                        'locale' => 'de-DE',
                        'resourceUri' => 'uri3'
                    ]
                ],
            ],
            $this->sut->jsonSerialize()
        );
    }
}
