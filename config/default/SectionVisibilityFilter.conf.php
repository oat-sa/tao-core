<?php

use oat\tao\model\menu\SectionVisibilityFilter;
use oat\taoLti\models\classes\FeatureFlag\ExcludedSectionListProvider;

return new SectionVisibilityFilter([
    SectionVisibilityFilter::EXCLUDED_SECTION_LIST_PROVIDERS => [
        new ExcludedSectionListProvider()
    ]
]);
