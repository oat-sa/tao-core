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
 * Copyright (c) 2018-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\user\implementation;

use oat\tao\model\user\UserSettings;
use DomainException;

class UserSettingsBuilder
{
    /** @var string|null */
    protected $dataLanguageCode = null;

    /** @var string|null */
    protected $uiLanguageCode = null;

    /** @var string */
    protected $timezone;

    public function __construct(string $defaultTimezone)
    {
        if (empty($defaultTimezone)) {
            throw new DomainException("Default Timezone is required");
        }

        $this->timezone = $defaultTimezone;
    }

    public function withDataLanguage(string $dataLanguage = null): self
    {
        $this->dataLanguageCode = trim($dataLanguage) ?? null;

        return $this;
    }

    public function withUILanguage(string $uiLanguage = null): self
    {
        $this->uiLanguageCode = trim($uiLanguage) ?? null;

        return $this;
    }

    public function withTimezone(string $timezone = null): self
    {
        $this->timezone = trim($timezone) ?? null;

        return $this;
    }

    public function build(): UserSettings
    {
        return new UserSettingsImpl(
            $this->timezone,
            $this->dataLanguageCode,
            $this->uiLanguageCode
        );
    }
}
