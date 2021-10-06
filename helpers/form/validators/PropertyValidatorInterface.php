<?php

declare(strict_types=1);

namespace oat\tao\helpers\form\validators;

use core_kernel_classes_Property;

interface PropertyValidatorInterface
{
    public function setProperty(core_kernel_classes_Property $property): void;
}
