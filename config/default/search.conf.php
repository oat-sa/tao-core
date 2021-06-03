<?php

/**
 * Default label search based on generis
 */

use oat\tao\model\search\SearchProxy;
use oat\tao\model\search\strategy\GenerisSearch;

return new SearchProxy(
    [
        SearchProxy::OPTION_DEFAULT_SEARCH_CLASS => new GenerisSearch(),
    ]
);
