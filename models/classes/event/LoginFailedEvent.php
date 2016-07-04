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

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'login' => $this->getLogin()
        ];
    }
}
