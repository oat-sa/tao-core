<?php
function writeConfigValue($name,$val,&$str)
{
	$val = str_replace("'","\'",$val);
	$str = preg_replace('/(\''.$name.'\')(.*?)$/ms','$1,\''.$val.'\');',$str);
}

function loadSqlReplaceNS($pFile, $con,$namespace){
	if ($file = @fopen($pFile, "r")){
		$ch = "";

		while (!feof ($file)){
			$line = utf8_decode(fgets($file));

			if (isset($line[0]) && ($line[0] != '#') && ($line[0] != '-')){
				$ch = $ch.$line;
			}
		}

		$requests = explode(";", $ch);
		unset($requests[count($requests)-1]);
		foreach($requests as $request){
			$request = str_replace("##NAMESPACE", $namespace,$request);
			$request = str_replace("{ROOT_PATH}", $_SERVER['DOCUMENT_ROOT'], $request);
			$con->Execute($request);

		}

		fclose($file);
	}
	else{
		die("File not found".$pFile);
	}
}


function loadSql($pFile, $con) {
	if ($file = @fopen($pFile, "r")){
		$ch = "";

		while (!feof ($file)){
			$line = utf8_decode(fgets($file));

			if (isset($line[0]) && ($line[0] != '#') && ($line[0] != '-')){
				$ch = $ch.$line;
			}
		}

		$requests = explode(";", $ch);
		unset($requests[count($requests)-1]);
		foreach($requests as $request){
			$con->Execute($request);

		}

		fclose($file);
	}
	else{
		die("File not found".$pFile);
	}
}
?>