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

namespace oat\tao\test\unit\model\Language\Filter;

use oat\tao\model\Language\Filter\LanguageAllowedFilter;
use oat\tao\model\Language\Language;
use oat\tao\model\Language\LanguageCollection;
use PHPUnit\Framework\TestCase;

class LanguageAllowedFilterTest extends TestCase
{
    public function testFilterByLanguageCollectionWithRestriction(): void
    {
        $sut = new LanguageAllowedFilter('en-US');
        $collection = $sut->filterByLanguageCollection($this->getLanguageCollection());

        $this->assertCount(1, $collection);
        $this->assertSame(
            [
                [
                    'uri' => 'uri',
                    'code' => 'en-US',
                    'label' => 'label',
                    'orientation' => 'ltr',
                ]
            ],
            json_decode(json_encode($collection->jsonSerialize()), true)
        );
    }

    public function testFilterByLanguageCollectionWithRestriction(): void
    {
        $sut = new LanguageAllowedFilter(null);
        $collection = $sut->filterByLanguageCollection($this->getLanguageCollection());

        $this->assertCount(1, $collection);
        $this->assertSame(
            [
                [
                    'uri' => 'uri',
                    'code' => 'en-US',
                    'label' => 'label',
                    'orientation' => 'ltr',
                ]
            ],
            json_decode(json_encode($collection->jsonSerialize()), true)
        );
    }

    private function getLanguageCollection(): LanguageCollection
    {
        return new LanguageCollection(...[
            new Language(
                'uri',
                'en-US',
                'label',
                'ltr'
            ),
            new Language(
                'uri',
                'pt-BR',
                'label',
                'ltr'
            ),
        ]);
    }
}
