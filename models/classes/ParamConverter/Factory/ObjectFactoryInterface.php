<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Factory;

use oat\tao\model\ParamConverter\Context\ObjectFactoryContextInterface;

interface ObjectFactoryInterface
{
    public function create(ObjectFactoryContextInterface $context): object;

    public function deserialize(ObjectFactoryContextInterface $context): object;
}
