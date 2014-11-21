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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

namespace oat\tao\model\media;

class MediaSource{

    const CONFIG_KEY = 'mediaSources';

    /**
     * @var array
     */
    private static $mediaSources = array();


    public static function getMediaSources(){
        $tao = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $configs = $tao->hasConfig(self::CONFIG_KEY)
            ? $tao->getConfig(self::CONFIG_KEY)
            : array();

        foreach($configs as $mediaSourceId => $mediaSource){
            self::getMediaSource($mediaSourceId);
        }

        return self::$mediaSources;
    }

    /**
     *
     * @param string $mediaSourceId
     * @return MediaBrowser
     */
    public static function getMediaSource($mediaSourceId){
        if(!isset(self::$mediaSources[$mediaSourceId])) {
            self::$mediaSources[$mediaSourceId] = self::createMediaSource($mediaSourceId);
        }
        return self::$mediaSources[$mediaSourceId];
    }

    /**
     * Add a new persistence to the system
     *
     * @param string $mediaSourceId
     * @param string $mediaSource
     */
    public static function addMediaSource($mediaSourceId, $mediaSource) {
        $tao = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $configs = $tao->hasConfig(self::CONFIG_KEY)
            ? $tao->getConfig(self::CONFIG_KEY)
            : array();
        $configs[$mediaSourceId] = $mediaSource;
        $tao->setConfig(self::CONFIG_KEY, $configs);
    }

    /**
     * Add a new persistence to the system
     *
     * @param string $mediaSourceId
     * @param array $mediaSource
     */
    public static function removeMediaSource($mediaSourceId) {
        $tao = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        $configs = $tao->hasConfig(self::CONFIG_KEY)
            ? $tao->getConfig(self::CONFIG_KEY)
            : array();
        if(isset($configs[$mediaSourceId])){
            unset($configs[$mediaSourceId]);
            if(isset(self::$mediaSources[$mediaSourceId])){
                unset(self::$mediaSources[$mediaSourceId]);
            }
            $tao->setConfig(self::CONFIG_KEY, $configs);
        }
        else{
            throw new \common_Exception('Media Sources Configuration for source '.$mediaSourceId.' not found');
        }

    }

    /**
     * @param string $mediaSourceId
     * @throws \common_Exception
     * @return MediaBrowser
     */
    private static function createMediaSource($mediaSourceId) {
        $tao = \common_ext_ExtensionsManager::singleton()->getExtensionById('tao');
        if ($tao->hasConfig(self::CONFIG_KEY)) {
            $configs = $tao->getConfig(self::CONFIG_KEY);
            if (isset($configs[$mediaSourceId])) {
                $config = $configs[$mediaSourceId];
                return $config;
            } else {
                throw new \common_Exception('Media Sources Configuration for source '.$mediaSourceId.' not found');
            }
        } else {
            throw new \common_Exception('Media Sources Configuration not found');
        }
    }

} 