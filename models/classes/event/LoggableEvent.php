<?php

namespace oat\tao\model\event;

use JsonSerializable;
use oat\oatbox\event\Event;

/**
 * Class LoggableEvent
 * @package oat\tao\model\event
 */
abstract class LoggableEvent implements Event, JsonSerializable
{
    /**
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }
}
