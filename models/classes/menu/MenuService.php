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
    public static function getAllPerspectives() {
        $structure = self::readStructure();
        return $structure['perspectives'];
    }

    /**
     * Returns all the existing entry points
     *
     * @return array()
     */
    public static function getEntryPoints()
    {
        $structure = self::readStructure();
        return $structure['entrypoints'];
    }    
    
    public static function readStructure()
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
		$perspectives = array();
		$entrypoints = array();
		$sorted = \helpers_ExtensionHelper::sortByDependencies(\common_ext_ExtensionsManager::singleton()->getEnabledExtensions());
		foreach($sorted as $extID => $extension){
			$xmlStructures = self::getStructuresXml($extID);
			if(!is_null($xmlStructures)){
				$extStructures = $xmlStructures->xpath("/structures/structure");
				foreach($extStructures as $xmlStructure){
					$id = (string)$xmlStructure['id'];
					if (!isset($perspectives[$id])) {
						$perspectives[$id] = Perspective::fromSimpleXMLElement($xmlStructure, $extID);
					} else {
					    $sections = $xmlStructure->xpath("sections/section");
					    foreach($sections as $section) {
					        $perspectives[$id]->addSection(Section::fromSimpleXMLElement($section));
					    }
					}
				}
				foreach($xmlStructures->xpath("/structures/entrypoint") as $xmlStructure){
				    $entryPoint = Entrypoint::fromSimpleXMLElement($xmlStructure);
				    foreach ($entryPoint->getReplacedIds() as $id) {
				        if (isset($entrypoints[$id])) {
				            unset($entrypoints[$id]);
				        }
				    }
				    $entrypoints[$entryPoint->getId()] = $entryPoint;
				}
			}
		}
		usort($perspectives, create_function('$a,$b', "return \$a->getLevel() - \$b->getLevel(); "));
		return array(
			'perspectives' => $perspectives,
		    'topbars' => array(),
		    'entrypoints' => $entrypoints
		);
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
     * Get the perspective for the extension/section in parameters
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  string extension
     * @param  string perspectiveId
     * @return Structure
     */
    public static function getPerspective($extension, $perspectiveId)
    {
        $returnValue = array();

		foreach(self::getAllPerspectives() as $perspective){
		    if ($perspective->getId() == $perspectiveId) {
				$returnValue = $perspective;
			    break;
			}
		}
		if (empty($returnValue)) {
			\common_logger::w('Structure '.$perspectiveId.' not found for extension '.$extension);
    	}

        return $returnValue;
    }

    /**
     * Short description of method getSection
     *
     * @access public
     * @author Jerome Bogaerts, <jerome@taotesting.com>
     * @param  string extension
     * @param  string perspectiveId
     * @param  string sectionId
     * @return Section
     */
    public static function getSection($extension, $perspectiveId, $sectionId)
    {
        $returnValue = array();

        $structure = self::getPerspective($extension, $perspectiveId);
        foreach ($structure->getSections() as $section) {
            if ($section->getId() == $sectionId) {
                $returnValue = $section;
                break;
            }
        }
		if (empty($returnValue)) {
			\common_logger::w('Section '.$section.' not found found for perspective '.$perspectiveId);
    	}

        return $returnValue;
    }
}