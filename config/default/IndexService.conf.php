<?php
/**
 * Default config header created during install
 */
use oat\tao\model\TaoOntology;
use oat\tao\model\search\index\IndexService;

return new oat\tao\model\search\index\IndexService(array(
    IndexService::OPTION_ROOT_CLASSES => array(
        TaoOntology::CLASS_URI_ITEM,
        TaoOntology::CLASS_URI_TEST,
        TaoOntology::CLASS_URI_SUBJECT,
        TaoOntology::CLASS_URI_GROUP
    ),
    IndexService::OPTION_CUSTOM_REINDEX_CLASSES => []
));
