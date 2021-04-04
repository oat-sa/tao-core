<?php

declare(strict_types=1);

namespace oat\tao\model\role\contract;

class RuleContract implements RuleContractInterface
{
    /** @var string */
    private $extension;

    /** @var string */
    private $module;

    /** @var string|null */
    private $action;

    public function __construct(string $extension, string $module, ?string $action = null)
    {
        $this->extension = $extension;
        $this->module = $module;
        $this->action = $action;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }
}