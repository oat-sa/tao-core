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

namespace oat\tao\model\Lists\Business\Domain;

use JsonSerializable;

class ClassMetadata implements JsonSerializable
{
    /** @var string */
    private $class;

    /** @var string|null */
    private $parentClass;

    /** @var string */
    private $label;

    /** @var MetadataCollection */
    private $metaData;

    public function getClass(): string
    {
        return $this->class;
    }

        public function setClass(string $class): ClassMetadata
    {
        $this->class = $class;

        return $this;
    }

    public function getParentClass(): ?string
    {
        return $this->parentClass;
    }

    public function setParentClass(?string $parentClass): ClassMetadata
    {
        $this->parentClass = $parentClass;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): ClassMetadata
    {
        $this->label = $label;

        return $this;
    }

    public function getMetaData(): ?MetadataCollection
    {
        return $this->metaData;
    }

    public function setMetaData(MetadataCollection $metaData): ClassMetadata
    {
        $this->metaData = $metaData;

        return $this;
    }

    public function addMetaData(Metadata $metaData): ClassMetadata
    {
        $this->metaData->addMetadata($metaData);

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'class' => $this->class,
            'parent-class' => $this->parentClass,
            'label' => $this->label,
            'metadata' => $this->metaData,
        ];
    }
}
