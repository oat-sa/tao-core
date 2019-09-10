<?php

namespace oat\tao\model\tusUpload\Events;

class UploadCreatedEvent extends TusEvent
{
    const EVENT_NAME = __CLASS__;

    public function getName()
    {
        return self::EVENT_NAME;
    }
}
