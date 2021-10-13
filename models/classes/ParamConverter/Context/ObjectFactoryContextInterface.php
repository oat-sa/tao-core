<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Context;

interface ObjectFactoryContextInterface
{
    public function getClass(): string;

    public function getData(): array;

    public function getFormat(): string;

    public function getContext(): array;
}
