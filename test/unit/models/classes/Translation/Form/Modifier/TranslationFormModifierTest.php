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

use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
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

    private TranslationFormModifier $sut;

    protected function setUp(): void
    {
        $this->form = $this->createMock(tao_helpers_form_Form::class);
        $this->featureFlagChecker = $this->createMock(FeatureFlagCheckerInterface::class);
        $this->sut = new TranslationFormModifier($this->featureFlagChecker);
    }

    public function testModifyTranslationDisabled(): void
    {
        $this->featureFlagChecker
            ->expects($this->once())
            ->method('isEnabled')
            ->with('FEATURE_TRANSLATION_ENABLED')
            ->willReturn(false);

        $this->form
            ->expects($this->exactly(5))
            ->method('removeElement')
            ->withConsecutive(
                [tao_helpers_Uri::encode(TaoOntology::PROPERTY_UNIQUE_IDENTIFIER)],
                [tao_helpers_Uri::encode(TaoOntology::PROPERTY_LANGUAGE)],
                [tao_helpers_Uri::encode(TaoOntology::PROPERTY_TRANSLATION_TYPE)],
                [tao_helpers_Uri::encode(TaoOntology::PROPERTY_TRANSLATION_STATUS)],
                [tao_helpers_Uri::encode(TaoOntology::PROPERTY_TRANSLATION_PROGRESS)],
            );

        $this->sut->modify($this->form);
    }

    /**
     * @dataProvider modifyTranslationEnabledDataProvider
     */
    public function testModifyTranslationEnabled(bool $developerMode, ?string $type, array $removeElements): void
    {
        $this->featureFlagChecker
            ->expects($this->exactly(2))
            ->method('isEnabled')
            ->withConsecutive(
                ['FEATURE_TRANSLATION_ENABLED'],
                ['FEATURE_TRANSLATION_DEVELOPER_MODE'],
            )
            ->willReturnOnConsecutiveCalls(true, $developerMode);

        $this->form
            ->expects($this->once())
            ->method('getValue')
            ->with(tao_helpers_Uri::encode(TaoOntology::PROPERTY_TRANSLATION_TYPE))
            ->willReturn($type);

        $this->form
            ->expects($this->exactly(count($removeElements)))
            ->method('removeElement')
            ->withConsecutive(...array_map(static fn ($element) => [tao_helpers_Uri::encode($element)], $removeElements));

        $this->sut->modify($this->form);
    }

    private function modifyTranslationEnabledDataProvider(): array
    {
        return [
            'Developer Mode enabled and no type provided' => [
                'developerMode' => true,
                'type' => null,
                'removeElements' => [
                    TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
                    TaoOntology::PROPERTY_TRANSLATION_STATUS,
                ],
            ],
            'Developer Mode enabled and type original' => [
                'developerMode' => true,
                'type' => TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL,
                'removeElements' => [
                    TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
                ],
            ],
            'Developer Mode enabled and type translation' => [
                'developerMode' => true,
                'type' => TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION,
                'removeElements' => [
                    TaoOntology::PROPERTY_TRANSLATION_STATUS,
                ],
            ],
            'Developer Mode disabled and no type provided' => [
                'developerMode' => false,
                'type' => null,
                'removeElements' => [
                    TaoOntology::PROPERTY_TRANSLATION_TYPE,
                    TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
                    TaoOntology::PROPERTY_TRANSLATION_STATUS,
                ],
            ],
            'Developer Mode disabled and type original' => [
                'developerMode' => false,
                'type' => TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL,
                'removeElements' => [
                    TaoOntology::PROPERTY_TRANSLATION_TYPE,
                    TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
                ],
            ],
            'Developer Mode disabled and type translation' => [
                'developerMode' => false,
                'type' => TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION,
                'removeElements' => [
                    TaoOntology::PROPERTY_TRANSLATION_TYPE,
                    TaoOntology::PROPERTY_TRANSLATION_STATUS,
                ],
            ],
        ];
    }
}
