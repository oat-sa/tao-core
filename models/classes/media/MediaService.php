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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\media;

use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;

/**
 * Service to manage the media sources
 *
 * To be used as if it were a singleton until serviceManager in place
 */
class MediaService extends ConfigurableService
{
    
    const SERVICE_ID = 'tao/MediaService';

    const OPTION_SOURCE = 'source';

    /**
     * Scheme name used to identify media resource URLs
     *
     * @var string
     */
    const SCHEME_NAME = 'taomedia';

    /**
     * @deprecated backward compatibility
     * @return \oat\tao\model\media\MediaService
     */
    public static function singleton()
    {
        return ServiceManager::getServiceManager()->get(self::SERVICE_ID);
    }
    
    /**
     * @var array
     */
    private $mediaSources = null;
    
    /**
     * Return all configured media sources
     *
     * @return MediaBrowser
     */
    protected function getMediaSources()
    {
        if (is_null($this->mediaSources)) {
            $this->mediaSources = [];
            foreach ($this->getOption(self::OPTION_SOURCE) as $mediaSourceId => $mediaSource) {
                $this->mediaSources[$mediaSourceId] = $this->propagate($mediaSource);
            }
        }
        return $this->mediaSources;
    }
    
    /**
     * Returns the media source specified by $mediaSourceId
     *
     * @param string $mediaSourceId
     * @throws \common_Exception
     * @return MediaBrowser
     */
    public function getMediaSource($mediaSourceId)
    {
        $sources = $this->getMediaSources();
        if (!isset($sources[$mediaSourceId])) {
            throw new \common_Exception('Media Sources Configuration for source ' . $mediaSourceId . ' not found');
        }
        return $sources[$mediaSourceId];
    }

    /**
     * Returns all media sources that are browsable
     *
     * @return MediaBrowser[]
     */
    public function getBrowsableSources()
    {
        $returnValue = [];
        foreach ($this->getMediaSources() as $id => $source) {
            if ($source instanceof MediaBrowser) {
                $returnValue[$id] = $source;
            }
        }
        return $returnValue;
    }

    /**
     * Returns all media sources that can write
     *
     * @return MediaManagement[]
     */
    public function getWritableSources()
    {
        $returnValue = [];
        foreach ($this->getMediaSources() as $id => $source) {
            if ($source instanceof MediaManagement) {
                $returnValue[$id] = $source;
            }
        }
        return $returnValue;
    }
    
    /**
     * Adds a media source to Tao
     *
     * WARNING: Will always add the mediasource as 'mediamanager' as other
     * identifiers are not supported by js widget
     *
     * @param MediaBrowser $source
     * @return boolean
     */
    public function addMediaSource(MediaBrowser $source)
    {
        // ensure loaded
        $mediaSources = $this->getMediaSources();
        // only mediaSource called 'mediamanager' supported
        $mediaSources['mediamanager'] = $source;
        return $this->registerMediaSources($mediaSources);
    }
    
    /**
     * Removes a media source for tao
     *
     * @param string $sourceId
     * @return boolean
     */
    public function removeMediaSource($sourceId)
    {
        // ensure loaded
        $mediaSources = $this->getMediaSources();
        unset($mediaSources[$sourceId]);
        return $this->registerMediaSources($mediaSources);
    }
    
    public function registerMediaSources($sources)
    {
        $this->setOption(self::OPTION_SOURCE, $sources);
        $this->getServiceManager()->register(self::SERVICE_ID, $this);
        return true;
    }
}
