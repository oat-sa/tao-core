<?php

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Contract;

use oat\tao\model\Context\ContextInterface;

interface DependsOnPropertyValidatorInterface
{
    public function validate(ContextInterface $context): void;
}
