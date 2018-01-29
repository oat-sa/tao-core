<?php
/**
 * Default config header created during install
 */
use oat\tao\model\TaoOntology;
use oat\tao\model\search\index\IndexService;

return new oat\tao\model\search\index\IndexService(array(
    IndexService::OPTION_CUSTOM_REINDEX_CLASSES => []
));
