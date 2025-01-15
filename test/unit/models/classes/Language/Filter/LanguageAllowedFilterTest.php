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

namespace oat\tao\test\unit\model\Language\Filter;

use oat\tao\model\Language\Filter\LanguageAllowedFilter;
use oat\tao\model\Language\Language;
use oat\tao\model\Language\LanguageCollection;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\TaoOntology;
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

    public function testFilterByLanguageCollectionWithoutRestriction(): void
    {
        $sut = new LanguageAllowedFilter();
        $collection = $sut->filterByLanguageCollection($this->getLanguageCollection());

        $this->assertCount(2, $collection);
        $this->assertSame(
            [
                [
                    'uri' => 'uri',
                    'code' => 'en-US',
                    'label' => 'label',
                    'orientation' => 'ltr',
                ],
                [
                    'uri' => 'uri',
                    'code' => 'pt-BR',
                    'label' => 'label',
                    'orientation' => 'ltr',
                ]
            ],
            json_decode(json_encode($collection->jsonSerialize()), true)
        );
    }

    public function testFilterByValueCollectionWithRestriction(): void
    {
        $sut = new LanguageAllowedFilter('en-US');
        $collection = $sut->filterByValueCollection($this->getLanguageValueCollection());

        $this->assertCount(1, $collection);
        $this->assertSame(
            [
                [
                    'uri' => 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langen-US',
                    'label' => 'label',
                    'dependencyUri' => null,
                ]
            ],
            json_decode(json_encode($collection->jsonSerialize()), true)
        );
    }

    public function testFilterByValueCollectionWithoutRestriction(): void
    {
        $sut = new LanguageAllowedFilter();
        $collection = $sut->filterByValueCollection($this->getLanguageValueCollection());

        $this->assertCount(2, $collection);
        $this->assertSame(
            [
                [
                    'uri' => 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langen-US',
                    'label' => 'label',
                    'dependencyUri' => null,
                ],
                [
                    'uri' => 'http_2_www_0_tao_0_lu_1_Ontologies_1_TAO_0_rdf_3_Langpt-BR',
                    'label' => 'label',
                    'dependencyUri' => null,
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
                'ltr',
                null
            ),
            new Language(
                'uri',
                'pt-BR',
                'label',
                'ltr',
                null
            ),
        ]);
    }

    private function getLanguageValueCollection(): ValueCollection
    {
        return new ValueCollection(
            'uri',
            ...[
                new Value(
                    'uri',
                    TaoOntology::LANGUAGE_PREFIX . 'en-US',
                    'label'
                ),
                new Value(
                    'uri',
                    TaoOntology::LANGUAGE_PREFIX . 'pt-BR',
                    'label'
                ),
            ]
        );
    }
}
