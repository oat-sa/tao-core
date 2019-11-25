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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 */

/**
 * Aims at providing common utility methods for the tao::helpers::translation
 *
 * @access public
 * @author Jerome Bogaerts
 * @package tao
 * @since 2.2

 * @version 1.0
 */
class tao_helpers_translation_Utils
{
    /**
     * Returns the default language of TAO.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
    public static function getDefaultLanguage()
    {
        $returnValue = (string) '';

        
        $returnValue = 'en-US';
        

        return (string) $returnValue;
    }

    /**
     * Get the availables locales for the current installation (from installed extensions).
     * @return string[] the list of all locales supported by the app (even though a locale is used in only one extension).
     */
    public static function getAvailableLanguages()
    {
        $languages = array();
        foreach (common_ext_ExtensionsManager::singleton()->getInstalledExtensions() as $extension) {
            $localesDir = $extension->getDir() . 'locales/';
            foreach (glob($localesDir . '*') as  $file) {
                if (is_dir($file) && file_exists($file . '/messages.po')) {
                    $lang = basename($file);
                    if (!in_array($lang, $languages)) {
                        $languages[] = $lang;
                    }
                }
            }
        }
        return $languages;
    }
}
