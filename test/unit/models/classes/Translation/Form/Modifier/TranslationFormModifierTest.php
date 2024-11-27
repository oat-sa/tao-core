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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\test\unit\models\classes\Translation\Form\Modifier;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\featureFlag\Service\FeatureFlagPropertiesMapping;
use oat\tao\model\TaoOntology;
use oat\tao\model\Translation\Form\Modifier\TranslationFormModifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use tao_helpers_form_Form;
use tao_helpers_Uri;

class TranslationFormModifierTest extends TestCase
{
    /** @var tao_helpers_form_Form|MockObject */
    private tao_helpers_form_Form $form;

    /** @var FeatureFlagCheckerInterface|MockObject */
    private FeatureFlagCheckerInterface $featureFlagChecker;

    /** @var Ontology|MockObject */
    private Ontology $ontology;

    private TranslationFormModifier $sut;

    protected function setUp(): void
    {
        $this->form = $this->createMock(tao_helpers_form_Form::class);

        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);
        $featureFlagPropertiesMapping = $this->createMock(FeatureFlagPropertiesMapping::class);
        $this->ontology = $this->createMock(Ontology::class);

        $featureFlagPropertiesMapping
            ->method('getFeatureProperties')
            ->with('FEATURE_FLAG_TRANSLATION_ENABLED')
            ->willReturn([
                TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI,
                TaoOntology::PROPERTY_LANGUAGE,
                TaoOntology::PROPERTY_TRANSLATION_STATUS,
                TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
                TaoOntology::PROPERTY_TRANSLATION_TYPE,
                TaoOntology::PROPERTY_TRANSLATED_INTO_LANGUAGES,
            ]);

        $this->sut = new TranslationFormModifier(
            $this->featureFlagChecker,
            $featureFlagPropertiesMapping,
            $this->ontology
        );
    }

    public function testModifyTranslationDisabled(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->with('FEATURE_FLAG_TRANSLATION_ENABLED')
            ->willReturn(false);

        $this->form
            ->expects($this->exactly(6))
            ->method('removeElement')
            ->withConsecutive(
                [tao_helpers_Uri::encode(TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI)],
                [tao_helpers_Uri::encode(TaoOntology::PROPERTY_LANGUAGE)],
                [tao_helpers_Uri::encode(TaoOntology::PROPERTY_TRANSLATION_STATUS)],
                [tao_helpers_Uri::encode(TaoOntology::PROPERTY_TRANSLATION_PROGRESS)],
                [tao_helpers_Uri::encode(TaoOntology::PROPERTY_TRANSLATION_TYPE)],
                [tao_helpers_Uri::encode(TaoOntology::PROPERTY_TRANSLATED_INTO_LANGUAGES)],
            );

        $this->sut->modify($this->form);
    }

    /**
     * @dataProvider modifyTranslationEnabledDataProvider
     */
    public function testModifyTranslationEnabled(?string $type, array $removeElements): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->with('FEATURE_FLAG_TRANSLATION_ENABLED')
            ->willReturn(true);

        $this->form
            ->expects($this->once())
            ->method('getValue')
            ->with('uri')
            ->willReturn('instanceUri');

        $instance = $this->createMock(core_kernel_classes_Resource::class);

        $this->ontology
            ->expects($this->once())
            ->method('getResource')
            ->with('instanceUri')
            ->willReturn($instance);

        $property = $this->createMock(core_kernel_classes_Property::class);

        $this->ontology
            ->expects($this->once())
            ->method('getProperty')
            ->with(TaoOntology::PROPERTY_TRANSLATION_TYPE)
            ->willReturn($property);

        if ($type !== null) {
            $typeValue = $type;

            $type = $this->createMock(core_kernel_classes_Resource::class);
            $type
                ->expects($this->once())
                ->method('getUri')
                ->willReturn($typeValue);
        }

        $instance
            ->expects($this->once())
            ->method('getOnePropertyValue')
            ->with($property)
            ->willReturn($type);


        $this->form
            ->expects($this->exactly(count($removeElements)))
            ->method('removeElement')
            ->withConsecutive(
                ...array_map(
                    static fn ($element) => [tao_helpers_Uri::encode($element)],
                    $removeElements
                )
            );

        $this->sut->modify($this->form);
    }

    public function modifyTranslationEnabledDataProvider(): array
    {
        return [
            'No type provided' => [
                'type' => null,
                'removeElements' => [
                    TaoOntology::PROPERTY_TRANSLATION_TYPE,
                    TaoOntology::PROPERTY_TRANSLATED_INTO_LANGUAGES,
                    TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
                    TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI,
                    TaoOntology::PROPERTY_TRANSLATION_STATUS,
                ],
            ],
            'Type original' => [
                'type' => TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL,
                'removeElements' => [
                    TaoOntology::PROPERTY_TRANSLATION_TYPE,
                    TaoOntology::PROPERTY_TRANSLATED_INTO_LANGUAGES,
                    TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
                    TaoOntology::PROPERTY_TRANSLATION_ORIGINAL_RESOURCE_URI,
                ],
            ],
            'Type translation' => [
                'type' => TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION,
                'removeElements' => [
                    TaoOntology::PROPERTY_TRANSLATION_TYPE,
                    TaoOntology::PROPERTY_TRANSLATED_INTO_LANGUAGES,
                    TaoOntology::PROPERTY_TRANSLATION_STATUS,
                ],
            ],
        ];
    }
}
