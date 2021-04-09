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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\update\OntologyUpdater;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\scripts\tools\migrations\AbstractMigration;

final class Version202103301905282234_tao extends AbstractMigration
{
    private const RULES = [
        TaoRoles::ITEM_CLASS_NAVIGATOR => [
            ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'viewClassLabel'],
            ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'getOntologyData'],
            ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'index'],
        ],
        TaoRoles::ITEM_CLASS_EDITOR => [
            ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'editClassLabel'],
        ],
        TaoRoles::ITEM_CLASS_EDITOR => [
            ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'addSubClass'],
        ],
    ];

    public function getDescription(): string
    {
        return 'Create roles for items and assign permissions to them';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();

        foreach (self::RULES as $roleUri => $masks) {
            foreach ($masks as $mask) {
                AclProxy::applyRule($this->createAccessRule($roleUri, $mask));
            }
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::RULES as $roleUri => $masks) {
            foreach ($masks as $mask) {
                AclProxy::revokeRule($this->createAccessRule($roleUri, $mask));
            }
        }
    }

    private function createAccessRule(string $role, array $rule): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            $role,
            $rule
        );
    }
}
