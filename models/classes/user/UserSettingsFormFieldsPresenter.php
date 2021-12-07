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

namespace oat\tao\model\user;

use oat\generis\model\GenerisRdf;

class UserSettingsFormFieldsPresenter
{
    /** @var UserSettings */
    private $userSettings;

    public function __construct(UserSettings $userSettings)
    {
        $this->userSettings = $userSettings;
    }

    /**
     * Get the settings for a given user.
     *
     * This method returns a Resource array with the following keys:
     *
     * - ui_lang:   Language selected for the Graphical User Interface.
     * - data_lang: Language selected to access the data in persistent memory.
     * - timezone:  Timezone selected to display times and dates.
     *
     * @return string[] The URIs of the languages.
     */
    public function getFormFields(): array
    {
        $fields = [
            'timezone' => $this->userSettings->getTimezone(),
        ];

        if (!empty($this->userSettings->getUILanguageCode())) {
            $fields['ui_lang'] = $this->userSettings->getUILanguageCode();
        }

        if (!empty($props[GenerisRdf::PROPERTY_USER_DEFLG])) {
            $fields['data_lang'] = $this->userSettings->getDataLanguageCode();
        }

        return $fields;
    }
}
