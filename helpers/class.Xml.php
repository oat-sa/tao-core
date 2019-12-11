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
     * @param mixed
     * @return string xml
     */
    public static function from_array($obj = array())
    {
        $simpleElementXml = new SimpleXMLElement("<?xml version=\"1.0\"?><root></root>");
        self::array_to_xml($obj, $simpleElementXml);

        //for formatting ...
        $dom = dom_import_simplexml($simpleElementXml)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    // function defination to convert array to xml
    private static function array_to_xml($data = array(), &$xml_data)
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

    /**
     *
     * @param string $xml
     * @return array
     */
    public static function to_array($xml)
    {
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);

        return json_decode($json, true);
    }

}
