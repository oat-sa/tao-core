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

namespace oat\tao\model\resources\relation;

use JsonSerializable;

class ResourceRelation implements JsonSerializable
{
    /** @var string */
    private $id;

    /** @var string */
    private $sourceId;

    /** @var string */
    private $label;

    /** @var string */
    private $type;

    public function __construct(string $type, string $id, string $label = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->label = $label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function withSourceId(string $sourceId): self
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'label' => $this->label ?? '',
        ];
    }
}
