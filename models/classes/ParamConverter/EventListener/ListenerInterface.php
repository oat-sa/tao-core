<?php

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\EventListener;

use oat\tao\model\ParamConverter\Event\Event;

interface ListenerInterface
{
    public function handleEvent(Event $event): void;
}
