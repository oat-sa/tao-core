<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

$langClass = new core_kernel_classes_Class(CLASS_LANGUAGES);
$langInstances = $langClass->getInstances();
$valueProperty = new core_kernel_classes_Property(RDF_VALUE);

// Put old languages in the local namespace.
$dbWrapper = core_kernel_classes_DbWrapper::singleton();
$dbWrapper->execSql("UPDATE statements SET modelID = 8 WHERE subject LIKE 'http://www.tao.lu/Ontologies/TAO.rdf#Lang%'");

if ($dbWrapper->getAffectedRows() > 0){
    // Transform language instances to make them compliant with TAO 2.2.
    foreach ($langInstances as $lang){
        $tmpCode = trim($lang->getLabel());
        $tmpName = trim($lang->getComment());
        
        if ($lang->delete()){
            $newLang = core_kernel_classes_ClassFactory::createInstance($langClass,
                                                                        $tmpName,
                                                                        $tmpName,
                                                                        'http://www.tao.lu/Ontologies/TAO.rdf#Lang' . $tmpCode);
            $newLang->setPropertyValue($valueProperty, $tmpCode);
        }
        else{
            common_Logger::e("Unable to delete language '${tmpCode}' from ontology.", array('UPDATE'));
        }
    }  
}
else{
    common_Logger::e("Unable to transfer language instances from tao model to the local one.", array('UPDATE'));
}

?>