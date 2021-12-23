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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA.
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\menu\SectionVisibilityFilter;
use oat\tao\model\user\TaoRoles;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202112210823010101_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Apply permission for MetadataImport';
    }

    public function up(Schema $schema): void
    {
        AclProxy::applyRule($this->createRule());

        $this->addReport(Report::createInfo('Apply permission for MetadataImport'));

        $sectionVisibilityFilter = $this->getServiceManager()->get(SectionVisibilityFilter::SERVICE_ID);

        $options = $sectionVisibilityFilter->getOption(SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS, []);
        $options['settings_metadata_import'] = [
            'FEATURE_FLAG_STATISTIC_METADATA_IMPORT'
        ];
        $sectionVisibilityFilter->setOption(SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS, $options);

        $this->getServiceManager()->register(SectionVisibilityFilter::SERVICE_ID, $sectionVisibilityFilter);

        $this->addReport(Report::createInfo('Add feature flag visibility for settings_metadata_import'));
    }

    public function down(Schema $schema): void
    {
        AclProxy::revokeRule($this->createRule());

        $this->addReport(Report::createInfo('Revoke permission for MetadataImport'));

        $sectionVisibilityFilter = $this->getServiceManager()->get(SectionVisibilityFilter::SERVICE_ID);

        $options = $sectionVisibilityFilter->getOption(SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS, []);

        unset($options['settings_metadata_import']);

        $sectionVisibilityFilter->setOption(SectionVisibilityFilter::OPTION_FEATURE_FLAG_SECTIONS, $options);

        $this->getServiceManager()->register(SectionVisibilityFilter::SERVICE_ID, $sectionVisibilityFilter);

        $this->addReport(Report::createInfo('Remove feature flag visibility for settings_metadata_import'));
    }

    private function createRule(): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            TaoRoles::GLOBAL_MANAGER,
            [
                'ext' => 'tao',
                'mod' => 'MetadataImport'
            ]
        );
    }
}
