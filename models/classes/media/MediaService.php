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
 * Copyright (c) 2015-2020 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\media;

use common_Exception;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\service\ServiceNotFoundException;

/**
 * Service to manage the media sources
 *
 * To be used as if it were a singleton until serviceManager in place
 */
class MediaService extends ConfigurableService
{

    public const SERVICE_ID = 'tao/MediaService';

    public const OPTION_SOURCE = 'source';
    public const OPTION_PREPARAR = 'preparer';

    /**
     * Scheme name used to identify media resource URLs
     */
    public const SCHEME_NAME = 'taomedia';

    /**
     * @deprecated backward compatibility
     */
    public static function singleton(): self
    {
        return ServiceManager::getServiceManager()->get(self::SERVICE_ID);
    }

    /**
     * @var array
     */
    private $mediaSources;

    /**
     * Return all configured media sources
     *
     * @return MediaBrowser[]
     */
    protected function getMediaSources(): array
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
     * @throws common_Exception
     */
    public function getMediaSource(string $mediaSourceId): MediaBrowser
    {
        $sources = $this->getMediaSources();
        if (!isset($sources[$mediaSourceId])) {
            throw new common_Exception('Media Sources Configuration for source ' . $mediaSourceId . ' not found');
        }
        return $sources[$mediaSourceId];
    }

    /**
     * Returns all media sources that are browsable
     *
     * @return MediaBrowser[]
     */
    public function getBrowsableSources(): array
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
    public function getWritableSources(): array
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
     */
    public function addMediaSource(MediaBrowser $source): bool
    {
        // ensure loaded
        $mediaSources = $this->getMediaSources();
        // only mediaSource called 'mediamanager' supported
        $mediaSources['mediamanager'] = $source;
        return $this->registerMediaSources($mediaSources);
    }

    /**
     * Removes a media source for tao
     */
    public function removeMediaSource(string $sourceId): bool
    {
        // ensure loaded
        $mediaSources = $this->getMediaSources();
        unset($mediaSources[$sourceId]);
        return $this->registerMediaSources($mediaSources);
    }

    /**
     * @throws InvalidServiceManagerException
     * @throws common_Exception
     */
    public function registerMediaSources($sources): bool
    {
        $this->setOption(self::OPTION_SOURCE, $sources);
        $this->getServiceManager()->register(self::SERVICE_ID, $this);
        return true;
    }

    public function getMediaResourcePreparer(): ?ConfigurableService
    {
        try {
            return $this->getSubService(self::OPTION_PREPARAR);
        } catch (ServiceNotFoundException $e) {
            return null;
        }
    }
}
