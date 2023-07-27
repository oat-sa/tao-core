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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\tao\model\configurationMarkers\Secrets;

/**
 * Replace configuration variable with environment variable
 */
class SerializableSecretDtoExtended
{
    private array $envIndexes;
    private string $originalString;
    private const MARKER_PATTERN = '/\$ENV{([a-zA-Z0-9\-\_]+)}/';

    public function __construct(array $envIndexes, string $originalString)
    {
        $this->envIndexes = $envIndexes;
        $this->originalString = $originalString;
    }

    private function markersReorganisationToPhp(array $markers)
    {
        return array_map("self::toPhpCode", $markers);
    }

    /**
     * @return string
     */
    private function toPhpCode(string $marker): string
    {
        return "\$_ENV['$marker']";
    }

    private function markersReorganisationToString(array $markers)
    {
        return array_map("self::toString", $markers);
    }

    /**
     * @return string
     */
    private function toString(string $marker): string
    {
        return $_ENV[$marker] ?? '';
    }


    /**
     * @return string
     */
    public function __toPhpCode(): string
    {
        return vsprintf(
            preg_replace(self::MARKER_PATTERN, '%s', $this->originalString),
            $this->markersReorganisationToPhp($this->envIndexes)
        );
    }

    public function __toPhpSprintfCode(): string
    {
        return '"' .
            preg_replace(self::MARKER_PATTERN, '%s', $this->originalString)
            . '", ' .
            implode(', ', $this->markersReorganisationToPhp($this->envIndexes)
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return vsprintf(
            preg_replace(self::MARKER_PATTERN, '%s', $this->originalString),
            $this->markersReorganisationToString($this->envIndexes)
        );
    }
}
