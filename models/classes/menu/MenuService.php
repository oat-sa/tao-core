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

namespace oat\tao\models\classes\menu;

use oat\tao\models\classes\menu\Section;
use oat\tao\models\classes\menu\Structure;

/**
 * 
 * @author joel bout, <joel@taotesting.com>
 */
class MenuService {

    /**
     * identifier to use to cache the structures
     * @var string
     */
    const CACHE_KEY = 'tao_structures';
    
    // --- ATTRIBUTES ---

    /**
     * to stock the extension structure
     *
     * @access protected
     * @var array
     */
    protected static $structure = array();

    // --- OPERATIONS ---

    /**
     * Load the extension structure file.
     * Return the SimpleXmlElement object (don't forget to cast it)
     *
     * @access protected
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  string extensionId
     * @return SimpleXMLElement
     */
    private static function getStructuresXml($extensionId)
    {
        $returnValue = null;

        $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById($extensionId);
        $structureFilePath = $extension->getDir().'actions/structures.xml';
		
		if(file_exists($structureFilePath)){
			return new \SimpleXMLElement($structureFilePath, null, true);
		}

        return $returnValue;
    }
    
    /**
     * Get the structure content (from the structure.xml file) of each extension.
     * @return array
     */
    public static function getAllStructures()
    {
        if(count(self::$structure) == 0 ){
            try {
                self::$structure = \common_cache_FileCache::singleton()->get(self::CACHE_KEY);
            } catch (\common_cache_NotFoundException $e) {
                self::$structure = self::buildStructures();
                \common_cache_FileCache::singleton()->put(self::$structure, self::CACHE_KEY);
            }
        }
        return self::$structure;
    }

    /**
     * Get the structure content (from the structure.xml file) of each extension.
     * @return array
     */
    protected static function buildStructures()
    {
		$structures = array();
		$sorted = \helpers_ExtensionHelper::sortByDependencies(\common_ext_ExtensionsManager::singleton()->getEnabledExtensions());
		foreach($sorted as $extID => $extension){
			$xmlStructures = self::getStructuresXml($extID);
			if(!is_null($xmlStructures)){
				$extStructures = $xmlStructures->xpath("/structures/structure");
				foreach($extStructures as $xmlStructure){
					$id = (string)$xmlStructure['id'];
					if (!isset($structures[$id])) {
						$structures[$id] = Structure::fromSimpleXMLElement($xmlStructure, $extID);
					} else {
					    $sections = $xmlStructure->xpath("sections/section");
					    foreach($sections as $section) {
					        $structures[$id]->addSection(Section::fromSimpleXMLElement($section));
					    }
					}
				}
			}
		}
		usort($structures, create_function('$a,$b', "return \$a->getLevel() - \$b->getLevel(); "));
		return $structures;
    }

    /**
     * Get the toolbar actions for the enabled extensions
     * @return array
     */
    public static function getToolbarActions()
    {
	    $actions = array();
        foreach(\common_ext_ExtensionsManager::singleton()->getEnabledExtensions() as $extID => $extension){
            $xmlStructures = self::getStructuresXml($extID);
            if(!is_null($xmlStructures)){
                $actionsNodes = $xmlStructures->xpath("/structures/toolbar/action");
                foreach($actionsNodes as $actionNode){
                    $action = array(
                        'id'        => (string)$actionNode['id'],
                        'extension' => $extID,
                        'title'		=> (string)$actionNode['title'],
                        'level'		=> (int)$actionNode['level']
                    );
                    $text = (string)$actionNode;
                    if(!empty($text)){
                        $action['text'] =  $text;
                    }
                    if(isset($actionNode['icon'])){
                         $action['icon'] =  (string)$actionNode['icon'];
                    }
                    if(isset($actionNode['js'])){
                         $action['js'] =  (string)$actionNode['js'];
                    } else if(isset($actionNode['structure'])){
                         $action['structure'] =  (string)$actionNode['structure'];
                    }
                    $actions[] = $action;
                }
            }
        }
        
        usort($actions, create_function('$a,$b', "return \$a['level'] - \$b['level']; "));

        return $actions;
    }

    /**
     * Get the structure for the extension/section in parameters
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  string extension
     * @param  string structure
     * @return Structure
     */
    public static function getStructure($extension, $structureId)
    {
        $returnValue = array();

		foreach(self::getAllStructures() as $struct){
		    if ($struct->getId() == $structureId) {
				$returnValue = $struct;
			    break;
			}
		}
		if (empty($returnValue)) {
			\common_logger::w('Structure '.$structure.' not found for extension '.$extension);
    	}

        return $returnValue;
    }

    /**
     * Short description of method getSection
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  string extension
     * @param  string structure
     * @param  string section
     * @return Section
     */
    public static function getSection($extension, $structureId, $sectionId)
    {
        $returnValue = array();

        $structure = self::getStructure($extension, $structureId);
        foreach ($structure->getSections() as $section) {
            if ($section->getId() == $sectionId) {
                $returnValue = $section;
                break;
            }
        }
		if (empty($returnValue)) {
			\common_logger::w('Section '.$section.' not found found for structure '.$structure);
    	}

        return $returnValue;
    }
}