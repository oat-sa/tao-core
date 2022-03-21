<?php

declare(strict_types=1);

namespace oat\tao\model\TestParamConverter;

class SubQuery
{
    /** @var string */
    public $uri = '';

    /** @var int */
    private $value = 0;

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }
}
