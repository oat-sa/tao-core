<?php

/*
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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA
 */
declare(strict_types=1);

namespace oat\tao\scripts\tools\e2e\models;

use LogicException;
use RuntimeException;
use stdClass;

class E2eConfigDriver
{
    private $configPath;

    public function append(object $config): void
    {
        if (!is_writable(dirname($this->configPath))) {
            throw new LogicException(sprintf('"%s" is not writable', $this->configPath));
        }

        $originalConfig = [];
        if (is_readable($this->configPath)) {
            $originalConfig = json_decode(file_get_contents($this->configPath), true) ?? [];
        }

        $content = json_decode(json_encode($config), true) ?? [];

        if (false === file_put_contents(
                $this->configPath,
                json_encode(
                    array_merge($content, $originalConfig),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            )) {
            throw new RuntimeException('Impossible to create e2e env configuration file');
        }
    }

    public function setConfigPath(string $path): self
    {
        $this->configPath = $path;
        return $this;
    }

    public function read(): stdClass
    {
        if (!is_readable($this->configPath)) {
            throw new LogicException(sprintf('"%s" is not readable', $this->configPath));
        }

        return json_decode(file_get_contents($this->configPath));
    }
}
