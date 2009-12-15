<?php
/**
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 */

$todefine = array(
	'TAO_OBJECT_CLASS' 		=> 'http://www.tao.lu/Ontologies/TAOGroup.rdf#Group',
	'RDFS_LABEL'			=> 'http://www.w3.org/2000/01/rdf-schema#label',
	'GENERIS_BOOLEAN'		=> 'http://www.tao.lu/Ontologies/generis.rdf#Boolean'
);
foreach($todefine as $constName => $constValue){
	if(!defined($constName)){
		define($constName, $constValue);
	}
}
unset($todefine);
?>