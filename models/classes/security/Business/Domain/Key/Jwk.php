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

use JsonSerializable;

final class Jwk implements JsonSerializable
{
    /** @var string */
    private $kty;

    /** @var string */
    private $e;

    /** @var string */
    private $n;

    /** @var string */
    private $kid;

    /** @var string */
    private $alg;

    /** @var string */
    private $use;

    public function __construct(string $kty, string $e, string $n, string $kid, string $alg, string $use)
    {
        $this->kty = $kty;
        $this->e = $e;
        $this->n = $n;
        $this->kid = $kid;
        $this->alg = $alg;
        $this->use = $use;
    }

    public function jsonSerialize(): array
    {
        return [
            'kty' => $this->kty,
            'e' => $this->e,
            'n' => $this->n,
            'kid' => $this->kid,
            'alg' => $this->alg,
            'use' => $this->use,
        ];
    }
}
