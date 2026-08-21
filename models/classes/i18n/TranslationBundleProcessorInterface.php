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
 * Copyright (c) 2026 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\model\i18n;

/**
 * Post-processes merged server translation bundles (msgid => msgstr) before they are cached.
 * Register a custom implementation at SERVICE_ID to replace the default.
 */
interface TranslationBundleProcessorInterface
{
    public const SERVICE_ID = 'tao/TranslationBundleProcessor';

    /**
     * @param array<string, string> $translations merged PO entries for one locale
     * @return array<string, string>
     */
    public function process(array $translations, string $langCode): array;
}
