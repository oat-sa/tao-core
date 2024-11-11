<?php

namespace oat\tao\model\resources\Event;

use oat\oatbox\event\Event;

class InstanceCopiedEvent implements Event
{
    private string $instanceUri;

    public function __construct(string $instanceUri)
    {
        $this->instanceUri = $instanceUri;
    }

    public function getName(): string
    {
        return __CLASS__;
    }

    public function getInstanceUri(): string
    {
        return $this->instanceUri;
    }
}
