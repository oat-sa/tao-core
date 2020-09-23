<?php

declare(strict_types=1);

namespace oat\tao\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\tao\model\menu\SectionVisibilityFilter;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoLti\models\classes\FeatureFlag\ExcludedSectionListProvider;
use oat\taoLti\models\classes\FeatureFlag\LtiFeatures;

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
            FeatureFlagChecker::SERVICE_ID,
            new FeatureFlagChecker(
                [
                    FeatureFlagChecker::OPTION_MANUALLY_ENABLED_FEATURES =>
                        [
                            LtiFeatures::LTI_1P3
                        ]
                ]
            )
        );

        $this->getServiceManager()->register(
            SectionVisibilityFilter::SERVICE_ID,
            new SectionVisibilityFilter([
                SectionVisibilityFilter::EXCLUDED_SECTION_LIST_PROVIDERS => [
                    new ExcludedSectionListProvider()
                ]
            ])
        );
    }

    public function down(Schema $schema): void
    {
        $this->getServiceManager()->unregister(FeatureFlagChecker::SERVICE_ID);
        $this->getServiceManager()->unregister(SectionVisibilityFilter::SERVICE_ID);
    }
}
