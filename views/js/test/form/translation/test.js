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

define(['jquery', 'form/translation', 'services/translation'], function (
    $,
    translationFormFactory,
    translationService
) {
    'use strict';

    const $fixture = $('#qunit-fixture');

    const languages = [
        {
            uri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langar-arb',
            code: 'ar-arb',
            label: 'Arabic',
            orientation: 'rtl'
        },
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

    const resourcesLanguages = ['http://www.tao.lu/Ontologies/TAO.rdf#Langen-US'];

    const translations = [
        {
            resourceUri: 'http://www.tao.lu/tao.rdf#i66ed86fbf2c862024092014301929944a63',
            languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langar-arb',
            language: 'Arabic',
            progress: 'Pending'
        }
    ];

    /**
     * Creates a timed out callback for async tests.
     * @param {function} assert - The QUnit assert function.
     * @param {number} timeout - The timeout in milliseconds.
     * @returns {function} - A done callback with a timeout.
     */
    function asyncTest(assert, timeout = 250) {
        const done = assert.async();
        const th = setTimeout(() => {
            assert.ok(false, 'Timeout');
            done();
        }, timeout);
        return () => {
            clearTimeout(th);
            done();
        };
    }

    QUnit.module('translationForm API', {
        beforeEach() {
            $fixture.empty();
        }
    });

    QUnit.test('module', function (assert) {
        assert.expect(3);

        assert.equal(typeof translationFormFactory, 'function', 'The translation module exposes a function');
        assert.equal(typeof translationFormFactory($fixture), 'object', 'The translation factory produces an object');
        assert.notStrictEqual(
            translationFormFactory($fixture),
            translationFormFactory($fixture),
            'The translation factory provides a different object on each call'
        );
    });

    QUnit.cases
        .init([
            { title: 'init' },
            { title: 'destroy' },
            { title: 'render' },
            { title: 'setSize' },
            { title: 'show' },
            { title: 'hide' },
            { title: 'enable' },
            { title: 'disable' },
            { title: 'is' },
            { title: 'setState' },
            { title: 'getContainer' },
            { title: 'getElement' },
            { title: 'getTemplate' },
            { title: 'setTemplate' },
            { title: 'getConfig' }
        ])
        .test('inherited API ', function (data, assert) {
            var instance = translationFormFactory($fixture);
            assert.expect(1);
            assert.equal(
                typeof instance[data.title],
                'function',
                `The translation instance exposes a "${data.title}" function`
            );
        });

    QUnit.cases
        .init([{ title: 'on' }, { title: 'off' }, { title: 'trigger' }, { title: 'spread' }])
        .test('event API ', function (data, assert) {
            var instance = translationFormFactory($fixture);
            assert.expect(1);
            assert.equal(
                typeof instance[data.title],
                'function',
                `The translation instance exposes a "${data.title}" function`
            );
        });

    QUnit.cases
        .init([
            { title: 'getData' },
            { title: 'prepareGridData' },
            { title: 'createTranslation' },
            { title: 'editTranslation' },
            { title: 'deleteTranslation' },
            { title: 'setControlsState' },
            { title: 'updateLanguagesList' },
            { title: 'updateTranslationsList' }
        ])
        .test('instance API ', function (data, assert) {
            var instance = translationFormFactory($fixture);
            assert.expect(1);
            assert.equal(
                typeof instance[data.title],
                'function',
                `The translation instance exposes a "${data.title}" function`
            );
        });

    QUnit.module('translationForm behavior', {
        beforeEach() {
            translationService.resetMock();
            $fixture.empty();
        }
    });

    QUnit.test('ready', function (assert) {
        const done = asyncTest(assert);

        assert.expect(1);

        translationFormFactory($fixture)
            .on('ready', function () {
                assert.ok(true, 'The translation instance can be initialized');
                done();
            })
            .on('error', function () {
                assert.ok(false, 'The translation instance cannot be initialized');
                done();
            });
    });

    QUnit.test('not ready for translation', function (assert) {
        const done = asyncTest(assert);

        translationService.setMockData({ ready: false });

        translationFormFactory($fixture)
            .on('ready', function () {
                assert.ok(true, 'The translation instance can be initialized');

                assert.equal($fixture.find('.translations-create').length, 0, 'no translation form');
                assert.equal($fixture.find('.translations-list').length, 0, 'no translations list');
                assert.equal($fixture.find('.translations-not-ready').length, 1, 'placeholder message');
                done();
            })
            .on('error', function () {
                assert.ok(false, 'The translation instance cannot be initialized');
                done();
            });
    });

    QUnit.test('no translation yet', function (assert) {
        const done = asyncTest(assert);

        translationService.setMockData({
            availableLanguages: languages,
            resourcesLanguages,
            languages: languages,
            status: {
                languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langar-arb',
                isEmpty: false,
                isReadyForTranslation: true,
            }
        });

        translationFormFactory($fixture)
            .on('ready', function () {
                assert.ok(true, 'The translation instance can be initialized');

                assert.equal($fixture.find('.translations-create').length, 1, 'translation form');
                assert.equal(
                    $fixture.find('.translations-create select option').length,
                    languages.length,
                    'available languages'
                );
                assert.equal($fixture.find('.translations-list').length, 1, 'translations list');
                assert.equal($fixture.find('.translations-list > *').length, 0, 'empty translations list');
                assert.equal($fixture.find('.translations-not-ready').length, 0, 'no placeholder message');
                done();
            })
            .on('error', function (error) {
                console.log(error);
                assert.ok(false, 'The translation instance cannot be initialized');
                done();
            });
    });

    QUnit.test('create translation missing language', function (assert) {
        const done = asyncTest(assert);

        translationService.setMockData({
            availableLanguages: languages,
            resourcesLanguages,
            languages: languages,
            status: {
                languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langar-arb',
                isEmpty: false,
                isReadyForTranslation: true,
            }
        });

        translationFormFactory($fixture)
            .on('ready', function () {
                assert.ok(true, 'The translation instance can be initialized');

                assert.equal($fixture.find('.translations-create').length, 1, 'translation form');
                assert.equal(
                    $fixture.find('.translations-create select option').length,
                    languages.length,
                    'available languages'
                );
                assert.equal($fixture.find('.translations-list').length, 1, 'translations list');
                assert.equal($fixture.find('.translations-list > *').length, 0, 'empty translations list');
                assert.equal($fixture.find('.translations-not-ready').length, 0, 'no placeholder message');

                $fixture.find('.translations-create button').click();
                setTimeout(function () {
                    assert.equal($('.modal').length, 1, 'error message');
                    assert.equal($('.modal .modal-body button').length, 1, 'only one button');
                    $('.modal button.ok').click();
                    done();
                }, 10);
            })
            .on('error', function (error) {
                console.log(error);
                assert.ok(false, 'The translation instance cannot be initialized');
                done();
            });
    });

    QUnit.test('create translation cancel', function (assert) {
        const done = asyncTest(assert);

        translationService.setMockData({
            availableLanguages: languages,
            resourcesLanguages,
            languages: languages,
            status: {
                languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
                isEmpty: false,
                isReadyForTranslation: true,
            }
        });

        translationFormFactory($fixture)
            .on('ready', function () {
                assert.ok(true, 'The translation instance can be initialized');

                assert.equal($fixture.find('.translations-create').length, 1, 'translation form');
                assert.equal(
                    $fixture.find('.translations-create select option').length,
                    languages.length,
                    'available languages'
                );
                assert.equal($fixture.find('.translations-list').length, 1, 'translations list');
                assert.equal($fixture.find('.translations-list > *').length, 0, 'empty translations list');
                assert.equal($fixture.find('.translations-not-ready').length, 0, 'no placeholder message');

                $fixture.find('.translations-create select').val(languages[0].uri);
                $fixture.find('.translations-create button').click();
                setTimeout(function () {
                    assert.equal($('.modal').length, 1, 'confirmation message');
                    assert.equal($('.modal .modal-body button').length, 2, '2 buttons');
                    $('.modal button.cancel').click();
                    done();
                }, 10);
            })
            .on('create', function () {
                assert.ok(false, 'A translation has been created');
            })
            .on('error', function (error) {
                console.log(error);
                assert.ok(false, 'The translation instance cannot be initialized');
                done();
            });
    });

    QUnit.test('create translation', function (assert) {
        const done = asyncTest(assert);

        translationService.setMockData({
            availableLanguages: languages,
            resourcesLanguages,
            languages: languages,
            status: {
                languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langen-US',
                isEmpty: false,
                isReadyForTranslation: true,
            }
        });

        translationFormFactory($fixture)
            .on('ready', function () {
                assert.ok(true, 'The translation instance can be initialized');

                assert.equal($fixture.find('.translations-create').length, 1, 'translation form');
                assert.equal(
                    $fixture.find('.translations-create select option').length,
                    languages.length,
                    'available languages'
                );
                assert.equal($fixture.find('.translations-list').length, 1, 'translations list');
                assert.equal($fixture.find('.translations-list > *').length, 0, 'empty translations list');
                assert.equal($fixture.find('.translations-not-ready').length, 0, 'no placeholder message');

                translationService.setMockData({
                    translatedLanguages: translations,
                });

                $fixture.find('.translations-create select').val(languages[0].uri);
                $fixture.find('.translations-create button').click();
                setTimeout(function () {
                    assert.equal($('.modal').length, 1, 'confirmation message');
                    assert.equal($('.modal .modal-body button').length, 2, '2 buttons');
                    $('.modal button.ok').click();
                }, 10);
            })
            .after('create', function (uri, language) {
                assert.ok(true, 'A translation has been created');
                assert.equal(uri, 'i12345', 'The expected uri is passed');
                assert.equal(language, languages[0].uri, 'The expected language is passed');
                assert.equal(
                    $fixture.find(`.translations-create option[value="${language}"]`).length,
                    0,
                    'language removed from the list'
                );
                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${language}"]`).length,
                    1,
                    'translation added to the list'
                );
            })
            .on('edit', function (uri, language) {
                assert.ok(true, 'The translation will be edited');
                assert.equal(uri, 'i12345', 'The expected uri is passed');
                assert.equal(language, languages[0].uri, 'The expected language is passed');
                done();
            })
            .on('error', function (error) {
                console.log(error);
                assert.ok(false, 'The translation instance cannot be initialized');
                done();
            });
    });

    QUnit.test('list translations', function (assert) {
        const done = asyncTest(assert);

        translationService.setMockData({
            availableLanguages: languages,
            languages: languages,
            translatedLanguages: translations,
            resourcesLanguages,
            status: {
                languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langar-arb',
                isEmpty: false,
                isReadyForTranslation: true,
            }
        });

        translationFormFactory($fixture)
            .on('ready', function () {
                assert.ok(true, 'The translation instance can be initialized');

                assert.equal($fixture.find('.translations-create').length, 1, 'translation form');
                assert.equal(
                    $fixture.find('.translations-create select option').length,
                    languages.length,
                    'available languages'
                );
                assert.equal($fixture.find('.translations-list').length, 1, 'translations list');
                assert.equal($fixture.find('.translations-not-ready').length, 0, 'no placeholder message');

                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"]`).length,
                    1,
                    'translation listed'
                );
                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"] button.edit`)
                        .length,
                    1,
                    'translation editable'
                );
                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"] button.delete`)
                        .length,
                    0,
                    'translation not deletable'
                );

                done();
            })
            .on('error', function (error) {
                console.log(error);
                assert.ok(false, 'The translation instance cannot be initialized');
                done();
            });
    });

    QUnit.test('all translated', function (assert) {
        const done = asyncTest(assert);

        translationService.setMockData({
            translatedLanguages: translations,
            resourcesLanguages,
            status: {
                languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langar-arb',
                isEmpty: false,
                isReadyForTranslation: true,
            }
        });

        translationFormFactory($fixture)
            .on('ready', function () {
                assert.ok(true, 'The translation instance can be initialized');

                assert.equal($fixture.find('.translations-create').length, 0, 'translation form');
                assert.equal($fixture.find('.translations-list').length, 1, 'translations list');
                assert.equal($fixture.find('.translations-not-ready').length, 0, 'no placeholder message');

                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"]`).length,
                    1,
                    'translation listed'
                );
                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"] button.edit`)
                        .length,
                    1,
                    'translation editable'
                );
                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"] button.delete`)
                        .length,
                    0,
                    'translation not deletable'
                );

                done();
            })
            .on('error', function (error) {
                console.log(error);
                assert.ok(false, 'The translation instance cannot be initialized');
                done();
            });
    });

    QUnit.test('edit translation', function (assert) {
        const done = asyncTest(assert);

        translationService.setMockData({
            availableLanguages: languages,
            languages: languages,
            translatedLanguages: translations,
            resourcesLanguages,
            status: {
                languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langar-arb',
                isEmpty: false,
                isReadyForTranslation: true,
            }
        });

        translationFormFactory($fixture)
            .on('ready', function () {
                assert.ok(true, 'The translation instance can be initialized');

                assert.equal($fixture.find('.translations-create').length, 1, 'translation form');
                assert.equal(
                    $fixture.find('.translations-create select option').length,
                    languages.length,
                    'available languages'
                );
                assert.equal($fixture.find('.translations-list').length, 1, 'translations list');
                assert.equal($fixture.find('.translations-not-ready').length, 0, 'no placeholder message');

                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"]`).length,
                    1,
                    'translation listed'
                );
                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"] button.edit`)
                        .length,
                    1,
                    'translation editable'
                );
                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"] button.delete`)
                        .length,
                    0,
                    'translation not deletable'
                );

                $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"] button.edit`).click();
            })
            .on('edit', function (uri, language) {
                assert.ok(true, 'The translation will be edited');
                assert.equal(
                    uri,
                    'http://www.tao.lu/tao.rdf#i66ed86fbf2c862024092014301929944a63',
                    'The expected uri is passed'
                );
                assert.equal(language, languages[0].uri, 'The expected language is passed');
                done();
            })
            .on('error', function (error) {
                console.log(error);
                assert.ok(false, 'The translation instance cannot be initialized');
                done();
            });
    });

    QUnit.test('delete translation', function (assert) {
        const done = asyncTest(assert);

        translationService.setMockData({
            availableLanguages: languages,
            languages: languages,
            translatedLanguages: translations,
            resourcesLanguages,
            status: {
                languageUri: 'http://www.tao.lu/Ontologies/TAO.rdf#Langar-arb',
                isEmpty: false,
                isReadyForTranslation: true,
            }
        });

        translationFormFactory($fixture, { allowDeletion: true })
            .on('ready', function () {
                assert.ok(true, 'The translation instance can be initialized');

                assert.equal($fixture.find('.translations-create').length, 1, 'translation form');
                assert.equal(
                    $fixture.find('.translations-create select option').length,
                    languages.length,
                    'available languages'
                );
                assert.equal($fixture.find('.translations-list').length, 1, 'translations list');
                assert.equal($fixture.find('.translations-not-ready').length, 0, 'no placeholder message');

                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"]`).length,
                    1,
                    'translation listed'
                );
                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"] button.edit`)
                        .length,
                    1,
                    'translation editable'
                );
                assert.equal(
                    $fixture.find(`.translations-list tr[data-item-identifier="${languages[0].uri}"] button.delete`)
                        .length,
                    1,
                    'translation deletable'
                );

                $fixture
                    .find(`.translations-list tr[data-item-identifier="${languages[0].uri}"] button.delete`)
                    .click();

                setTimeout(function () {
                    assert.equal($('.modal').length, 1, 'confirmation message');
                    assert.equal($('.modal .modal-body button').length, 2, '2 buttons');
                    $('.modal button.ok').click();
                }, 10);
            })
            .on('delete', function (uri, language) {
                assert.ok(true, 'The translation will be deleted');
                assert.equal(
                    uri,
                    'http://www.tao.lu/tao.rdf#i66ed86fbf2c862024092014301929944a63',
                    'The expected uri is passed'
                );
                assert.equal(language, languages[0].uri, 'The expected language is passed');
                done();
            })
            .on('error', function (error) {
                console.log(error);
                assert.ok(false, 'The translation instance cannot be initialized');
                done();
            });
    });
});
