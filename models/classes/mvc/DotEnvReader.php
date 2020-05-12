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
 * Copyright (c) 2019 Open Assessment Technologies SA;
 */

namespace oat\tao\model\mvc;

use Symfony\Component\Dotenv\Dotenv;

/**
 * This class loads the project's default .env file into $_ENV.
 */
class DotEnvReader
{
    /**
     * Reads .env file into $_ENV.
     *
     * @param string $envFile (defaults to project .env file)
     */
    public function __construct($envFile = '')
    {
        if ($envFile === '') {
            $envFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env';
        }
        if (file_exists($envFile)) {
            $dotEnv = new Dotenv();
            $dotEnv->overload($envFile);
        }
    }
}
