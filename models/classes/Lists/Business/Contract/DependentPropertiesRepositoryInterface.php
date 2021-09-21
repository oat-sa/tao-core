<?php

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Contract;

use oat\tao\model\Lists\Business\Domain\DependentPropertiesRepositoryContext;

interface DependentPropertiesRepositoryInterface
{
    public function findAll(DependentPropertiesRepositoryContext $context): array;
}
