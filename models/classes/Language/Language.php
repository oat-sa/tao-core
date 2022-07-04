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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Language;

use JsonSerializable;

class Language implements JsonSerializable
{
    /** @var string */
    private $uri;

    /** @var string */
    private $code;

    /** @var string */
    private $label;

    /** @var string */
    private $orientation;

    public function __construct(string $uri, string $code, string $label, string $orientation)
    {
        $this->uri = $uri;
        $this->code = $code;
        $this->orientation = $orientation;
        $this->label = $label;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getOrientation(): string
    {
        return $this->orientation;
    }

    public function jsonSerialize()
    {
        return [
            'uri' => $this->uri,
            'code' => $this->code,
            'label' => $this->label,
            'orientation' => $this->orientation,
        ];
    }
}
