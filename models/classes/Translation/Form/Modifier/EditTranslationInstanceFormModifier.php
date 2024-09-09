<?php

namespace oat\tao\model\Translation\Form\Modifier;

use oat\generis\model\data\Ontology;
use oat\tao\model\TaoOntology;
use tao_helpers_form_Form as Form;
use oat\tao\model\form\Modifier\FormModifierInterface;
use tao_helpers_Uri;

class EditTranslationInstanceFormModifier implements FormModifierInterface
{
    public const ID = 'tao.form_modifier.edit_translation_instance';

    private Ontology $ontology;

    public function __construct(Ontology $ontology)
    {
        $this->ontology = $ontology;
    }

    public function supports(Form $form, array $options = []): bool
    {
        $instanceUri = $form->getValue(self::FORM_INSTANCE_URI);

        if (!$instanceUri) {
            return false;
        }

        $instance = $this->ontology->getResource($instanceUri);

        // @TODO Check if FF for translation enabled
        return $instance->isInstanceOf($this->ontology->getClass(TaoOntology::CLASS_URI_ITEM))
            || $instance->isInstanceOf($this->ontology->getClass(TaoOntology::CLASS_URI_TEST));
    }

    public function modify(Form $form, array $options = []): void
    {
        $translationTypeValue = $form->getValue(tao_helpers_Uri::encode(TaoOntology::PROPERTY_TRANSLATION_TYPE));

        $translationTypeRelatedElementUris = [
            TaoOntology::PROPERTY_TRANSLATION_STATUS,
            TaoOntology::PROPERTY_TRANSLATION_PROGRESS,
        ];

        if ($translationTypeValue === TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_ORIGINAL) {
            unset($translationTypeRelatedElementUris[0]);
        }

        if ($translationTypeValue === TaoOntology::PROPERTY_VALUE_TRANSLATION_TYPE_TRANSLATION) {
            unset($translationTypeRelatedElementUris[1]);
        }

        foreach ($translationTypeRelatedElementUris as $elementUri) {
            $form->removeElement(tao_helpers_Uri::encode($elementUri));
        }
    }
}
