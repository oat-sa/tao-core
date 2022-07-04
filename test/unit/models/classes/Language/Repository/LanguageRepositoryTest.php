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

namespace oat\tao\test\unit\model\Language\Repository;

use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\OntologyRdf;
use oat\tao\model\Language\Repository\LanguageRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use tao_models_classes_LanguageService;

class LanguageRepositoryTest extends TestCase
{
    /** @var LanguageRepository */
    private $sut;

    /** @var MockObject|tao_models_classes_LanguageService */
    private $languageService;

    /** @var Ontology|MockObject */
    private $ontology;

    public function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->languageService = $this->createMock(tao_models_classes_LanguageService::class);

        $this->sut = new LanguageRepository($this->ontology, $this->languageService);
    }

    public function testFindAvailableLanguagesByUsage(): void
    {
        $usageResource = $this->createMock(core_kernel_classes_Resource::class);
        $languageResource = $this->createMock(core_kernel_classes_Resource::class);

        $languageCode = $this->createMock(core_kernel_classes_Resource::class);
        $orientation = $this->createMock(core_kernel_classes_Resource::class);

        $orientation
            ->method('getUri')
            ->willReturn(tao_models_classes_LanguageService::INSTANCE_ORIENTATION_LTR);

        $languageCode
            ->method('__toString')
            ->willReturn('code');

        $this->ontology
            ->method('getResource')
            ->willReturn($usageResource);

        $this->languageService
            ->method('getAvailableLanguagesByUsage')
            ->willReturn(
                [
                    $languageResource
                ]
            );

        $languageResource
            ->method('getPropertiesValues')
            ->willReturn(
                [
                    OntologyRdf::RDF_VALUE => [
                        0 => $languageCode
                    ],
                    tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION => [
                        0 => $orientation
                    ],
                ]
            );

        $languageResource
            ->method('getUri')
            ->willReturn('uri');

        $languageResource
            ->method('getLabel')
            ->willReturn('label');

        $collection = $this->sut->findAvailableLanguagesByUsage();

        $this->assertCount(1, $collection);
        $this->assertSame(
            [
                [
                    'uri' => 'uri',
                    'code' => 'code',
                    'label' => 'label',
                    'orientation' => tao_models_classes_LanguageService::INSTANCE_ORIENTATION_LTR,
                ]
            ],
            json_decode(json_encode($collection->jsonSerialize()), true)
        );
    }
}
