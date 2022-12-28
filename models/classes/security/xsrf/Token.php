<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\tao\model\security\xsrf;

use common_Exception;
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

    public const TOKEN_KEY = 'token';
    public const TIMESTAMP_KEY = 'ts';

    private string $token;
    private float $tokenTimeStamp;

    /**
     * @throws common_Exception
     */
    public function __construct(array $data = [])
    {
        if (empty($data)) {
            $this->token = $this->generate();
            $this->tokenTimeStamp = microtime(true);
        } elseif (isset($data[self::TOKEN_KEY], $data[self::TIMESTAMP_KEY])) {
            $this->setValue($data[self::TOKEN_KEY]);
            $this->setCreatedAt($data[self::TIMESTAMP_KEY]);
        }
    }

    public function setValue(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Set the microtime at which the token was created.
     */
    public function setCreatedAt(float $timestamp): void
    {
        $this->tokenTimeStamp = $timestamp;
    }

    /**
     * Get the value of the token.
     */
    public function getValue(): string
    {
        return $this->token;
    }

    /**
     * Get the microtime at which the token was created.
     */
    public function getCreatedAt(): float
    {
        return $this->tokenTimeStamp;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return [
            self::TOKEN_KEY     => $this->getValue(),
            self::TIMESTAMP_KEY => $this->getCreatedAt(),
        ];
    }
}
