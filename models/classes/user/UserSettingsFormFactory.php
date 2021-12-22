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

use common_Exception;
use tao_actions_form_UserSettings;
use tao_helpers_form_Form;
use tao_helpers_form_FormContainer;
use tao_models_classes_LanguageService;

class UserSettingsFormFactory
{
    public const PARAM_USE_CSRF_PROTECTION = 'useCSRFProtection';

    /** @var tao_models_classes_LanguageService */
    private $languageService;

    public function __construct(tao_models_classes_LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * @throws common_Exception
     */
    public function create(
        UserSettingsInterface $userSettings,
        string $uiLanguage = null,
        array $params = []
    ): tao_helpers_form_Form {
        $fields = [
            'timezone' => trim($userSettings->getTimezone()),
            'ui_lang' => $uiLanguage,
        ];

        if (!empty($userSettings->getUILanguageCode())) {
            $fields['ui_lang'] = $userSettings->getUILanguageCode();
        }

        if (!empty($userSettings->getDataLanguageCode())) {
            $fields['data_lang'] = $userSettings->getDataLanguageCode();
        }

        $options = [
            tao_actions_form_UserSettings::OPTION_LANGUAGE_SERVICE => $this->languageService,
            tao_helpers_form_FormContainer::CSRF_PROTECTION_OPTION => (bool) ($params[self::PARAM_USE_CSRF_PROTECTION] ?? true),
        ];

        $formBuilder = new tao_actions_form_UserSettings($fields, $options);

        return $formBuilder->getForm();
    }
}
