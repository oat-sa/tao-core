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

namespace oat\tao\model\Translation\Form\Modifier;

use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\form\Modifier\AbstractFormModifier;
use oat\tao\model\TaoOntology;
use tao_helpers_form_Form;
use tao_helpers_Uri;

class TranslationFormModifier extends AbstractFormModifier
{
    private FeatureFlagCheckerInterface $featureFlagChecker;

    public function __construct(FeatureFlagCheckerInterface $featureFlagChecker)
    {
        $this->featureFlagChecker = $featureFlagChecker;
    }

    public function modify(tao_helpers_form_Form $form, array $options = []): void
    {
        foreach ($this->getTranslationElementsToRemove($form) as $elementUri) {
            $form->removeElement(tao_helpers_Uri::encode($elementUri));
        }
    }

    private function getTranslationElementsToRemove(tao_helpers_form_Form $form): array
    {
        if (!$this->featureFlagChecker->isEnabled('FEATURE_FLAG_TRANSLATION_ENABLED')) {
            return [
                TaoOntology::PROPERTY_UNIQUE_IDENTIFIER,
                TaoOntology::PROPERTY_LANGUAGE,
                TaoOntology::PROPERTY_TRANSLATION_TYPE,
                TaoOntology::PROPERTY_TRANSLATION_STATUS,
                TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
            ];
        }

        $elementsToRemove = [];

        if (!$this->featureFlagChecker->isEnabled('FEATURE_TRANSLATION_DEVELOPER_MODE')) {
            $elementsToRemove[] = TaoOntology::PROPERTY_TRANSLATION_TYPE;
        }

        $translationTypeValue = $form->getValue(tao_helpers_Uri::encode(TaoOntology::PROPERTY_TRANSLATION_TYPE));
        $isTranslationTypeValueEmpty = empty($translationTypeValue);

        if (
            $isTranslationTypeValueEmpty
            || $translationTypeValue === TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL
        ) {
            $elementsToRemove[] = TaoOntology::PROPERTY_TRANSLATION_PROGRESS;
        }

        if (
            $isTranslationTypeValueEmpty
            || $translationTypeValue === TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION
        ) {
            $elementsToRemove[] = TaoOntology::PROPERTY_TRANSLATION_STATUS;
        }

        return $elementsToRemove;
    }
}
