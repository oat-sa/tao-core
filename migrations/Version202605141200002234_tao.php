<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\reporting\Report;
use oat\tao\model\i18n\DefaultTranslationBundleProcessor;
use oat\tao\model\i18n\TranslationBundleProcessorInterface;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use Throwable;

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
        try {
            $this->addReport(
                Report::createInfo(
                    sprintf(
                        'up(): registering %s (DefaultTranslationBundleProcessor)',
                        TranslationBundleProcessorInterface::SERVICE_ID
                    )
                )
            );
            $this->getServiceManager()->register(
                TranslationBundleProcessorInterface::SERVICE_ID,
                new DefaultTranslationBundleProcessor()
            );
            $this->addReport(
                Report::createSuccess(
                    sprintf(
                        'up(): registered %s (DefaultTranslationBundleProcessor)',
                        TranslationBundleProcessorInterface::SERVICE_ID
                    )
                )
            );
        } catch (Throwable $e) {
            $this->addReport(Report::createError($e->getMessage()));
            throw $e;
        }
    }

    public function down(Schema $schema): void
    {
        try {
            $this->addReport(
                Report::createInfo(
                    sprintf(
                        'down(): unregistering %s',
                        TranslationBundleProcessorInterface::SERVICE_ID
                    )
                )
            );
            $this->getServiceManager()->unregister(TranslationBundleProcessorInterface::SERVICE_ID);
            $this->addReport(
                Report::createSuccess(
                    sprintf(
                        'down(): unregistered %s',
                        TranslationBundleProcessorInterface::SERVICE_ID
                    )
                )
            );
        } catch (Throwable $e) {
            $this->addReport(Report::createError($e->getMessage()));
            throw $e;
        }
    }
}
