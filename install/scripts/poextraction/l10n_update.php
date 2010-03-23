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

	
//get exisiting strings
$listeChaineExistante	= getPoFile($empLoc. $langue . '/' . $fichier);

# charset 
$charset = 'UTF-8';

$listeChaineFichier = array();
foreach ($directories as $dir){
	$listeChaineFichier = array_merge($listeChaineFichier, parcoursRepertoire($dir, $extension));
}
$listeChaine	= array_merge($listeChaineFichier, $listeChaineExistante);


echo $empLoc. $langue. "/" .$fichier."\n";
if(writePoFile($empLoc. $langue. "/" .$fichier, $listeChaine)){
	echo "File generated !\n";
}
else{
	echo "An error occured during the file generation!\n";
}
	
?>