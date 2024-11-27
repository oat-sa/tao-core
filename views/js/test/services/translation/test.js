/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA ;
 */

define(['services/translation'], function (translationService) {
    'use strict';

    QUnit.module('translationService');

    QUnit.test('module', function (assert) {
        assert.equal(typeof translationService, 'object', 'The translationService module exposes an object');
    });

    QUnit.cases
        .init([
            { title: 'keys', type: 'object' },
            { title: 'labels', type: 'object' },
            { title: 'metadata', type: 'object' },
            { title: 'translationType', type: 'object' },
            { title: 'translationStatus', type: 'object' },
            { title: 'translationProgress', type: 'object' },
            { title: 'getTranslationsProgress', type: 'function' },
            { title: 'getTranslationsLanguage', type: 'function' },
            { title: 'listResourcesLanguages', type: 'function' },
            { title: 'listAvailableLanguages', type: 'function' },
            { title: 'listTranslatedLanguages', type: 'function' },
            { title: 'getLanguages', type: 'function' },
            { title: 'getTranslatable', type: 'function' },
            { title: 'getTranslatableStatus', type: 'function' },
            { title: 'getTranslations', type: 'function' },
            { title: 'createTranslation', type: 'function' },
            { title: 'updateTranslation', type: 'function' },
            { title: 'deleteTranslation', type: 'function' },
            { title: 'syncTranslation', type: 'function' }
        ])
        .test('translationService API', function (data, assert) {
            assert.equal(
                typeof translationService[data.title],
                data.type,
                `The translation API exposes the ${data.title}() ${data.type}`
            );
        });

    QUnit.cases
        .init([
            { title: 'empty', resources: [], expected: [] },
            { title: 'empty resource', resources: [{}], expected: [null] },
            { title: 'no progress', resources: [{ metadata: {} }], expected: [null] },
            {
                title: 'pending',
                resources: [
                    {
                        metadata: {
                            'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgress': {
                                value: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusPending',
                                literal: null
                            }
                        }
                    }
                ],
                expected: ['pending']
            },
            {
                title: 'in progress',
                resources: [
                    {
                        metadata: {
                            'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgress': {
                                value: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusTranslating',
                                literal: null
                            }
                        }
                    }
                ],
                expected: ['translating']
            },
            {
                title: 'completed',
                resources: [
                    {
                        metadata: {
                            'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgress': {
                                value: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusTranslated',
                                literal: null
                            }
                        }
                    }
                ],
                expected: ['translated']
            },
            {
                title: 'unknown',
                resources: [
                    {
                        metadata: {
                            'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgress': {
                                value: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusBlocked',
                                literal: null
                            }
                        }
                    }
                ],
                expected: ['http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusBlocked']
            }
        ])
        .test('getTranslationsProgress', function (data, assert) {
            assert.deepEqual(translationService.getTranslationsProgress(data.resources), data.expected);
        });

    QUnit.cases
        .init([
            { title: 'empty', resources: [], expected: [] },
            { title: 'empty resource', resources: [{}], expected: [null] },
            { title: 'no language', resources: [{ metadata: {} }], expected: [null] },
            {
                title: 'single language',
                resources: [
                    {
                        metadata: {
                            'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                                value: 'en',
                                literal: null
                            }
                        }
                    }
                ],
                expected: [
                    {
                        value: 'en',
                        literal: null
                    }
                ]
            },
            {
                title: 'multiple languages',
                resources: [
                    {
                        metadata: {
                            'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                                value: 'en',
                                literal: null
                            }
                        }
                    },
                    {
                        metadata: {
                            'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                                value: 'fr',
                                literal: null
                            }
                        }
                    }
                ],
                expected: [
                    {
                        value: 'en',
                        literal: null
                    },
                    {
                        value: 'fr',
                        literal: null
                    }
                ]
            }
        ])
        .test('getTranslationsLanguage', function (data, assert) {
            assert.deepEqual(translationService.getTranslationsLanguage(data.resources), data.expected);
        });

    QUnit.test('listResourcesLanguages', function (assert) {
        const resources = [
            {
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'en',
                        literal: null
                    }
                }
            },
            {
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'fr',
                        literal: null
                    }
                }
            }
        ];

        const languages = translationService.listResourcesLanguages(resources);
        assert.deepEqual(languages, ['en', 'fr'], 'Should return the correct languages');
    });

    QUnit.test('listResourcesLanguages with empty resources', function (assert) {
        const languages = translationService.listResourcesLanguages([]);
        assert.deepEqual(languages, [], 'Should return an empty array');
    });

    QUnit.test('listResourcesLanguages with no language metadata', function (assert) {
        const resources = [{}];
        const languages = translationService.listResourcesLanguages(resources);
        assert.deepEqual(languages, [], 'Should return an empty array');
    });

    QUnit.test('listResourcesLanguages with missing language metadata', function (assert) {
        const resources = [
            {
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'en',
                        literal: null
                    }
                }
            },
            {}
        ];
        const languages = translationService.listResourcesLanguages(resources);
        assert.deepEqual(languages, ['en'], 'Should return the correct languages');
    });

    QUnit.test('listAvailableLanguages', function (assert) {
        const resources = [
            {
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'en',
                        literal: null
                    }
                }
            },
            {
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'fr',
                        literal: null
                    }
                }
            }
        ];

        const languages = [
            { uri: 'en', name: 'English' },
            { uri: 'fr', name: 'French' },
            { uri: 'es', name: 'Spanish' }
        ];

        const availableLanguages = translationService.listAvailableLanguages(resources, languages);
        assert.deepEqual(availableLanguages, [{ uri: 'es', name: 'Spanish' }], 'Should return the available languages');
    });

    QUnit.test('listAvailableLanguages with empty resources', function (assert) {
        const languages = [
            { uri: 'en', name: 'English' },
            { uri: 'fr', name: 'French' },
            { uri: 'es', name: 'Spanish' }
        ];

        const availableLanguages = translationService.listAvailableLanguages([], languages);
        assert.deepEqual(availableLanguages, languages, 'Should return all the languages');
    });

    QUnit.test('listAvailableLanguages with no languages', function (assert) {
        const availableLanguages = translationService.listAvailableLanguages([], []);
        assert.deepEqual(availableLanguages, [], 'Should return an empty array');
    });

    QUnit.test('listAvailableLanguages with no available languages', function (assert) {
        const resources = [
            {
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'en',
                        literal: null
                    }
                }
            },
            {
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'fr',
                        literal: null
                    }
                }
            }
        ];

        const availableLanguages = translationService.listAvailableLanguages(resources, []);
        assert.deepEqual(availableLanguages, [], 'Should return an empty array');
    });

    QUnit.test('listAvailableLanguages with all languages taken', function (assert) {
        const resources = [
            {
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'en',
                        literal: null
                    }
                }
            },
            {
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'fr',
                        literal: null
                    }
                }
            }
        ];

        const languages = [
            { uri: 'en', name: 'English' },
            { uri: 'fr', name: 'French' }
        ];

        const availableLanguages = translationService.listAvailableLanguages(resources, languages);
        assert.deepEqual(availableLanguages, [], 'Should return an empty array');
    });

    QUnit.test('listTranslatedLanguages', function (assert) {
        const resources = [
            {
                originResourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66bf239f225f1202408161002075ca6e371',
                resourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66e96b4bb3a162024091711430754588114',
                resourceLabel: 'Item 8 (fr-FR)',
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#UniqueIdentifier': {
                        value: 'i66bf239f225f1202408161002075ca6',
                        literal: null
                    },
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'http://www.tao.lu/Ontologies/TAO.rdf#Langfr-FR',
                        literal: 'fr-FR'
                    },
                    'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgress': {
                        value: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusPending',
                        literal: null
                    }
                }
            },
            {
                originResourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66bf239f225f1202408161002075ca6e371',
                resourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66e96bad3d1c8202409171144450caf7160',
                resourceLabel: 'Item 8 (fr-CA)',
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#UniqueIdentifier': {
                        value: 'i66bf239f225f1202408161002075ca6',
                        literal: null
                    },
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'http://www.tao.lu/Ontologies/TAO.rdf#Langfr-CA',
                        literal: 'fr-CA'
                    },
                    'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgress': {
                        value: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusPending',
                        literal: null
                    }
                }
            },
            {
                originResourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66bf239f225f1202408161002075ca6e371',
                resourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66ea8be336b5b20240918081427d3672c75',
                resourceLabel: 'Item 8 (it-IT)',
                metadata: {
                    'http://www.tao.lu/Ontologies/TAO.rdf#UniqueIdentifier': {
                        value: 'i66bf239f225f1202408161002075ca6',
                        literal: null
                    },
                    'http://www.tao.lu/Ontologies/TAO.rdf#Language': {
                        value: 'http://www.tao.lu/Ontologies/TAO.rdf#Langit-IT',
                        literal: 'it-IT'
                    },
                    'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgress': {
                        value: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusPending',
                        literal: null
                    }
                }
            }
        ];
        const languages = [
            {
                uri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langde-DE',
                code: 'de-DE',
                label: 'German',
                orientation: 'ltr'
            },
            {
                uri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
                code: 'en-US',
                label: 'English (USA)',
                orientation: 'ltr'
            },
            {
                uri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langes-ES',
                code: 'es-ES',
                label: 'Spanish (Spain)',
                orientation: 'ltr'
            },
            {
                uri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langfr-CA',
                code: 'fr-CA',
                label: 'French (Canada)',
                orientation: 'ltr'
            },
            {
                uri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langfr-FR',
                code: 'fr-FR',
                label: 'French (France)',
                orientation: 'ltr'
            },
            {
                uri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langit-IT',
                code: 'it-IT',
                label: 'Italian',
                orientation: 'ltr'
            },
            {
                uri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langja-JP',
                code: 'ja-JP',
                label: 'Japanese (Japan)',
                orientation: 'ltr'
            }
        ];

        const expected = [
            {
                originResourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66bf239f225f1202408161002075ca6e371',
                resourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66e96b4bb3a162024091711430754588114',
                languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langfr-FR',
                progressUri: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusPending',
                language: 'French (France)',
                progress: 'Pending'
            },
            {
                originResourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66bf239f225f1202408161002075ca6e371',
                resourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66e96bad3d1c8202409171144450caf7160',
                languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langfr-CA',
                progressUri: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusPending',
                language: 'French (Canada)',
                progress: 'Pending'
            },
            {
                originResourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66bf239f225f1202408161002075ca6e371',
                resourceUri: 'https://tr-enterprise.kitchen.tao/tao.rdf#i66ea8be336b5b20240918081427d3672c75',
                languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langit-IT',
                progressUri: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusPending',
                language: 'Italian',
                progress: 'Pending'
            }
        ];

        const translatedLanguages = translationService.listTranslatedLanguages(resources, languages);
        assert.deepEqual(translatedLanguages, expected, 'Should return the correct languages');
    });
});
