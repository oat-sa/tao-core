<?php

namespace oat\tao\model\security\xsrf;

use JsonSerializable;
use oat\tao\model\security\TokenGenerator;

/**
 * Class that provides the Token model
 *
 * @author Martijn Swinkels <m.swinkels@taotesting.com>
 */
class Token implements JsonSerializable
{
    use TokenGenerator;

    const TOKEN_KEY = 'token';
    const TIMESTAMP_KEY = 'ts';

    /**
     * @var string
     */
    private $token;

    /**
     * @var float
     */
    private $tokenTimeStamp;

    /**
     * Token constructor.
     * @param array $data
     * @throws \common_Exception
     */
    public function __construct($data = [])
    {
        if (empty($data)) {
            $this->token = $this->generate();
            $this->tokenTimeStamp = microtime(true);
        } elseif (isset($data[self::TOKEN_KEY], $data[self::TIMESTAMP_KEY])) {
            $this->setValue($data[self::TOKEN_KEY]);
            $this->setCreatedAt($data[self::TIMESTAMP_KEY]);
        }
    }

    /**
     * Set the value of the token.
     *
     * @param string $token
     */
    public function setValue($token)
    {
        $this->token = $token;
    }

    /**
     * Set the microtime at which the token was created.
     * @param float $timestamp
     */
    public function setCreatedAt($timestamp)
    {
        $this->tokenTimeStamp = $timestamp;
    }

    /**
     * Get the value of the token.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->token;
    }

    /**
     * Get the microtime at which the token was created.
     *
     * @return float
     */
    public function getCreatedAt()
    {
        return $this->tokenTimeStamp;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            self::TOKEN_KEY     => $this->getValue(),
            self::TIMESTAMP_KEY => $this->getCreatedAt(),
        ];
    }
}
