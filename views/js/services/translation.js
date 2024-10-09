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
        labels,
        metadata,
        translationType,
        translationStatus,
        translationProgress,

        /**
         * Tells whether the resources are ready for translation.
         * @param {Resource[]} resources
         * @returns {boolean}
         */
        isReadyForTranslation(resources) {
            if (!resources || !resources.length) {
                return false;
            }

            return resources.some(
                resource =>
                    resource.metadata &&
                    resource.metadata[metadata.translationStatus] &&
                    resource.metadata[metadata.translationStatus].value === translationStatus.ready
            );
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
                    resourceUri: resource.originResourceUri || resource.resourceUri,
                    languageUri,
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
         * Queries the list of translations for a resource.
         * @param {string} id - The URI of the resource.
         * @returns {Promise<ResourceList>}
         */
        getTranslations(id) {
            return request({
                url: urlUtil.route('translations', 'Translation', 'tao', { id }),
                method: 'GET',
                noToken: true
            }).then(response => response.data);
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
        }
    };
});
