<?php

declare(strict_types=1);

/**
 * Default config header created during install
 */

return new oat\tao\model\user\import\UserCsvImporterFactory([
    'mappers' => [
    ],
    'default-schema' => [
        'mandatory' => [
            'label' => 'http://www.w3.org/2000/01/rdf-schema#label',
            'interface language' => 'http://www.tao.lu/Ontologies/generis.rdf#userUILg',
            'login' => 'http://www.tao.lu/Ontologies/generis.rdf#login',
            'password' => 'http://www.tao.lu/Ontologies/generis.rdf#password',
        ],
        'optional' => [
            'default language' => 'http://www.tao.lu/Ontologies/generis.rdf#userDefLg',
            'first name' => 'http://www.tao.lu/Ontologies/generis.rdf#userFirstName',
            'last name' => 'http://www.tao.lu/Ontologies/generis.rdf#userLastName',
            'mail' => 'http://www.tao.lu/Ontologies/generis.rdf#userMail',
        ],
    ],
]);
