<?php
/**
 * Default config header created during install
 */
use oat\tao\model\TaoOntology;
use oat\tao\model\search\index\IndexService;

return new oat\tao\model\search\index\IndexService(array(
    IndexService::PROPERTY_ROOT_CLASSES => array(
        TaoOntology::CLASS_URI_ITEM => [
            IndexService::PROPERTY_FIELDS => []
        ],
        TaoOntology::CLASS_URI_TEST => [
            IndexService::PROPERTY_FIELDS => []
        ],
        TaoOntology::CLASS_URI_SUBJECT => [
            IndexService::PROPERTY_FIELDS => []
        ],
        TaoOntology::CLASS_URI_GROUP => [
            IndexService::PROPERTY_FIELDS => []
        ]
    ),
    IndexService::PROPERTY_CUSTOM_REINDEX_CLASSES => array()
));
