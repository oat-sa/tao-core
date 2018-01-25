<?php
/**
 * Default config header created during install
 */
use oat\tao\model\TaoOntology;
use oat\tao\model\search\index\IndexService;

return new oat\tao\model\search\index\IndexService(array(
    IndexService::OPTION_ROOT_CLASSES => array(
        TaoOntology::CLASS_URI_ITEM => [
            IndexService::OPTION_CUSTOM_FIELDS => []
        ],
        TaoOntology::CLASS_URI_TEST => [
            IndexService::OPTION_CUSTOM_FIELDS => []
        ],
        TaoOntology::CLASS_URI_SUBJECT => [
            IndexService::OPTION_CUSTOM_FIELDS => []
        ],
        TaoOntology::CLASS_URI_GROUP => [
            IndexService::OPTION_CUSTOM_FIELDS => []
        ]
    ),
    IndexService::OPTION_CUSTOM_REINDEX_CLASSES => array()
));
