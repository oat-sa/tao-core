<?php

return new \oat\tao\model\metadata\import\OntologyMetadataImport(array(
    'labelCsvToOntology' => [
        'class' => \oat\tao\model\metadata\injector\implementation\LabelCsvToOntologyInjector::class,
        'source' => [
            'label' => 'type'
        ],
        'destination' => [
            'labelPropertyWriter' => [
                'class' => \oat\tao\model\metadata\writer\ontologyWriter\PropertyWriter::class,
                'params' => [
                    'propertyUri' => RDFS_LABEL
                ]
            ]
        ]
    ]
));