<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202103301905282234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create atomic roles for items and assign permissions to them';
    }

    public function up(Schema $schema): void
    {
        OntologyUpdater::syncModels();

        foreach ($this->getItemClassNavigatorRules() as $rule) {
            AclProxy::applyRule($this->createAclRulesForRole(TaoRoles::ITEM_CLASS_NAVIGATOR, $rule));
        }

        foreach ($this->getItemClassEditorRules() as $rule) {
            AclProxy::applyRule($this->createAclRulesForRole(TaoRoles::ITEM_CLASS_EDITOR, $rule));
        }

        foreach ($this->getItemClassCreatorRules() as $rule) {
            AclProxy::applyRule($this->createAclRulesForRole(TaoRoles::ITEM_CLASS_CREATOR, $rule));
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function getItemClassNavigatorRules(): array
    {
        return [
            ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'viewClassLabel'],
            ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'getOntologyData'],
            ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'index'],
        ];
    }

    private function getItemClassEditorRules(): array
    {
        return [
            ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'editClassLabel'],
        ];
    }

    private function getItemClassCreatorRules(): array
    {
        return [
            ['ext' => 'taoItems', 'mod' => 'Items', 'act' => 'addSubClass'],
        ];
    }

    private function createAclRulesForRole(string $role, array $rule): AccessRule
    {
        return new AccessRule(
            AccessRule::GRANT,
            $role,
            $rule
        );
    }
}
