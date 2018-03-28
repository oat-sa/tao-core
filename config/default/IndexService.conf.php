<?php
/**
 * Default config header created during install
 */
use oat\tao\model\search\index\IndexService;

return new IndexService([
    IndexService::OPTION_LASTRUN_STORE => 'YOUR_KEYVALUE_PERSISTENCE',
    IndexService::OPTION_INDEX_SINCE_LAST_RUN => false,

]);
