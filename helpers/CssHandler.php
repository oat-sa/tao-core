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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\tao\helpers;

class CssHandler
{
    /**
     * Convert incoming CSS to CSS array
     * This CSS must have the format generated by the online editor
     *
     * @param $css
     * @return mixed
     */
    public static function cssToArray($css)
    {
        if (!$css) {
            return [];
        }
        $css = str_replace(' /* Do not edit */', '', $css);
        $oldCssArr = explode("\n", $css);
        $newCssArr = [];
        foreach ($oldCssArr as $line) {
            if (false === strpos($line, '{')) {
                continue;
            }

            preg_match('~(?P<selector>[^{]+)(\{)(?P<rules>[^}]+)\}~', $line, $matches);

            foreach ($matches as $key => &$match) {
                if (is_numeric($key)) {
                    continue;
                }
                $match = trim($match);
                if ($key === 'rules') {
                    $ruleSet = array_filter(array_map('trim', explode(';', $match)));
                    $match = [];
                    foreach ($ruleSet as $rule) {
                        $rule = array_map('trim', explode(':', $rule));
                        $match[$rule[0]] = $rule[1];
                    }
                }
            }

            $newCssArr[$matches['selector']] = $matches['rules'];
        }
        return $newCssArr;
    }

    /**
     * Convert incoming CSS array to proper CSS
     *
     * @param $array
     * @param $compressed boolean add break lines or not
     * @return string
     */
    public static function arrayToCss($array, $compressed = true)
    {
        $css = '';

        $break = '';
        $space = '';
        if (!$compressed) {
            $break = "\n\t";
            $space = " ";
        }
        // rebuild CSS
        foreach ($array as $key1 => $value1) {
            $css .= $key1 . $space . '{';

            foreach ($value1 as $key2 => $value2) {
                // in the case that the code is embedded in a media query
                if (is_array($value2)) {
                    foreach ($value2 as $value3) {
                        $css .= $break . $key2 . $space . '{';
                        foreach ($value3 as $mProp) {
                            $css .= $break . $mProp . ':' . $value3 . ';';
                        }
                        $css .= ($compressed) ? '}' : "\n}\n";
                    }
                } else { // regular selectors
                    $css .= $break . $key2 . ':' . $value2 . ';';
                }
            }
            $css .= ($compressed) ? '}' : "\n}\n";
        }
        return $css;
    }
}
