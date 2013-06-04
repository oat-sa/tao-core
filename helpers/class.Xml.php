<?php
class tao_helpers_Xml {

    /**
* @param mixed
 * @return string xml
 */
    public static function from_array($obj)
    {	   
	   
	    $simpleElementXml = new SimpleXMLElement("<?xml version=\"1.0\"?><root></root>");
	    self::array_to_xml($obj,$simpleElementXml);
	    
	    //for formatting ...
	    $dom = dom_import_simplexml($simpleElementXml)->ownerDocument;
	    $dom->formatOutput = true;
	    return $dom->saveXML();
	
    }

    // function defination to convert array to xml
    private static function array_to_xml($student_info, &$xml_student_info) {
	foreach($student_info as $key => $value) {
	    if(is_array($value) or (get_class($value)=="stdClass")) {
		if(!is_numeric($key)){
		    $subnode = $xml_student_info->addChild("$key");
		    self::array_to_xml($value, $subnode);
		}
		else{
		    $subnode = $xml_student_info->addChild("element");
		    self::array_to_xml($value, $subnode);
		}
	    }
	    else {
		$xml_student_info->addChild("$key","$value");
	    }
	}
    }

    /*
     * $str = "";
	if(is_null($obj)) return "<null/>";
	elseif(is_array($obj) or is_object($obj)) {

	  //a list is a hash with 'simple' incremental keys
	  $is_list = array_keys($obj) == array_keys(array_values($obj));
	  if(!$is_list) {
	    $str.= "<hash>";
	    foreach($obj as $k=>$v)
		$str.="<item key=\"$k\">".self::from_array($v)."</item>".PHP_EOL;
		$str .= "</hash>";
	  }
	  else {
	    $str.= "<list>".PHP_EOL;
	    foreach($obj as $key=>$v)
	      $str.="\t<".$key.">".self::from_array($v)."</item>".PHP_EOL;
	    $str .= "</list>".PHP_EOL;
	    }
    return $str;
  } elseif(is_string($obj)) {
    return htmlspecialchars($obj) != $obj ? "<![CDATA[$obj]]>" : $obj;
  } elseif(is_scalar($obj))
    return $obj;
  else
    throw new Exception("Unsupported type $obj");
     */

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