<?php

namespace oat\tao\model\event;

use JsonSerializable;
use oat\oatbox\event\Event;

class LoginFailedEvent implements Event, JsonSerializable
{
    private $login = '';

    public function __construct($login)
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Return a unique name for this event
     *
     * @return string
     */
    public function getName()
    {
        return __CLASS__;
    }

    public function jsonSerialize(): array
    {
        return [
            'login' => $this->getLogin()
        ];
    }
}
