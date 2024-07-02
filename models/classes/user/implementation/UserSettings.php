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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\model\user\implementation;

use oat\tao\model\user\UserSettingsInterface;

class UserSettings implements UserSettingsInterface
{
    /** @var array */
    private $settings = [];

    public function __construct(string $timezone, string $uiLanguageCode = null, string $dataLanguageCode = null)
    {
        $this->setSetting(self::TIMEZONE, $timezone);
        $this->setSetting(self::UI_LANGUAGE_CODE, $uiLanguageCode);
        $this->setSetting(self::DATA_LANGUAGE_CODE, $dataLanguageCode);
    }

    public function getUILanguageCode(): ?string
    {
        return $this->getSetting(self::UI_LANGUAGE_CODE);
    }

    public function getDataLanguageCode(): ?string
    {
        return $this->getSetting(self::DATA_LANGUAGE_CODE);
    }

    public function getTimezone(): string
    {
        return $this->getSetting(self::TIMEZONE);
    }

    public function setSetting(string $setting, $value): UserSettingsInterface
    {
        $this->settings[$setting] = $value;

        return $this;
    }

    public function getSetting(string $setting)
    {
        return $this->settings[$setting] ?? null;
    }
}
