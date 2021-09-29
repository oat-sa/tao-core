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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\ParamConverter\Configuration;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class ParamConverter extends ConfigurationAnnotation
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $class;

    /** @var array */
    private $options = [];

    /** @var bool */
    private $isOptional = false;

    /** @var string|null */
    private $converter;

    /**
     * @param string|array $data
     */
    public function __construct(
        $data = [],
        string $converter = null,
        string $class = null,
        array $options = [],
        bool $isOptional = false
    ) {
        $values = [];

        if (is_string($data)) {
            $values['value'] = $data;
        } else {
            $values = $data;
        }

        $values['converter'] = $values['converter'] ?? $converter;
        $values['class'] = $values['class'] ?? $class;
        $values['options'] = $values['options'] ?? $options;
        $values['isOptional'] = $values['isOptional'] ?? $isOptional;

        parent::__construct($values);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setValue(string $name): void
    {
        $this->setName($name);
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): void
    {
        $this->class = $class;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function isOptional(): bool
    {
        return $this->isOptional;
    }

    public function setIsOptional(bool $isOptional): void
    {
        $this->isOptional = $isOptional;
    }

    public function getConverter(): ?string
    {
        return $this->converter;
    }

    public function setConverter(?string $converter): void
    {
        $this->converter = $converter;
    }
}
