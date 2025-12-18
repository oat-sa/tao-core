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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * This script aims at generating client-side translation bundles while
 * TAO is not installed yet. It creates a translation bundle per locale
 * found accross the code base.
 *
 * An optional argument 'p' can be given to the script in order to indicate
 * the path to the root of your TAO platform. In case of 'p' is not given,
 * the current directory is used.
 *
 * An optional argument 'v' enables verbose mode.
 *
 * Example usage #1 (current directory is the root of the TAO platform):
 * cd /path/to/tao
 * sudo -u www-data php tao/scripts/tools/GenerateTranslationBundles.php -v
 *
 * Example usage #2 (current directory is NOT the root of the TAO platform):
 * sudo -u www-data php tao/scripts/tools/GenerateTranslationBundles.php -v -p /path/to/tao
 */
require(__DIR__ . '/../../../vendor/autoload.php');

$options = getopt('p:v');
$path = (!isset($options['p'])) ? realpath(getcwd()) : realpath($options['p']);
$verbose = isset($options['v']);
$extensions = [];
$locales = [];

// Check final destination is readable.
if (!is_writable("{$path}/tao/views/locales") || !is_dir("{$path}/tao/views/locales")) {
    fwrite(STDERR, "Final destination path 'tao/views/locales' is not writable.\n");
}

// Scan extensions to find out all locales.
foreach (array_filter(glob("{$path}/*"), 'is_dir') as $dir) {
    if (is_readable("{$dir}/manifest.php")) {
        // This is an extension.
        $extensionId = basename($dir);
        $extensions[] = $extensionId;

        if (is_readable("{$dir}/locales") && is_dir("{$dir}/locales")) {
            // We have a locales directory. Let's retrieve locales we don't know yet.
            foreach (array_filter(glob("{$dir}/locales/*"), 'is_dir') as $localeDir) {
                $locale = basename($localeDir);
                $newLocale = !in_array($locale, $locales);

                if ($newLocale) {
                    $locales[] = $locale;

                    if ($verbose) {
                        fwrite(STDOUT, "Locale '{$locale}' found.\n");
                    }
                }
            }
        } elseif ($verbose) {
            fwrite(STDOUT, "Extension '{$extensionId}' has no locales.\n");
        }
    }
}

// Generate Translation Bundles.
$count = 0;
foreach ($locales as $locale) {
    $translationBundle = new oat\tao\helpers\translation\TranslationBundle($locale, $extensions, $path);
    $bundleFile = $translationBundle->generateTo("{$path}/tao/views/locales");

    if ($bundleFile !== false) {
        if ($verbose) {
            fwrite(STDOUT, "Translation Bundle for locale '{$locale}' written to '{$bundleFile}'.\n");
        }

        $count++;
    } else {
        fwrite(STDERR, "Translation Bundle for locale '{$locale}' could not be created.\n");
    }
}

if ($verbose) {
    fwrite(STDOUT, "{$count} Translation Bundles created.\n");
}
