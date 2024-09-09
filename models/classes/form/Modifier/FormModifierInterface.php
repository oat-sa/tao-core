<?php

namespace oat\tao\model\form\Modifier;

use tao_helpers_form_Form as Form;

interface FormModifierInterface
{
    public const FORM_INSTANCE_URI = 'uri';

    public function supports(Form $form, array $options = []): bool;

    public function modify(Form $form, array $options = []): void;
}