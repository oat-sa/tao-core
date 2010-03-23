<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of "myWiWall".
# Copyright (c) 2007-2008 CRP Henri Tudor and contributors.
# All rights reserved.
#
# "myWiWall" is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License version 2 as published by
# the Free Software Foundation.
# 
# "myWiWall" is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with "myWiWall"; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

	
/**
 * Parcours d'un répertoire
 * @param	string		$pRoot			Répértoire à parcourir
 * @param	array		$pExtension		Liste des extensions autorisées
 * @return	array						Liste des chaines à traduire
 */
function parcoursRepertoire($pRoot, $pExtension) {
	$liste_chaine	= array();
	
	if (is_dir($pRoot)) {	
    	# on récupére la liste des fichiers et dossiers
    	if (($liste_fichiers_dossiers	= scandir($pRoot)) !== false) {
    		# parcours des fichiers et dossiers
    		foreach($liste_fichiers_dossiers as $fd) {

   		 		if ($fd !== "." && $fd !== ".." && $fd !== ".svn" && $fd !== ".settings") {
   		 			# si c'est un dossier
   		 			if (is_dir($pRoot. $fd."/")) {
	    				$liste_chaine	= array_merge($liste_chaine, parcoursRepertoire($pRoot.$fd."/", $pExtension));
	    			# si c'est un fichier
    				} else {
    					if(in_array('xml', $pExtension) && preg_match("/actions(.*)structure\.xml$/", $pRoot. $fd)){
    						$liste_chaine	= array_merge($liste_chaine, getXmlStrings($pRoot. $fd));
    					}
						else{
							$liste_chaine	= array_merge($liste_chaine, recuperationChaine($pRoot. $fd, $pExtension));
						}
    				}
   		 		}
    			
    		}
    	}   	
	}
	
	return $liste_chaine;
}
	
/**
 * Récupération des chaines à traduire dans un fichier
 * @param	string		$pFichier		Nom du fichier
 */
 function recuperationChaine($pFichier, $pExtension) {
 	# on vérifie qu'il s'agit bien d'un fichier
 	if (!is_file($pFichier)) {
 		return array();
 	}
	
 	# récupération de l'extension du fichier
	$extOk = false;
	foreach ($pExtension as $exp)
	{
		if (ereg("\.${exp}$", $pFichier))
		{
			$extOk = true;
			break;
		}
	}
	
	if ($extOk)
	{
		echo "Récupération chaines dans fichier ${pFichier}\n"; 
		$liste_chaine = array();
	
	 	# lecture du fichier
	 	$lines			= file($pFichier);
	 	foreach ($lines as $line_num => $line) {
	 		$chaine			= array();
	 		preg_match_all("/__\(['\"](.*?)['\"]\)/u", $line, $chaine);
			
	 		if (!empty($chaine[1])) {
	 			foreach($chaine[1] as $c) {
	 				$liste_chaine[$c]	= "";
	 			}
	 		}
	 	}
		
		return $liste_chaine;
	}
	else
	{
		return array();
	}
 }
	
/**
 * get the strings of a po file
 * @param string $file 
 * @return array 
 */
function getPoFile($file) {
	if (!file_exists($file)) {
		echo 'The file does not exist\n';
		return false;
	}
	
	$fc = implode('',file($file));
	
	$res = array();
	
	$matched = preg_match_all('/(msgid\s+("([^"]|\\\\")*?"\s*)+)\s+'.
	'(msgstr\s+("([^"]|\\\\")*?(?<!\\\)"\s*)+)/',
	$fc, $matches);
	
	if (!$matched) {
		
		return $res;
	}
	
	for ($i=0; $i<$matched; $i++) {
		$msgid = preg_replace('/\s*msgid\s*"(.*)"\s*/s','\\1',$matches[1][$i]);
		$msgstr= preg_replace('/\s*msgstr\s*"(.*)"\s*/s','\\1',$matches[4][$i]);
		
		$msgstr = poString($msgstr);
		
		if ($msgstr) {
			$res[poString($msgid)] = $msgstr;
		}
	}
	
	if (!empty($res[''])) {
		$meta = $res[''];
		unset($res['']);
	}
	
	return $res;
}
	
function poString($string, $reverse = false) {
	if ($reverse) {
		$smap = array('"', "\n", "\t", "\r");
		$rmap = array('\\"', '\\n"' . "\n" . '"', '\\t', '\\r');
		return trim((string) str_replace($smap, $rmap, $string));
	} else {
		$smap = array('/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\"/');
		$rmap = array('', "\n", "\r", "\t", '"');
		return trim((string) preg_replace($smap, $rmap, $string));
	}
}

/**
 * Parse Xml file for string to translate
 * @param string $file
 * @return array
 */
function getXmlStrings($file){
	if (!file_exists($file)) {
 		return array();
 	}
	
	$strings = array();
	try{
		$xml = new SimpleXMLElement(trim(file_get_contents($file)));
		if($xml instanceof SimpleXMLElement){
			$nodes = $xml->xpath("//*[@name]");
			foreach($nodes as $node){
				if(isset($node['name'])){
					$strings[(string)$node['name']] = '';
				}
			}
		}
	}
	catch(Exception $e){}
	return $strings;
}

/**
 * Write the translated strings array to a po file
 * @param string $file
 * @param array $strings
 * @return boolean
 */
function writePoFile($file, $strings){
	$buffer = '';
	foreach($strings as $string => $translation) {
		$buffer .=  "msgid \"{$string}\"\n";
		$buffer .=  "msgstr \"{$translation}\"\n";
		$buffer .=  "\n";
	}
	return file_put_contents($file, $buffer);
}
?>