<?php
class tao_helpers_xml {
/**
* @param array
 * @return string xml
 */
    public static function from_array($arr, $xml = NULL)
    {
        $first = $xml;
        if($xml === NULL) $xml = new SimpleXMLElement('<root/>');
        foreach ($arr as $k => $v)
        {
            is_array($v)
                ? self::from_array($v, $xml->addChild(tao_helpers_Uri::encode($k)))
                : $xml->addChild(tao_helpers_Uri::encode($k), $v);
        }
        return ($first === NULL) ? $xml->asXML() : $xml;
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
        return json_decode($json,TRUE);
    }

}
?>