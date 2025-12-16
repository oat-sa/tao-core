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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 */

/**
 * Utilities on XML
 *
 * Class tao_helpers_Xml
 */
class tao_helpers_Xml
{
    /**
     * Returns XML from the array
     * @param mixed
     * @return string xml
     *
     * phpcs:disable PSR1.Methods.CamelCapsMethodName
     */
    public static function from_array($obj = [])
    {
        $simpleElementXml = new SimpleXMLElement("<?xml version=\"1.0\"?><root></root>");
        self::array_to_xml($obj, $simpleElementXml);

        //for formatting ...
        $dom = dom_import_simplexml($simpleElementXml)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }
    // phpcs:enable PSR1.Methods.CamelCapsMethodName

    /**
     * Convert array to xml
     * @param array $data
     * @param $xml_data
     *
     * phpcs:disable PSR1.Methods.CamelCapsMethodName,PEAR.Functions.ValidDefaultValue
     */
    private static function array_to_xml($data = [], &$xml_data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value) or (is_object($value))) {
                if (!is_numeric($key)) {
                    $subnode = $xml_data->addChild("$key");
                    self::array_to_xml($value, $subnode);
                } else {
                    $subnode = $xml_data->addChild("element");
                    self::array_to_xml($value, $subnode);
                }
            } else {
                if (is_bool($value)) {
                    $value = $value ? "true" : "false";
                }
                $xml_data->addChild("$key", "$value");
            }
        }
    }
    // phpcs:enable PSR1.Methods.CamelCapsMethodName,PEAR.Functions.ValidDefaultValue

    /**
     * Convert xml to array
     * @param string $xml
     * @return mixed
     * @throws common_exception_Error
     *
     * phpcs:disable PSR1.Methods.CamelCapsMethodName
     */
    public static function to_array($xml)
    {
        $json = json_encode(self::getSimpleXml($xml));
        return json_decode($json, true);
    }
    // phpcs:enable PSR1.Methods.CamelCapsMethodName

    /**
     * @param $xml
     * @return SimpleXMLElement
     * @throws common_exception_Error
     */
    public static function getSimpleXml($xml)
    {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($xml ?? '');
        if ($xml === false) {
            $report = [];
            $errors = libxml_get_errors();
            /** @var LibXMLError $error */
            foreach ($errors as $error) {
                $report[] = trim($error->message) . ' [' . $error->line . ']';
            }
            libxml_clear_errors();
            throw new common_exception_Error(implode("\n", $report));
        }
        return $xml;
    }

    /**
     * Extract elements from the xml (xpath) with namespace dependency
     * @param $tagName
     * @param $xml
     * @param string $namespace
     * @return array
     * @throws common_exception_Error
     */
    public static function extractElements($tagName, $xml, $namespace = '')
    {
        $elements = [];
        $simpleXml = self::getSimpleXml($xml);
        $ns = '';
        if ($namespace) {
            $simpleXml->registerXPathNamespace('ns', $namespace);
            $ns = 'ns:';
        }
        $tagName = $ns . $tagName;
        foreach ($simpleXml->xpath('//' . $tagName) as $item) {
            $elements [] = current($item);
        }
        return $elements;
    }
}
