<?php

use oat\tao\model\featureFlag\FeatureFlagChecker;
use oat\taoLti\models\classes\FeatureFlag\LtiFeatures;

return new FeatureFlagChecker(
    [
        FeatureFlagChecker::OPTION_ENABLED_FEATURES => LtiFeatures::LTI_1P3
    ]
);
