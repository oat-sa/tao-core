<?php
define('HOST', 'localhost');
define('DATABASE', 'topten-ict');
define('LOGIN', 'root');
define('PASSWORD', '');
define('TBL_CATEGORIES', 'categories');
define('TBL_QUESTION', 'questions');
define('TBL_RESPONSES', 'response_refs');
define('COL_LABEL', 'label');
define('PO_FILE', dirname(__FILE__) . '/messages.po');
define('INI_FILE', dirname(__FILE__) . '/messages.ini');
define('DATABASE_CHARSET', 'utf8');

function extractLabelsFromTable($conn, $tableId)
{
	$query = "SELECT " . COL_LABEL . " FROM ${tableId}";
	$result = mysql_query($query, $conn);
	$array = array();
	
	while ($row = mysql_fetch_array($result))
	{
		$array[] = $row[COL_LABEL];
	}
	
	return $array;
}

function extractLabelsFromIniFile($filename)
{
	return parse_ini_file($filename);
}

function appendToPOFile($fp, $labels, &$insertedLabels)
{
	foreach ($labels as $label)
	{
		if ($label != '' && !in_array($label, $insertedLabels))
		{
			fwrite($fp, 'msgid "' . $label . '"' . "\n");
			fwrite($fp, "msgstr \"\"\n\n");
			
			$insertedLabels[] = $label;
		}
	}
}

// Resources access
$conn = mysql_connect(HOST, LOGIN, PASSWORD);
mysql_select_db(DATABASE, $conn);
$fpPO = fopen(PO_FILE, 'w');
mysql_query("SET NAMES ".DATABASE_CHARSET);

$insertedLabels = array();

appendToPOFile($fpPO, extractLabelsFromTable($conn, TBL_CATEGORIES), $insertedLabels);
appendToPOFile($fpPO, extractLabelsFromTable($conn, TBL_QUESTION), $insertedLabels);
appendToPOFile($fpPO, extractLabelsFromTable($conn, TBL_RESPONSES), $insertedLabels);
appendToPOFile($fpPO, extractLabelsFromIniFile(INI_FILE), $insertedLabels);

mysql_close($conn);
fclose($fpPO);
?>