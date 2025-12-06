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

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->languageService = $this->createMock(tao_models_classes_LanguageService::class);

        $this->sut = new LanguageRepository($this->ontology, $this->languageService);
    }

    public function testFindAvailableLanguagesByUsage(): void
    {
        $usageResource = $this->createMock(core_kernel_classes_Resource::class);
        $languageResource1 = $this->createMock(core_kernel_classes_Resource::class);
        $languageResource2 = $this->createMock(core_kernel_classes_Resource::class);

        $languageCode1 = $this->createMock(core_kernel_classes_Resource::class);
        $languageCode2 = $this->createMock(core_kernel_classes_Resource::class);
        $orientation = $this->createMock(core_kernel_classes_Resource::class);
        $verticalWritingMode2 = $this->createMock(core_kernel_classes_Resource::class);

        $orientation
            ->method('getUri')
            ->willReturn(tao_models_classes_LanguageService::INSTANCE_ORIENTATION_LTR);

        $verticalWritingMode2
            ->method('getUri')
            ->willReturn(tao_models_classes_LanguageService::INSTANCE_VERTICAL_WRITING_MODE_RL);

        $languageCode1
            ->method('__toString')
            ->willReturn('code1');
        $languageCode2
            ->method('__toString')
            ->willReturn('code2');

        $this->ontology
            ->method('getResource')
            ->willReturn($usageResource);

        $this->languageService
            ->method('getAvailableLanguagesByUsage')
            ->willReturn(
                [
                    $languageResource1,
                    $languageResource2
                ]
            );

        $languageResource1
            ->method('getPropertiesValues')
            ->willReturn(
                [
                    OntologyRdf::RDF_VALUE => [
                        0 => $languageCode1
                    ],
                    tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION => [
                        0 => $orientation
                    ],
                    tao_models_classes_LanguageService::PROPERTY_LANGUAGE_VERTICAL_WRITING_MODE => null,
                ]
            );

        $languageResource2
            ->method('getPropertiesValues')
            ->willReturn(
                [
                    OntologyRdf::RDF_VALUE => [
                        0 => $languageCode2
                    ],
                    tao_models_classes_LanguageService::PROPERTY_LANGUAGE_ORIENTATION => [
                        0 => $orientation
                    ],
                    tao_models_classes_LanguageService::PROPERTY_LANGUAGE_VERTICAL_WRITING_MODE => [
                        0 => $verticalWritingMode2
                    ],
                ]
            );

        $languageResource1
            ->method('getUri')
            ->willReturn('uri1');
        $languageResource2
            ->method('getUri')
            ->willReturn('uri2');

        $languageResource1
            ->method('getLabel')
            ->willReturn('label1');
        $languageResource2
            ->method('getLabel')
            ->willReturn('label2');

        $collection = $this->sut->findAvailableLanguagesByUsage();

        $this->assertCount(2, $collection);
        $this->assertSame(
            [
                [
                    'uri' => 'uri1',
                    'code' => 'code1',
                    'label' => 'label1',
                    'orientation' => 'ltr'
                ],
                [
                    'uri' => 'uri2',
                    'code' => 'code2',
                    'label' => 'label2',
                    'orientation' => 'ltr',
                    'verticalWritingMode' => 'vertical-rl'
                ]
            ],
            json_decode(json_encode($collection->jsonSerialize()), true)
        );
    }
}
