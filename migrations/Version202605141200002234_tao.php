<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\i18n\DefaultTranslationBundleProcessor;
use oat\tao\model\i18n\TranslationBundleProcessorInterface;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202605141200002234_tao extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Registers default translation bundle processor for build-time PO post-processing';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            TranslationBundleProcessorInterface::SERVICE_ID,
            new DefaultTranslationBundleProcessor()
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(TranslationBundleProcessorInterface::SERVICE_ID);
    }
}
