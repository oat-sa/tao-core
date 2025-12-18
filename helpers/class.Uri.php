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
 *
 */

/**
 * Utilities on URL/URI
 *
 * @access public
 * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
 * @package tao
 */
class tao_helpers_Uri
{
    /**
     * the base url
     *
     * @access private
     * @var string
     */
    private static $base = '';

    /**
     * Short description of attribute root
     *
     * @access private
     * @var string
     */
    private static $root = '';

    /**
     * Short description of attribute ENCODE_ARRAY_KEYS
     *
     * @access public
     * @var int
     */
    public const ENCODE_ARRAY_KEYS = 1;

    /**
     * Short description of attribute ENCODE_ARRAY_VALUES
     *
     * @access public
     * @var int
     */
    public const ENCODE_ARRAY_VALUES = 2;

    /**
     * Short description of attribute ENCODE_ARRAY_ALL
     *
     * @access public
     * @var int
     */
    public const ENCODE_ARRAY_ALL = 3;

    /**
     * get the project base url
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
    public static function getBaseUrl()
    {
        if (empty(self::$base) && defined('BASE_URL')) {
            self::$base = BASE_URL;
            if (!preg_match("/\/$/", self::$base)) {
                self::$base .= '/';
            }
        }

        return self::$base;
    }

    /**
     * Short description of method getRootUrl
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @return string
     */
    public static function getRootUrl()
    {
        if (empty(self::$root) && defined('ROOT_URL')) {
            self::$root = ROOT_URL;
            if (!preg_match("/\/$/", self::$root)) {
                self::$root .= '/';
            }
        }

        return self::$root;
    }

    /**
     * conveniance method to create urls based on the current MVC context and
     * it for the used kind of url resolving
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string action
     * @param  string module
     * @param  string extension
     * @param  array|string params
     * @return string
     */
    public static function url($action = null, $module = null, $extension = null, $params = [])
    {

        if (is_null($module)) {
            $module = Context::getInstance()->getModuleName();
        }
        if (is_null($action)) {
            $action = Context::getInstance()->getActionName();
        }
        if (is_null($extension)) {
            $extension = Context::getInstance()->getExtensionName();
        }

        $returnValue = self::getRootUrl() . $extension . '/' . $module . '/' . $action;

        if (is_string($params) && strlen($params) > 0) {
            $returnValue .= '?' . $params;
        }

        if (is_array($params) && count($params)) {
            $returnValue .= '?';
            foreach ($params as $key => $value) {
                if ($value !== null) {
                    $returnValue .= $key . '=' . rawurlencode($value) . '&';
                }
            }
            $returnValue = substr($returnValue, 0, -1);
        }

        return $returnValue;
    }

    /**
     * format propertly an ol style url
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string url
     * @param  array params
     * @return string
     */
    public static function legacyUrl($url, $params = [])
    {
        return '';
    }

    /**
     * encode an URI
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string uri
     * @param  boolean dotMode
     * @return string
     */
    public static function encode($uri, $dotMode = true)
    {
        if (0 === strpos($uri ?? '', 'http')) {
            //return base64_encode($uri);
            if ($dotMode) {
                //order matters here don't change the _4_ position
                $returnValue = str_replace(['#', '://', '/', '.', ':'], ['_3_', '_2_', '_1_', '_0_', '_4_'], $uri);
            } else {
                $returnValue = str_replace(['#', '://', '/', ':'], ['_3_', '_2_', '_1_', '_4_'], $uri);
            }
        } else {
            $returnValue = $uri;
        }

        return $returnValue;
    }

    /**
     * encode a relative URL
     *
     * @access public
     * @param  string uri
     * @param  boolean dotMode
     * @return string
     */
    public static function encodeRelativeUrl($url)
    {
        $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
        $parts = explode('/', $url);
        foreach (array_keys($parts) as $key) {
            $parts[$key] = rawurlencode($parts[$key]);
        }
        return implode('/', $parts);
    }

    /**
     * decode an URI
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string $uri
     * @param  boolean $dotMode
     * @return string
     */
    public static function decode($uri, $dotMode = true)
    {
        if (0 === strpos($uri, 'http')) {
            //return base64_decode($uri);
            if ($dotMode) {
                //$returnValue = urldecode(str_replace('__', '.', $uri));
                //$returnValue = str_replace('w_org', 'w3.org', $returnValue);
                $returnValue = str_replace(['_0_', '_1_', '_2_', '_3_', '_4_'], ['.', '/', '://', '#', ':'], $uri);
            } else {
                $returnValue = str_replace(['_1_', '_2_', '_3_', '_4_'], ['/', '://', '#', ':'], $uri);
            }
        } else {
            $returnValue = $uri;
        }


        return (string)$returnValue;
    }

    /**
     * @param string $decodedUri
     *
     * @return bool
     */
    public static function isUriEncoded($decodedUri)
    {
        return preg_match('/^[a-z]*_2_/', $decodedUri) === 1;
    }

    /**
     * Encode the uris composing either the keys or the values of the array in
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  array uris
     * @param  int encodeMode
     * @param  boolean dotMode
     * @param  boolean uniqueMode
     * @return array
     */
    public static function encodeArray(
        $uris,
        $encodeMode = self::ENCODE_ARRAY_ALL,
        $dotMode = true,
        $uniqueMode = false
    ) {
        $returnValue = [];

        if (is_array($uris)) {
            foreach ($uris as $key => $value) {
                if ($encodeMode == self::ENCODE_ARRAY_KEYS || $encodeMode == self::ENCODE_ARRAY_ALL) {
                    $key = self::encode($key, $dotMode);
                }
                if ($encodeMode == self::ENCODE_ARRAY_VALUES || $encodeMode == self::ENCODE_ARRAY_ALL) {
                    $value = self::encode($value, $dotMode);
                }
                $returnValue[$key] = $value;
            }
        }

        if ($uniqueMode) {
            $returnValue = array_unique($returnValue);
        }

        return $returnValue;
    }

    /**
     * generate a semi unique id, that is used
     * in folder names.
     * @todo Remove the need for this function
     *
     * @access public
     * @author sam@taotesting.com
     * @param  string uriResource
     * @return string
     */
    public static function getUniqueId($uriResource)
    {
        $returnValue = '';

        if (stripos($uriResource, "#") > 0) {
            $returnValue = substr($uriResource, stripos($uriResource, "#") + 1);
        }

        return $returnValue;
    }

    /**
     * Returns the path from a URI. In other words, it returns what comes after
     * domain but before the query string. If
     * is given as a parameter value, '/path/to/something' is returned. If an
     * occurs, null will be returned.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string uri A Uniform Resource Identifier (URI).
     * @return string|null
     */
    public static function getPath($uri)
    {
        if (preg_match("/^[A-Za-z0-9]*$/", $uri)) {
            // no '.', no '/', ... does not look good.
            return null;
        }

        $returnValue = parse_url($uri, PHP_URL_PATH);
        if (empty($returnValue)) {
            return null;
        }

        return $returnValue;
    }

    /**
     * Returns the domain extracted from a given URI. If the domain cannot be
     * null is returned.
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string uri A Uniform Resource Identifier (URI).
     * @return string|null
     */
    public static function getDomain($uri)
    {
        $returnValue = parse_url($uri, PHP_URL_HOST);
        if (empty($returnValue)) {
            return null;
        }

        return $returnValue;
    }

    /**
     * To be used to know if a given URI is valid as a cookie domain. Usually,
     * domain such as 'mytaoplatform', 'localhost' make issues with
     *
     * @access public
     * @author Jerome Bogaerts, <jerome.bogaerts@tudor.lu>
     * @param  string uri
     * @return boolean
     */
    public static function isValidAsCookieDomain($uri)
    {
        $domain = self::getDomain($uri);
        if (!empty($domain)) {
            if (preg_match("/^[a-z0-9\-]+(?:[a-z0-9\-]\.)+/iu", $domain) > 0) {
                $returnValue = true;
            } else {
                $returnValue = false;
            }
        } else {
            $returnValue = false;
        }

        return $returnValue;
    }
}
