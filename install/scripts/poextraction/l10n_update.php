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

	
	# récupération des chaines existantes dans le fichier de langue
	$listeChaineExistante	= getPoFile($empLoc. $langue . '/' . $fichier);
	
	# charset de la page
	$charset = 'UTF-8';
	
	//header('Content-Type: text/html; charset=' . $charset);	
		
	$listeChaineFichier = array();
	foreach ($directories as $dir){
		$listeChaineFichier = array_merge($listeChaineFichier, parcoursRepertoire($dir, $extension));
	}
	
	#
	$listeChaine	= array_merge($listeChaineFichier, $listeChaineExistante);
	
	
	# ouverture du fichier
	echo $empLoc. $langue. "/" .$fichier."\n";
	if (!$handle = fopen($empLoc. $langue. "/" .$fichier, 'w+')) {
		echo "Impossible d'ouvrir le fichier:  ".$empLoc. $langue. "/" .$fichier. "\n";
		exit();
	}
	
	foreach($listeChaine as $chaine => $trad) {
		fwrite($handle, "msgid \"". $chaine ."\"\n");
		fwrite($handle, "msgstr \"". $trad ."\"\n");
		fwrite($handle, "\n");
	}
	
	fclose($handle);
	
	echo "Fichier généré !\n";
?>