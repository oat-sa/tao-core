<?php

declare(strict_types=1);

namespace oat\tao\helpers\form\elements;

use tao_helpers_form_FormElement as FormElement;

interface ElementAware
{
    public function setElement(FormElement $element): void;
}
