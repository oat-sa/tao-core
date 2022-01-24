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
 */

declare(strict_types=1);

namespace oat\tao\model\Language\Service;

use oat\tao\model\Lists\Business\Contract\ListElementSorterComparatorInterface;
use oat\tao\model\Lists\Business\Contract\ListElementSorterInterface;
use oat\tao\model\Lists\Business\Domain\Value;
use oat\tao\model\Lists\Business\Domain\ValueCollection;

class LanguageListElementSortService implements ListElementSorterInterface
{
    /** @var ListElementSorterComparatorInterface */
    private $comparator;

    public function __construct(ListElementSorterComparatorInterface $comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * @return Value[]
     */
    public function getSortedListCollectionValues(ValueCollection $valueCollection): array
    {
        $values = iterator_to_array($valueCollection);
        usort($values, $this->comparator);
        return $values;
    }
}
