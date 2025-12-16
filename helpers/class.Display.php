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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung
 *                         (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor
 *                         (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2017 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

use oat\oatbox\service\ServiceManager;
use oat\tao\model\service\ApplicationService;

/**
 * Utility class focusing on display methods.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao

 */
class tao_helpers_Display
{
    private static $replacement = '_';

    /**
     * Enables you to cut a long string and end it with [...] and add an hover
     * to display the complete string on mouse over.
     *
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string input The string input.
     * @param  int maxLength (optional, default = 75) The maximum length for the result string.
     * @return string The cut string, enclosed in a <span> html tag. This tag received the 'cutted' CSS class.
     */
    public static function textCutter($input, int $maxLength = 75): string
    {
        $encoding = self::getEncoding();

        if (mb_strlen($input, $encoding) <= $maxLength) {
            return $input;
        }

        return sprintf(
            '<span title="%s" class="cutted" style="cursor:pointer;">%s[...]</span>',
            $input,
            mb_substr($input, 0, $maxLength, $encoding)
        );
    }

    /**
     * Clean a text with a joker character to replace any characters that is not alphanumeric.
     *
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string input The string input.
     * @param  string joker (optional, default = '_') A joker character that will be the alphanumeric placeholder.
     * @param  int maxLength (optional, default = -1) output maximum length
     * @return string The result string.
     */
    public static function textCleaner($input, string $joker = '_', int $maxLength = -1): string
    {
        $encoding = self::getEncoding();

        if ($maxLength > -1) {
            $input = mb_substr($input, 0, $maxLength, $encoding);
        }

        $replacingPattern = strpos($encoding, 'UTF') === 0
            ? '/[^\p{L}0-9-_]+/u'
            : '/[^a-z0-9-_]+/ui';

        $patternMaps = [
            '/\s+/u'          => [self::class, 'replaceWithUnderscore'],
            $replacingPattern => [self::class, 'replace'],
        ];

        self::$replacement = $joker;

        return preg_replace_callback_array($patternMaps, $input);
    }

    /**
     * Display clean and more secure text into an HTML page. The implementation
     * of this method is done with htmlentities.This method is Unicode safe.
     *
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string input The input string.
     * @return string The htmlized string.
     */
    public static function htmlize($input): string
    {
        return htmlentities($input ?? '', ENT_COMPAT, self::getEncoding());
    }



    /**
     *  Convert special characters to HTML entities
     */
    public static function htmlEscape($string): string
    {
        return htmlspecialchars($string);
    }

    /**
     * Encode the value of an html attribute
     *
     * @param string $string
     * @return string
     */
    public static function encodeAttrValue($string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize all parts that could be used in XSS (script, style, hijacked links, etc.)
     * and update some attributes to use a safe behavior.
     *
     * @see http://htmlpurifier.org/live/configdoc/plain.html
     *
     * @param string $input the input HTML
     * @return string the sanitized HTML
     */
    public static function sanitizeXssHtml($input): string
    {
        $config = HTMLPurifier_Config::createDefault();

        //we don't use HTMLPurifier cache
        //because it writes serialized files inside it's own folder
        //so this won't work in a multi server env
        $config->set('Cache.DefinitionImpl', null);

        //allow target=_blank on links
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        $purifier = new HTMLPurifier($config);
        return  $purifier->purify($input);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function replace(array $matches): string
    {
        return '*' === self::$replacement
            ? self::replaceWithRandom($matches)
            : self::replaceWith($matches, self::$replacement);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function replaceWithUnderscore(array $matches): string
    {
        return self::replaceWith($matches, '_');
    }

    private static function replaceWithRandom(array $matches): string
    {
        $result = [];

        $length = mb_strlen(reset($matches), self::getEncoding());

        for ($i = 0; $i < $length; $i++) {
            /** @noinspection RandomApiMigrationInspection */
            $result[] = chr(mt_rand(97, 122));
        }

        return implode('', $result);
    }

    private static function replaceWith(array $matches, string $replacement): string
    {
        return str_repeat($replacement, mb_strlen(reset($matches), self::getEncoding()));
    }

    private static function getEncoding(): string
    {
        static $encoding;

        if (null === $encoding) {
            try {
                $encoding = self::getApplicationService()->getDefaultEncoding();
            } catch (common_Exception $exception) {
                $encoding = mb_internal_encoding();
            }
        }

        return $encoding;
    }

    private static function getApplicationService(): ApplicationService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceManager::getServiceManager()->get(ApplicationService::SERVICE_ID);
    }
}
