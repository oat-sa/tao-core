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

namespace oat\tao\model\session\Business\Domain;

use IteratorAggregate;
use oat\tao\model\session\Business\Contract\SessionCookieAttributeInterface;

final class SessionCookieAttributeCollection implements IteratorAggregate
{
    /** @var SessionCookieAttributeInterface[] */
    private $attributes = [];

    public function add(SessionCookieAttributeInterface $attribute): self
    {
        $collection = clone $this;

        $collection->attributes[] = $attribute;

        return $collection;
    }
    
    /**
     * getCookieParams
     * convert collection attributes to cookie options array
     *
     * @param  array $params predefined cookie params array from caller code
     * @param  bool $replace if true it will replace values from collection
     * @return array
     */
    public function getCookieParams(array $params, bool $replace): array
    {
        $retVal = $params;
        foreach ($this as $attribute) {
            switch ($attribute->name()) {
                //this is not exists in params array always need to be set not
                //dependent from replace flag
                case 'path':
                     $retVal[$attribute->name()] = $attribute->value();
                     break;
                case 'lifetime':
                case 'domain':
                case 'secure':
                case 'httponly':
                    if ($replace) {
                        $retVal[$attribute->name()] = $attribute->value();
                    }
                    break;
                default:
                    $retVal[$attribute->name()]= $attribute->value();
                    break;
            }
        }
        return $retVal;
    }

    /**
     * @return iterable|SessionCookieAttributeInterface[]
     */
    public function getIterator(): iterable
    {
        yield from $this->attributes;
    }

    public function __toString(): string
    {
        $rawAttributes = [];

        foreach ($this as $attribute) {
            $rawAttributes[] = (string)$attribute;
        }

        return implode('; ', $rawAttributes);
    }
}
