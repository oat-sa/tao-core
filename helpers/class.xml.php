<?php
class tao_helpers_xml {
/**
* @param mixed
 * @return string xml
 */
    public static function from_array($obj)
    {
	$str = "";
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
	    $str.= "<list>";
	    foreach($obj as $v)
	      $str.="<item>".self::from_array($v)."</item>".PHP_EOL;
	    $str .= "</list>";
	    }
    return $str;
  } elseif(is_string($obj)) {
    return htmlspecialchars($obj) != $obj ? "<![CDATA[$obj]]>" : $obj;
  } elseif(is_scalar($obj))
    return $obj;
  else
    throw new Exception("Unsupported type $obj");
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