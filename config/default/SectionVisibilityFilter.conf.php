<?php

use oat\tao\model\menu\SectionVisibilityFilter;
use oat\taoLti\models\classes\FeatureFlag\ExcludedSectionList;

return new SectionVisibilityFilter([
    SectionVisibilityFilter::OPTION_CLASSES => [
        new ExcludedSectionList()
    ]
]);
