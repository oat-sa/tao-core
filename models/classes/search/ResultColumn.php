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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\search;

use JsonSerializable;

class ResultColumn implements JsonSerializable
{
    /** @var string */
    private $id;

    /** @var string */
    private $sortId;

    /** @var string */
    private $label;

    /** @var string */
    private $type;

    /** @var string */
    private $alias;

    /** @var string */
    private $classLabel;

    /** @var bool */
    private $isDuplicated;

    /** @var bool */
    private $default;

    /** @var bool */
    private $sortable;

    public function __construct(
        string $id,
        string $sortId,
        string $label,
        string $type,
        string $alias = null,
        string $classLabel = null,
        bool $isDuplicated = false,
        bool $default = false,
        bool $sortable = false
    ) {
        $this->id = $id;
        $this->sortId = $sortId;
        $this->label = $label;
        $this->type = $type;
        $this->alias = $alias;
        $this->classLabel = $classLabel;
        $this->isDuplicated = $isDuplicated;
        $this->default = $default;
        $this->sortable = $sortable;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSortId(): string
    {
        return $this->sortId;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function getClassLabel(): ?string
    {
        return $this->classLabel;
    }

    public function isDuplicated(): bool
    {
        return $this->isDuplicated;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'sortId' => $this->getSortId(),
            'label' => $this->getLabel(),
            'type' => $this->getType(),
            'alias' => $this->getAlias(),
            'classLabel' => $this->getClassLabel(),
            'isDuplicated' => $this->isDuplicated(),
            'default' => $this->isDefault(),
            'sortable' => $this->isSortable(),
        ];
    }
}
