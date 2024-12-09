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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA;
 *
 * @author Gabriel Felipe Soares <gabriel.felipe.soares@taotesting.com>
 */

declare(strict_types=1);

namespace oat\tao\model\Language\Filter;

use oat\tao\model\Language\Language;
use oat\tao\model\Language\LanguageCollection;
use oat\tao\model\Lists\Business\Domain\ValueCollection;
use oat\tao\model\TaoOntology;

class LanguageAllowedFilter
{
    public const TAO_ALLOWED_TRANSLATION_LOCALES = 'TAO_ALLOWED_TRANSLATION_LOCALES';

    /** @var string[] */
    private array $allowedLocales;

    public function __construct(?string $allowedLocales = null)
    {
        $this->allowedLocales = array_filter(
            explode(
                ',',
                str_replace(
                    ' ',
                    '',
                    (string)$allowedLocales
                )
            )
        );
    }

    public function filterByLanguageCollection(LanguageCollection $collection): LanguageCollection
    {
        if (empty($this->allowedLocales)) {
            return $collection;
        }

        $indicesToUnset = [];

        /** @var Language $item */
        foreach ($collection as $key => $item) {
            if (!in_array($item->getCode(), $this->allowedLocales, true)) {
                $indicesToUnset[] = $key;
            }
        }

        foreach ($indicesToUnset as $index) {
            $collection->offsetUnset($index);
        }

        return $collection;
    }

    public function filterByValueCollection(ValueCollection $collection): ValueCollection
    {
        if (empty($this->allowedLocales)) {
            return $collection;
        }

        $allowedUris = [];

        foreach ($this->allowedLocales as $locale) {
            $allowedUris[] = sprintf('%s%s', TaoOntology::LANGUAGE_PREFIX, $locale);
        }

        foreach ($collection->getUris() as $uri) {
            if (!in_array($uri, $allowedUris, true)) {
                $collection->removeValueByUri($uri);
            }
        }

        return $collection;
    }
}
