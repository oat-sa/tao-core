<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use oat\tao\scripts\tools\AddRtlLocale;
use oat\tao\scripts\tools\TextDirectionRegistry;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202105140945122237_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'This migration will add ar-arb locale to RTL map';
    }

    public function up(Schema $schema): void
    {
        $this->runAction(
            new AddRtlLocale(),
            [
                '-rtl' ,
                'ar-arb'
            ]
        );

    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration(
            'You cannot remove locales from configuration'
        );
    }
}
