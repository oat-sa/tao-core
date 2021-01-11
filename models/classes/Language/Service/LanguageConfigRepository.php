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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\Language\Service;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\session\SessionService;
use oat\tao\model\Language\LanguageConfig;

class LanguageConfigRepository extends ConfigurableService
{
    public const SERVICE_ID = 'tao/LanguageConfigRepository';
    public const OPTION_QTI_LANGUAGE = 'qtiLanguage';

    public function findActive(): LanguageConfig
    {
        $languageCode = $this->getSessionService()
            ->getCurrentSession()
            ->getInterfaceLanguage();

        $language = $this->getLanguage($languageCode);

        return new LanguageConfig(
            $languageCode,
            $language,
            $this->getOption(self::OPTION_QTI_LANGUAGE, $language)
        );
    }

    private function getLanguage(string $langCode)
    {
        if (strpos($langCode, '-') > 0) {
            return strtolower(substr($langCode, 0, strpos($langCode, '-')));
        }

        return strtolower($langCode);
    }

    private function getSessionService(): SessionService
    {
        return $this->getServiceLocator()->get(SessionService::SERVICE_ID);
    }
}
