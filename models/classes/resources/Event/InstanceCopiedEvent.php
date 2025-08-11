<?php

namespace oat\tao\model\resources\Event;

use oat\oatbox\event\Event;

class InstanceCopiedEvent implements Event
{
    private string $instanceUri;
    private ?string $originInstanceUri;
    private array $options;

    public function __construct(string $instanceUri, ?string $originInstanceUri = null, array $options = [])
    {
        $this->instanceUri = $instanceUri;
        $this->originInstanceUri = $originInstanceUri;
        $this->options = $options;
    }

    public function getName(): string
    {
        return __CLASS__;
    }

    public function getInstanceUri(): string
    {
        return $this->instanceUri;
    }
    public function getOriginInstanceUri(): ?string
    {
        return $this->originInstanceUri;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
