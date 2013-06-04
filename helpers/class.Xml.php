<?php
class tao_helpers_Xml {
/**
* @param mixed
 * @return string xml
 */
    public static function from_array($obj)
    {
	$xmlStr = "";
	switch (gettype($obj)){

	    case "object":{
		$xmlStr = "<object>".PHP_EOL;
		foreach ($obj as $member=>$value){
		    $valueType = gettype($value);
		    if (in_array($valueType, array("array", "object"))) {
			$xmlStr .= "<".$member.">".PHP_EOL.self::from_array($value).PHP_EOL."</".$member.">".PHP_EOL;
		    }
		    else {
			$xmlStr .= "<".$member.">".$value."</".$member.">".PHP_EOL;
		    }
		}
		$xmlStr .= "</object>".PHP_EOL;
	    break;}
	    case "array":{
		$xmlStr = "<list>".PHP_EOL;
		foreach ($obj as $member=>$value){
		    $valueType = gettype($value);
		    if (in_array($valueType, array("array", "object"))) {
			$xmlStr .= "<".$member.">".PHP_EOL.self::from_array($value).PHP_EOL."</".$member.">".PHP_EOL;
		    }
		    else {
			$xmlStr .= "<".$member.">".$value."</".$member.">".PHP_EOL;
		    }
		}
		$xmlStr .= "</list>".PHP_EOL;
	    break;}

	}
	return $xmlStr;
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