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
 * Copyright (c) 2022  (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\metadata\compiler;

use core_kernel_classes_Resource;
use oat\generis\model\GenerisRdf;
use oat\generis\test\OntologyMockTrait;
use PHPUnit\Framework\TestCase;
use oat\tao\model\export\JsonLdExport;
use oat\tao\model\export\Metadata\JsonLd\JsonLdTripleEncoderInterface;
use oat\tao\model\metadata\compiler\AdvancedJsonResourceMetadataCompiler;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

class AdvancedJsonResourceMetadataCompilerTest extends TestCase
{
    use OntologyMockTrait;

    /** @var JsonLdTripleEncoderInterface|MockObject */
    private $jsonLdTripleEncoder;

    /** @var JsonLdExport|MockObject */
    private $jsonLdExport;

    /** @var AdvancedJsonResourceMetadataCompiler */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jsonLdTripleEncoder = $this->createMock(JsonLdTripleEncoderInterface::class);
        $this->jsonLdExport = $this->createMock(JsonLdExport::class);
        $this->subject = new AdvancedJsonResourceMetadataCompiler($this->jsonLdTripleEncoder, $this->jsonLdExport);
    }

    public function testJsonSerialize(): void
    {
        $resource = $this->createMock(core_kernel_classes_Resource::class);

        $context = new stdClass();
        $context->type = JsonLdTripleEncoderInterface::RDF_TYPE;
        $context->value = JsonLdTripleEncoderInterface::RDF_VALUE;
        $context->alias = GenerisRdf::PROPERTY_ALIAS;

        $this->jsonLdExport
            ->method('setResource')
            ->willReturnSelf();

        $this->jsonLdExport
            ->method('addTripleEncoder')
            ->willReturnSelf();

        $this->jsonLdExport
            ->method('jsonSerialize')
            ->willReturn(
                [
                    '@context' => new stdClass(),
                ]
            );

        $this->assertEquals(
            [
                '@context' => $context
            ],
            $this->subject->compile($resource)
        );
    }
}
