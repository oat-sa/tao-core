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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use common_report_Report as Report;
use oat\oatbox\cache\KeyValueCache;
use tao_helpers_I18n as I18nHelper;
use oat\tao\scripts\update\OntologyUpdater;
use Psr\SimpleCache\InvalidArgumentException;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202012211727432234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronize models.';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();
        $this->addReport(Report::createSuccess('Models were successfully synchronized.'));

        try {
            $this->getKeyValueCache()->delete(I18nHelper::AVAILABLE_LANGS_CACHEKEY);
        } catch (InvalidArgumentException $exception) {
            $this->addReport(Report::createInfo('Key-Value cache does not contain available languages.'));
        }
    }

    public function down(Schema $schema): void
    {
        $this->addReport(Report::createInfo('Nothing to downgrade.'));
    }

    private function getKeyValueCache(): KeyValueCache
    {
        return $this->getServiceLocator()->get(KeyValueCache::SERVICE_ID);
    }
}
