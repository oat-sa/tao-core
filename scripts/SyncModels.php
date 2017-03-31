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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\scripts;

use common_ext_ExtensionsManager;
use oat\oatbox\extension\InstallAction;
use oat\tao\scripts\update\OntologyUpdater;

/**
 * Update Rdf statements and clear the cache.
 *
 * php index.php '\oat\tao\scripts\SyncModels'
 */
class SyncModels extends InstallAction
{
    public function __invoke($params)
    {
        // update rdf
        OntologyUpdater::syncModels();

        // clear cache
        $serviceManager = $this->getServiceManager();

        $generis = common_ext_ExtensionsManager::singleton()->getExtensionById('generis');

        $cache = $generis->getConfig('cache');
        $cache->setServiceManager($serviceManager);
        $cache->purge();

        return new \common_report_Report(
            \common_report_Report::TYPE_SUCCESS,
            'RDF has been synced. Cache has been cleared. Please do not forget to restart PHP-FPM ;-)'
        );
    }
}
