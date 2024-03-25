<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\tao\model\TaoOntology;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202403072011000000_tao extends AbstractMigration
{
    use OntologyAwareTrait;

    public function getDescription(): string
    {
        return sprintf('Fix duplicated label for %s (Test-taker)', TaoOntology::CLASS_URI_SUBJECT);
    }

    public function up(Schema $schema): void
    {
        $testTakerClass = $this->getClass(TaoOntology::CLASS_URI_SUBJECT);
        $labelProperty = $this->getProperty(OntologyRdfs::RDFS_LABEL);
        $testTakerClass->removePropertyValue(
            $labelProperty,
            $testTakerClass->getOnePropertyValue($labelProperty)
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
