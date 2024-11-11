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
define(function () {
    'use strict';

    function getMockData(data = {}) {
        return Object.assign(
            {
                ready: true,
                resourcesLanguages: [],
                availableLanguages: [],
                translatedLanguages: [],
                resourcesLanguages: [],
                languages: [],
                translatable: [],
                translations: [],
                createTranslation: { resourceUri: 'i12345' }
            },
            data
        );
    }

    function getMockDefault() {
        return {
            isReadyForTranslation() {
                return this.data.ready;
            },
            listAvailableLanguages() {
                return this.data.availableLanguages;
            },
            listTranslatedLanguages() {
                return this.data.translatedLanguages;
            },
            listResourcesLanguages() {
                return this.data.resourcesLanguages;
            },
            getLanguages() {
                return Promise.resolve(this.data.languages);
            },
            getTranslatable() {
                return Promise.resolve({ resources: this.data.translatable });
            },
            getTranslations() {
                return Promise.resolve({ resources: this.data.translations });
            },
            createTranslation() {
                return Promise.resolve(this.data.createTranslation);
            }
        };
    }

    return Object.assign(
        {
            data: getMockData(),
            setMockData(data = {}) {
                this.data = getMockData(data);
            },
            resetMock() {
                Object.assign(this, getMockDefault());
                this.data = getMockData();
            }
        },
        getMockDefault()
    );
});
