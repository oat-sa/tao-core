<?php
/** 
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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2017 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\helpers\translation;

use \common_exception_Error;
use \common_exception_InvalidArgumentType;
use \common_Logger;
/**
 * This class enables you to generate a bundle of translations, for a language and extensions. 
 * The bundle contains the translations of all the defined extensions.
 * 
 * @access public
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @package tao
 */
class TranslationBundle {

    /**
     * The bundle langCode, formated as a locale: en-US, fr-FR, etc.
     * @var string
     */
    private $langCode;

    /**
     * The list of extensions to generate the bundle for
     * @var common_ext_Extension[]
     */
    private $extensions;
    
    /**
     * The TAO platform installation base path.
     * @var string
     */
    private $basePath;
    
    /**
     * The TAO version in use.
     * @var string
     */
    private $taoVersion;

    /**
     * Create a new bundle
     * 
     * $extensions = ['tao', 'taoItems']
     * 
     * @param string $langCode
     * @param common_ext_Extension[]
     * @throws \InvalidArgumentException
     */
    public function __construct($langCode, $extensions, $basePath, $taoVersion = ''){
        if(!is_string($langCode)){
            throw new \InvalidArgumentException('$langCode argument should be a string.');
        }
        if(!is_string($basePath)){
            throw new \InvalidArgumentException('$basePath argument should be a string.');
        }
        if(!is_string($taoVersion)){
            throw new \InvalidArgumentException('$taoVersion argument should be a string.');
        }
        if(!is_array($extensions)){
            throw new \InvalidArgumentException('$extensions argument should be an array.');
        }
        if(empty($langCode) || empty($extensions) || empty($basePath)){
            throw new \InvalidArgumentException('$langCode, $extensions and $basePath arguments should not be empty.');
        }

        $this->langCode = $langCode;
        $this->extensions = $extensions;
        $this->basePath = rtrim($basePath, '/\\');
        $this->taoVersion = $taoVersion;
    }

    /**
     * Get a deterministic identifier from bundle data: one id for same langCode and extensions
     * @return string the identifier
     */
    public function getSerial(){
        $ids = $this->extensions;
        sort($ids); 
        return md5($this->langCode . '_' . implode('-', $ids));
    }

    /**
     * Generates the bundle to the given directory. It will create a json file, named with the langCode: {$directory}/{$langCode}.json
     * @param string $directory the path
     * @return string|false the path of the generated bundle or false
     */
    public function generateTo($directory){
        $translations = array();
        
        foreach($this->extensions as $extension){
            $jsFilePath = $this->basePath . '/' . $extension . '/locales/' . $this->langCode . '/messages_po.js';
            if(file_exists($jsFilePath)){
                $translate = json_decode(file_get_contents($jsFilePath),false);
                if($translate != null){
                    $translations = array_merge($translations, (array)$translate);
                }
            }
        }
        //the bundle contains as well some translations
        $content = array(
            'serial' =>  $this->getSerial(),
            'date'   => time(),
            'translations' =>   $translations
        );
        
        if (!empty($this->taoVersion)) {
            $content['version'] = $this->taoVersion;
        }
        
        if(is_dir($directory)){
            if(!is_dir($directory. '/' . $this->langCode)){
                mkdir($directory. '/' . $this->langCode);
            }
            $file = $directory. '/' . $this->langCode . '/messages.json';
            if(@file_put_contents($file, json_encode($content))){
                return $file;
            } 
        }
        return false; 
    } 

}

?>
