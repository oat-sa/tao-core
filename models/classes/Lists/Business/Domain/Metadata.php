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
 * Copyright (c) 2020|2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\Lists\Business\Domain;

use JsonSerializable;
use tao_helpers_Uri;

class Metadata implements JsonSerializable
{
    /** @var string */
    private $label;

    /** @var string */
    private $alias;

    /** @var string */
    private $type;

    /** @var array */
    private $values = [];

    /** @var string|null  */
    private $uri;

    /** @var string|null  */
    private $propertyUri;

    /** @var string|null  */
    private $classLabel;

    /** @var bool */
    private $isDuplicated = false;

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): Metadata
    {
        $this->label = $label;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Metadata
    {
        $this->type = $type;

        return $this;
    }

    public function getValues(): ?array
    {
        return $this->values;
    }

    public function setValues(?array $values): Metadata
    {
        $this->values = $values;

        return $this;
    }

    public function addValue(string $value): Metadata
    {
        array_push($this->values, $value);

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): Metadata
    {
        $this->uri = $uri;

        return $this;
    }

    public function getPropertyUri(): ?string
    {
        return $this->propertyUri;
    }

    public function setPropertyUri(?string $propertyUri): Metadata
    {
        $this->propertyUri = $propertyUri;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getClassLabel(): ?string
    {
        return $this->classLabel;
    }

    public function setClassLabel(?string $classLabel): self
    {
        $this->classLabel = $classLabel;

        return $this;
    }

    public function isDuplicated(): bool
    {
        return $this->isDuplicated;
    }

    public function markAsDuplicated(): self
    {
        $this->isDuplicated = true;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'label' => $this->label,
            'alias' => $this->alias,
            'type' => $this->type,
            'values' => $this->values,
            'isDuplicated' => $this->isDuplicated,
            'propertyUri' => tao_helpers_Uri::encode($this->propertyUri),
            'uri' => $this->uri,
            'class' => [
                'label' => $this->classLabel
            ],
        ];
    }
}
