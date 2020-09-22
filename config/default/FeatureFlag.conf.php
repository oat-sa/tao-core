<?php

use oat\tao\model\featureFlag\Lti1p3FeatureFlag;

return new Lti1p3FeatureFlag([
        Lti1p3FeatureFlag::OPTION_LTI_1P3_ENABLED => false,
        Lti1p3FeatureFlag::OPTION_DISABLED_SECTIONS => [
            'settings_manage_lti_keys'
        ]
    ]
);