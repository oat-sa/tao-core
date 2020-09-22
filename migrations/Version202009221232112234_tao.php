<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\featureFlag\Lti1p3FeatureFlag;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202009221232112234_tao extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Register Lti1p3FeatureFlag service';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceManager()->register(
            Lti1p3FeatureFlag::SERVICE_ID,
            new Lti1p3FeatureFlag(
                [
                    Lti1p3FeatureFlag::OPTION_LTI_1P3_ENABLED => false,
                    Lti1p3FeatureFlag::OPTION_DISABLED_SECTIONS => [
                        'settings_manage_lti_keys',
                    ],
                ]
            )
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(Lti1p3FeatureFlag::SERVICE_ID);
    }
}
