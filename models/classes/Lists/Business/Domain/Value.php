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
 * @author Sergei Mikhailov <sergei.mikhailov@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Domain;

use JsonSerializable;
use tao_helpers_Uri;

class Value implements JsonSerializable
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $uri;

    /** @var string */
    private $label;

    /** @var string|null */
    private $originalUri;

    /** @var bool */
    private $hasChanges = false;

    public function __construct(?int $id, string $uri, string $label)
    {
        $this->id          = $id;
        $this->uri         = $uri;
        $this->label       = $label;
        $this->originalUri = null === $id ? null : $uri;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUri(string $uri): self
    {
        if ($this->uri !== $uri) {
            $this->hasChanges = true;
        }

        $this->uri = $uri;

        return $this;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setLabel(string $label): self
    {
        if ($this->label !== $label) {
            $this->hasChanges = true;
        }

        $this->label = $label;

        return $this;
    }

    public function hasChanges(): bool
    {
        return $this->hasChanges;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getOriginalUri(): ?string
    {
        return $this->originalUri;
    }

    public function hasModifiedUri(): bool
    {
        return $this->originalUri !== $this->uri;
    }

    public function jsonSerialize(): array
    {
        return [
            'uri'   => tao_helpers_Uri::encode($this->uri),
            'label' => $this->label,
        ];
    }
}
