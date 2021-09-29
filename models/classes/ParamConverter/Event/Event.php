<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Event;

use oat\oatbox\event\Event as BaseEvent;
use oat\tao\model\Context\ContextInterface;

interface Event extends BaseEvent
{
    public function getContext(): ContextInterface;
}
