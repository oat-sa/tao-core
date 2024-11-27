/*
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
 * Copyright (c) 2024 (original work) Open Assessment Technologies SA
 *
 */
define(['i18n', 'core/request', 'util/url'], function (__, request, urlUtil) {
    'use strict';

    /**
     * @typedef {object} LanguageDefinition
     * @property {string} uri - The URI of the language (ex: http://www.tao.lu/Ontologies/TAO.rdf#Langen-US)
     * @property {string} code - The code of the language (ex: en-US)
     * @property {string} label - The label of the language (ex: English)
     * @property {string} orientation - The orientation of the language (ltr or rtl)
     */

    /**
     * @typedef {object} Metadata
     * @property {string} value - The value of the metadata.
     * @property {string} literal - The literal value of the metadata, can be null.
     */

    /**
     * @typedef {object} Resource
     * @property {string} originResourceUri - The URI of the resource's origin.
     * @property {string} resourceUri - The URI of the resource.
     * @property {string} resourceLabel - The label of the resource.
     * @property {object<Metadata>} metadata - A collection of metadata, indexed by URI.
     */

    /**
     * @typedef {object} ResourceList
     * @property {Resource[]} resources - The ID of the translatable resource.
     */

    /**
     * @typedef {object} ResourceTranslatableStatus
     * @property {string} uri - The ID of the translatable resource.
     * @property {string} type - The resource class type.
     * @property {string} languageUri - The resource language URI.
     * @property {bool} isReadyForTranslation - If a resource is marked as ready for translation.
     * @property {bool} isEmpty - If the resource is empty.
     */

    /**
     * @typedef {object} Translation
     * @property {string} resourceUri - The URI of the translated resource.
     * @property {string} languageUri - The URI of the language.
     * @property {string} language - The label of the language.
     * @property {string} progress - The progress of the translation.
     */

    /**
     * A mapping of URIs to labels for the translation services.
     */
    const labels = Object.freeze({
        'http://www.tao.lu/Ontologies/TAO.rdf#UniqueIdentifier': __('Unique Identifier'),
        'http://www.tao.lu/Ontologies/TAO.rdf#Language': __('Language'),
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationType': __('Translation Type'),
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatus': __('Translation Status'),
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgress': __('Translation Progress'),
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationTypeOriginal': __('Original'),
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationTypeTranslation': __('Translation'),
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatusNotReadyForTranslation': __('Not Ready for Translation'),
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatusReadyForTranslation': __('Ready for Translation'),
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusPending': __('Pending'),
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusTranslating': __('Translating'),
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusTranslated': __('Translated')
    });

    /**
     * A mapping of URIs to keys for the translation services.
     */
    const keys = Object.freeze({
        'http://www.tao.lu/Ontologies/TAO.rdf#UniqueIdentifier': 'uniqueIdentifier',
        'http://www.tao.lu/Ontologies/TAO.rdf#Language': 'language',
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationType': 'translationType',
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatus': 'translationStatus',
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgress': 'translationProgress',
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationTypeOriginal': 'original',
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationTypeTranslation': 'translation',
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatusNotReadyForTranslation': 'notReady',
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatusReadyForTranslation': 'ready',
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusPending': 'pending',
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusTranslating': 'translating',
        'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusTranslated': 'translated'
    });

    /**
     * URIs for the properties available in the translation services.
     */
    const metadata = Object.freeze({
        uniqueIdentifier: 'http://www.tao.lu/Ontologies/TAO.rdf#UniqueIdentifier',
        language: 'http://www.tao.lu/Ontologies/TAO.rdf#Language',
        translationType: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationType',
        translationStatus: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatus',
        translationProgress: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgress'
    });

    /**
     * URIs for the translation types in the translation services.
     */
    const translationType = Object.freeze({
        original: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationTypeOriginal',
        translation: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationTypeTranslation'
    });

    /**
     * URIs for the translation statuses in the translation services.
     */
    const translationStatus = Object.freeze({
        notReady: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatusNotReadyForTranslation',
        ready: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationStatusReadyForTranslation'
    });

    /**
     * URIs for the translation progresses in the translation services.
     */
    const translationProgress = Object.freeze({
        pending: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusPending',
        translating: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusTranslating',
        translated: 'http://www.tao.lu/Ontologies/TAO.rdf#TranslationProgressStatusTranslated'
    });

    return {
        keys,
        labels,
        metadata,
        translationType,
        translationStatus,
        translationProgress,

        /**
         * Gets the translation progress of the resources.
         * @param {Resource[]} resources
         * @returns {string[]}
         */
        getTranslationsProgress(resources) {
            if (!resources || !resources.length) {
                return [];
            }

            return resources.map(resource => {
                if (!resource.metadata || !resource.metadata[metadata.translationProgress]) {
                    return null;
                }
                const uri = resource.metadata[metadata.translationProgress].value;
                return keys[uri] || uri;
            });
        },

        /**
         * Gets the translation language of the resources.
         * @param {Resource[]} resources
         * @returns {Metadata[]}
         */
        getTranslationsLanguage(resources) {
            if (!resources || !resources.length) {
                return [];
            }

            return resources.map(resource => {
                if (!resource.metadata || !resource.metadata[metadata.language]) {
                    return null;
                }
                return resource.metadata[metadata.language];
            });
        },

        /**
         * Lists the languages of the resources.
         * @param {Resource[]} resources
         * @returns {string[]}
         */
        listResourcesLanguages(resources) {
            if (!resources || !resources.length) {
                return [];
            }

            return resources.reduce((acc, resource) => {
                if (!resource.metadata || !resource.metadata[metadata.language]) {
                    return acc;
                }

                const language = resource.metadata[metadata.language].value;
                if (acc.indexOf(language) === -1) {
                    acc.push(language);
                }
                return acc;
            }, []);
        },

        /**
         * Filters the available languages.
         * @param {Resource[]} resources
         * @param {LanguageDefinition[]} languages
         * @returns {LanguageDefinition[]}
         */
        listAvailableLanguages(resources, languages) {
            const resourceLanguages = this.listResourcesLanguages(resources);
            return languages.filter(language => resourceLanguages.indexOf(language.uri) === -1);
        },

        /**
         * Lists the translated languages of the resources.
         * @param {Resource[]} resources
         * @param {LanguageDefinition[]} languages
         * @returns {Translation[]}
         */
        listTranslatedLanguages(resources, languages) {
            const languagesMap = languages.reduce((acc, language) => {
                acc[language.uri] = language.label;
                return acc;
            }, {});

            return resources.reduce((acc, resource) => {
                if (!resource.metadata || !resource.metadata[metadata.language]) {
                    return acc;
                }

                const languageUri = resource.metadata[metadata.language].value;
                let progressUri = '';
                if (resource.metadata[metadata.translationProgress]) {
                    progressUri = resource.metadata[metadata.translationProgress].value;
                }

                acc.push({
                    resourceUri: resource.resourceUri,
                    originResourceUri: resource.originResourceUri,
                    languageUri,
                    progressUri,
                    language: languagesMap[languageUri],
                    progress: labels[progressUri] || ''
                });
                return acc;
            }, []);
        },

        /**
         * Queries the available languages.
         * @returns {Promise<LanguageDefinition[]>}
         */
        getLanguages() {
            return request({
                url: urlUtil.route('index', 'Languages', 'tao'),
                method: 'GET',
                headers: { 'Accept-version': 'v2' },
                noToken: true
            }).then(response => response.data);
        },

        /**
         * Queries information about a translatable resource.
         * @param {string} id - The URI of the resource.
         * @returns {Promise<ResourceList>}
         */
        getTranslatable(id) {
            return request({
                url: urlUtil.route('translatable', 'Translation', 'tao', { id }),
                method: 'GET',
                noToken: true
            }).then(response => response.data);
        },

        /**
         * Queries information about a translatable resource status.
         * @param {string} id - The URI of the resource.
         * @returns {Promise<ResourceTranslatableStatus>}
         */
        getTranslatableStatus(id) {
            return request({
                url: urlUtil.route('status', 'Translation', 'tao', { id }),
                method: 'GET',
                noToken: true
            }).then(response => response.data);
        },

        /**
         * Queries the list of translations for a resource.
         * @param {string|string[]} id - The URI of the resource. It may also be a list of URIs, but in this case the languageUri must also be provided.
         * @param {string|function} [languageUri] - The URI of the language to filter the translations. It may also be a filter function.
         * @param {function} [filter] - A filter function for the translations. When not provided through the languageUri parameter.
         * @returns {Promise<ResourceList>}
         */
        getTranslations(id, languageUri, filter) {
            if (Array.isArray(id)) {
                id = id.join(',');
            }
            const params = { id };
            if (languageUri) {
                if ('function' === typeof languageUri) {
                    filter = languageUri;
                } else {
                    params.languageUri = languageUri;
                }
            }
            return request({
                url: urlUtil.route('translations', 'Translation', 'tao', params),
                method: 'GET',
                noToken: true
            })
                .then(response => response.data)
                .then(data => {
                    if (filter && Array.isArray(data.resources)) {
                        data.resources = data.resources.filter(filter);
                    }
                    return data;
                });
        },

        /**
         * Creates a new translation for a resource.
         * @param {string} id - The URI of the resource.
         * @param {string} languageUri - The URI of the language
         * @param {string} resourceType - The URI of the resource type
         * @returns {Promise<Resource>}
         */
        createTranslation(id, languageUri, resourceType) {
            return request({
                url: urlUtil.route('translate', 'Translation', 'tao'),
                data: { id, languageUri, resourceType },
                method: 'POST',
                noToken: true
            }).then(response => response.data);
        },

        /**
         * Updates the progress of a translation.
         * @param {string} id - The URI of the resource.
         * @param {string} progress - The URI of the progress for the translation.
         * @returns {Promise<Resource>}
         */
        updateTranslation(id, progress) {
            return request({
                url: urlUtil.route('update', 'Translation', 'tao'),
                data: { id, progress },
                method: 'POST',
                noToken: true
            }).then(response => response.data);
        },

        /**
         * Deletes a translation.
         * @param {string} id - The URI of the resource.
         * @param {string} languageUri - The URI of the language.
         * @returns {Promise<Resource>}
         */
        deleteTranslation(id, languageUri) {
            return request({
                url: urlUtil.route('delete', 'Translation', 'tao'),
                data: { id, languageUri },
                method: 'POST',
                noToken: true
            }).then(response => response.data);
        },

        /**
         * Synchronizes the translations.
         * @param {string} id - The URI of the resource.
         * @returns {Promise<Resource>}
         */
        syncTranslation(id) {
            return request({
                url: urlUtil.route('sync', 'Translation', 'tao'),
                data: { id },
                method: 'POST',
                noToken: true
            }).then(response => response.data);
        }
    };
});
