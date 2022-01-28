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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\Csv\Factory;

use Throwable;
use InvalidArgumentException;
use oat\tao\model\Csv\Service\Reader;
use League\Csv\Reader as LeagueReader;

class ReaderFactory
{
    public const DELIMITER = 'delimiter';

    /**
     * @param resource $stream
     */
    public function createFromStream($stream, array $options = []): Reader
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('The provided value must be a resource.');
        }

        $reader = LeagueReader::createFromStream($stream);

        try {
            $reader->setHeaderOffset(0);

            if (!empty($options[self::DELIMITER]) && is_string($options[self::DELIMITER])) {
                $reader->setDelimiter($options[self::DELIMITER]);
            }
        } catch (Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }

        return new Reader($reader);
    }
}
