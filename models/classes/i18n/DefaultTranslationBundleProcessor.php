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

use oat\oatbox\service\ConfigurableService;

final class DefaultTranslationBundleProcessor extends ConfigurableService implements TranslationBundleProcessorInterface
{
    public function process(array $translations, string $langCode): array
    {
        foreach ($translations as $msgid => $msgstr) {
            if (is_string($msgstr)) {
                $translations[$msgid] = self::convertRubyTags($msgstr);
            }
        }

        return $translations;
    }

    /**
     * Convert custom ruby wrappers to HTML ruby tags:
     * {ruby} -> <ruby>, {/ruby} -> </ruby>, {rt} -> <rt>, {/rt} -> </rt>,
     * {rp} -> <rp>, {/rp} -> </rp>, {rb} -> <rb>, {/rb} -> </rb>
     */
    public static function convertRubyTags(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        return str_replace(
            ['{ruby}', '{/ruby}', '{rt}', '{/rt}', '{rp}', '{/rp}', '{rb}', '{/rb}'],
            ['<ruby>', '</ruby>', '<rt>', '</rt>', '<rp>', '</rp>', '<rb>', '</rb>'],
            $text
        );
    }
}
