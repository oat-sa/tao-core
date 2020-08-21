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
 *
 */

declare(strict_types=1);

namespace oat\tao\helpers\form\elements;

final class ElementValue
{
    /** @var string */
    private $uri;
    /** @var string */
    private $label;

    public function __construct(string $uri, string $label)
    {
        $this->uri = $uri;
        $this->label = $label;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function __toString()
    {
        return $this->uri;
    }
}
