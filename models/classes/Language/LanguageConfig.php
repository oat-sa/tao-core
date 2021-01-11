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

namespace oat\tao\model\Language;

class LanguageConfig
{
    public const SERVICE_ID = 'tao/LanguageConfig';
    public const OPTION_QTI_LANGUAGE = 'qtiLanguage';

    /** @var string */
    private $interfaceLanguageCode;

    /** @var string */
    private $interfaceLanguage;

    /** @var string */
    private $qtiLanguage;

    public function __construct(string $interfaceLanguageCode, string $interfaceLanguage, string $qtiLanguage)
    {
        $this->interfaceLanguageCode = $interfaceLanguageCode;
        $this->interfaceLanguage = $interfaceLanguage;
        $this->qtiLanguage = $qtiLanguage;
    }

    public function getInterfaceLanguageCode(): string
    {
        return $this->interfaceLanguageCode;
    }

    public function getInterfaceLanguage(): string
    {
        return $this->interfaceLanguage;
    }

    public function getQtiLanguage(): string
    {
        return $this->qtiLanguage;
    }
}
