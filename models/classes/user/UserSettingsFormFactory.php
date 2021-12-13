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

namespace oat\tao\model\user;

use tao_helpers_form_FormContainer;
use tao_actions_form_UserSettings;
use tao_helpers_form_Form;

class UserSettingsFormFactory
{
    /** @var UserSettingsInterface */
    private $userSettings;

    public function __construct(UserSettingsInterface $userSettings)
    {
        $this->userSettings = $userSettings;
    }

    public function getForm(bool $csrf = true): tao_helpers_form_Form
    {
        $fields = [
            'timezone' => $this->userSettings->getTimezone(),
        ];

        if (!empty($this->userSettings->getUILanguageCode())) {
            $fields['ui_lang'] = $this->userSettings->getUILanguageCode();
        }
        if (!empty($this->userSettings->getDataLanguageCode())) {
            $fields['data_lang'] = $this->userSettings->getDataLanguageCode();
        }

        $formBuilder = new tao_actions_form_UserSettings(
            $fields,
            [
                tao_helpers_form_FormContainer::CSRF_PROTECTION_OPTION => $csrf
            ]
        );

        return $formBuilder->getForm();
    }
}
