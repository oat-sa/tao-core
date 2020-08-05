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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\security\Business\Domain\Key;

final class KeyChain
{
    /** @var string */
    private $identifier;

    /** @var string */
    private $name;

    /** @var Key */
    private $publicKey;

    /** @var Key */
    private $privateKey;

    public function __construct(string $identifier, string $name, Key $publicKey, Key $privateKey)
    {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPublicKey(): Key
    {
        return $this->publicKey;
    }

    public function getPrivateKey(): Key
    {
        return $this->privateKey;
    }
}
