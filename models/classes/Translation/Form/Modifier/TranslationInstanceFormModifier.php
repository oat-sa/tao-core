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

use oat\generis\model\data\Ontology;
use oat\tao\model\featureFlag\FeatureFlagCheckerInterface;
use oat\tao\model\form\Modifier\AbstractFormModifier;
use oat\tao\model\TaoOntology;
use tao_helpers_form_Form as Form;
use tao_helpers_Uri;

class TranslationInstanceFormModifier extends AbstractFormModifier
{
    public const ID = 'tao.form_modifier.translation_instance';

    private FeatureFlagCheckerInterface $featureFlagChecker;

    public function __construct(Ontology $ontology, FeatureFlagCheckerInterface $featureFlagChecker)
    {
        parent::__construct($ontology);

        $this->featureFlagChecker = $featureFlagChecker;
    }

    public function supports(Form $form, array $options = []): bool
    {
        $instance = $this->getInstance($form, $options);

        if ($instance === null) {
            return false;
        }

        return $instance->isInstanceOf($this->ontology->getClass(TaoOntology::CLASS_URI_ITEM))
            || $instance->isInstanceOf($this->ontology->getClass(TaoOntology::CLASS_URI_TEST));
    }

    public function modify(Form $form, array $options = []): void
    {
        foreach ($this->getTranslationElementsToRemove($form) as $elementUri) {
            $form->removeElement(tao_helpers_Uri::encode($elementUri));
        }
    }

    private function getTranslationElementsToRemove(Form $form): array
    {
        if (!$this->featureFlagChecker->isEnabled(FeatureFlagCheckerInterface::FEATURE_TRANSLATION_ENABLED)) {
            return [
                TaoOntology::PROPERTY_UNIQUE_IDENTIFIER,
                TaoOntology::PROPERTY_LANGUAGE,
                TaoOntology::PROPERTY_TRANSLATION_TYPE,
                TaoOntology::PROPERTY_TRANSLATION_STATUS,
                TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
            ];
        }

        $elementsToRemove = [];
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
