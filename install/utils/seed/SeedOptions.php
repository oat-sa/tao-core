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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */
namespace oat\tao\install\utils\seed;

class SeedOptions
{
    private $rootUrl;
    private $localNamespace;
    private $options;

    const OPTION_LANGUAGE = 'lang';
    const OPTION_TIMEZONE = 'tz';
    const OPTION_INSTANCE_NAME = 'name';
    const OPTION_DEBUG = 'debug';
    const OPTION_INSTALL_SAMPLES = 'samples';

    public function __construct($rootUrl, $localNamespace, $options = [])
    {
        $this->rootUrl = $rootUrl;
        $this->localNamespace = $localNamespace;
        $this->options = $options;
    }

    public function getRootUrl(): string
    {
        return $this->rootUrl;
    }

    public function getLocalNamespace(): string
    {
        return $this->localNamespace;
    }

    public function getDefaultLanguage(): string
    {
        return isset($this->options[self::OPTION_LANGUAGE])
            ? $this->options[self::OPTION_LANGUAGE]
            : 'en-US';
    }

    public function getDefaultTimezone(): string
    {
        return isset($this->options[self::OPTION_TIMEZONE])
            ? $this->options[self::OPTION_TIMEZONE]
            : date_default_timezone_get();
    }

    public function getInstanceName(): string
    {
        return isset($this->options[self::OPTION_INSTANCE_NAME])
        ? $this->options[self::OPTION_INSTANCE_NAME]
        : null;
    }

    public function useDebugMode(): bool
    {
        return isset($this->options[self::OPTION_DEBUG])
        ? $this->options[self::OPTION_DEBUG]
        : true;
    }

    public function installSamples(): bool
    {
        return isset($this->options[self::OPTION_INSTALL_SAMPLES])
        ? $this->options[self::OPTION_INSTALL_SAMPLES]
        : true;
    }
}
