<?php

declare(strict_types=1);

namespace oat\tao\helpers\form\validators;

interface PreliminaryValidationInterface
{
    public function isPreValidationRequired(): bool;
}
