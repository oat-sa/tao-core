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

use common_report_Report as Report;
use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\user\TaoRoles;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Class Version202010291229382234_tao
 *
 * @package oat\tao\migrations
 */
final class Version202010291229382234_tao extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Grant base access to the AdvancedSearch controller.';
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        AclProxy::applyRule($this->createRule());

        $this->addReport(Report::createInfo(
            'Base access was successfully granted to the AdvancedSearch controller.'
        ));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        AclProxy::revokeRule($this->createRule());

        $this->addReport(Report::createInfo(
            'Base access was successfully revoked from the AdvancedSearch controller.'
        ));
    }

    /**
     * @return AccessRule
     */
    private function createRule(): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            TaoRoles::BASE_USER,
            ['ext' => 'tao', 'mod' => 'AdvancedSearch']
        );
    }
}
