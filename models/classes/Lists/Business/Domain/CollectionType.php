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

use InvalidArgumentException;
use core_kernel_classes_Class as KernelClass;

final class CollectionType
{
    public const TYPE_PROPERTY = 'http://www.tao.lu/Ontologies/TAO.rdf#ListType';

    private const TYPE_DEFAULT = '';
    private const TYPE_REMOTE  = 'http://www.tao.lu/Ontologies/TAO.rdf#ListRemote';

    private const TYPES = [
        self::TYPE_DEFAULT,
        self::TYPE_REMOTE,
    ];

    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function default(): self
    {
        return new self(self::TYPE_DEFAULT);
    }

    public static function remote(): self
    {
        return new self(self::TYPE_REMOTE);
    }

    public static function fromValue(string $value): self
    {
        if (!in_array($value, self::TYPES, true)) {
            throw new InvalidArgumentException("\"$value\" is not a valid Collection type.");
        }

        return new self($value);
    }

    public static function fromCollectionUri(string $uri): self
    {
        $collectionClass = new KernelClass($uri);

        $value = $collectionClass->getOnePropertyValue(
            $collectionClass->getProperty(self::TYPE_PROPERTY)
        );

        return null === $value ? self::default() : self::fromValue($value->getUri());
    }

    public function equals(self $collectionType): bool
    {
        return $this->value === $collectionType->value;
    }

    public function __toString()
    {
        return $this->value;
    }
}
