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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\test\unit\model\export;

use core_kernel_classes_ContainerCollection;
use core_kernel_classes_Property;
use core_kernel_classes_Triple;
use oat\generis\model\data\Ontology;
use PHPUnit\Framework\TestCase;
use oat\tao\model\export\JsonLdExport;
use oat\tao\model\export\Metadata\JsonLd\JsonLdTripleEncoderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

class JsonLdExportTest extends TestCase
{
    /** @var JsonLdExport */
    private $sut;

    /** @var Ontology|MockObject  */
    private $ontology;

    /** @var JsonLdTripleEncoderInterface|MockObject */
    private $tripleEncoder;

    protected function setUp(): void
    {
        $this->ontology = $this->createMock(Ontology::class);
        $this->tripleEncoder = $this->createMock(JsonLdTripleEncoderInterface::class);
        $this->sut = new JsonLdExport(
            null,
            $this->ontology
        );
    }

    public function testJsonSerializeWithTripleEncoders(): void
    {
        $this->tripleEncoder->method('encode')
            ->willReturnCallback(
                function (array $data) {
                    $data['new_field'] = 'new_value';

                    return $data;
                }
            );

        $property = $this->createMock(core_kernel_classes_Property::class);
        $property->method('getLabel')
            ->willReturn('Property_Label');

        $this->ontology
            ->method('getProperty')
            ->willReturn($property);

        $triple = new core_kernel_classes_Triple();
        $triple->subject = 'triple_subject';
        $triple->predicate = 'triple_predicate';
        $triple->object = 'triple_object';

        $triples = $this->createMock(core_kernel_classes_ContainerCollection::class);
        $triples->method('toArray')
            ->willReturn(
                [
                    $triple,
                ]
            );

        $this->sut->setTriples($triples);
        $this->sut->setTypes(['type']);
        $this->sut->setUri('uri');
        $this->sut->addTripleEncoder($this->tripleEncoder);

        $context = new stdClass();
        $context->property_label = 'triple_predicate';

        $this->assertEquals(
            [
                '@context' => $context,
                '@id' => 'uri',
                '@type' => 'type',
                'property_label' => 'triple_object',
                'new_field' => 'new_value',
            ],
            $this->sut->jsonSerialize()
        );
    }
}
