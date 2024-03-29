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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */
/**
 * @author Christophe Noël <christophe@taotesting.com>
 */
define(['lodash', 'jquery', 'layout/generisRouter'], function(_, $, generisRouter) {
    'use strict';

    var location = window.history.location || window.location;
    var testerUrl = location.href;
    var baseUrlAbs = 'http://tao/tao/Main/index?structure=items&ext=taoItems';
    var baseUrlRel = '/tao/Main/index?structure=items&ext=taoItems';

    generisRouter.init();

    QUnit.module('Module');

    QUnit.test('Module export', function(assert) {
        assert.expect(1);

        assert.ok(typeof generisRouter === 'object', 'The module expose an object');
    });

    QUnit
        .cases.init([
            {title: 'pushSectionState'},
            {title: 'pushNodeState'},
            {title: 'restoreState'},
            {title: 'hasRestorableState'},

            // Eventifier
            {title: 'on'},
            {title: 'off'},
            {title: 'trigger'}
        ])
        .test('Instance API', function(data, assert) {
            assert.expect(1);

            assert.ok(typeof generisRouter[data.title] === 'function', 'instance implements ' + data.title);
        });

    QUnit.module('.pushSectionState()', {
        beforeEach: function(assert) {
            window.history.replaceState(null, '', testerUrl);
        },
        afterEach: function(assert) {
            window.history.replaceState(null, '', testerUrl);
        }
    });

    QUnit
        .cases.init([
            {
                title: 'change the section parameter',
                baseUrl: baseUrlAbs + '&section=manage_items',
                sectionId: 'authoring',
                restoreWith: 'activate',
                expectedUrl: baseUrlRel + '&section=authoring'
            },
            {
                title: 'remove the uri parameter on section change',
                baseUrl: baseUrlAbs + '&section=manage_items' + '&uri=http_2_tao_1_mytao_0_rdf_3_i15151515151515',
                sectionId: 'authoring',
                restoreWith: 'activate',
                expectedUrl: baseUrlRel + '&section=authoring'
            },
            {
                title: 'redirect to resource',
                baseUrl: baseUrlAbs + '&section=manage_items' + '&uri=http_2_tao_1_mytao_0_rdf_3_i15151515151515',
                sectionId: 'manage_items',
                restoreWith: 'activate',
                expectedUrl: baseUrlRel + '&section=manage_items' + '&uri=http_2_tao_1_mytao_0_rdf_3_i15151515151515',
                nodeUri: 'http_2_tao_1_mytao_0_rdf_3_i15151515151515'
            }
        ])
        .test('Push new state in history when section parameter already exists', function(data, assert) {
            var state = window.history.state;
            var ready = assert.async();

            assert.expect(6);

            generisRouter
                .off('.test')
                .on('pushsectionstate.test', function(stateUrl) {
                    state = window.history.state;
                    assert.ok(true, 'pushsectionstate have been called');
                    assert.equal(stateUrl, data.expectedUrl);
                    assert.equal(state.sectionId, data.sectionId, 'section id param has been correctly set');
                    assert.equal(state.restoreWith, data.restoreWith, 'restoreWith param has been correctly set');
                    assert.equal(state.nodeUri, data.nodeUri, 'nodeUri param has been correctly set');
                    ready();
                })
                .on('replacesectionstate.test', function() {
                    assert.ok(false, 'I should not be called');
                    ready();
                });

            assert.equal(state, null, 'state is null');

            generisRouter.pushSectionState(data.baseUrl, data.sectionId, data.restoreWith);
        });

    QUnit
        .cases.init([
            {
                title: 'add the section parameter',
                baseUrl: baseUrlAbs,
                sectionId: 'authoring',
                restoreWith: 'activate',
                expectedUrl: baseUrlRel + '&section=authoring'
            },
            {
                title: 'add the section parameter and keep the uri parameter',
                baseUrl: baseUrlAbs + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i555555555555555',
                sectionId: 'authoring',
                restoreWith: 'activate',
                nodeUri: 'http://tao/mytao.rdf#i555555555555555',
                expectedUrl: baseUrlRel + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i555555555555555' + '&section=authoring'
            }
        ])
        .test('Replace current state when section does not exists', function(data, assert) {
            var state = window.history.state;
            var ready = assert.async();
            assert.expect(6);

            generisRouter
                .off('.test')
                .on('pushsectionstate.test', function() {
                    assert.ok(false, 'I should not be called');
                    ready();
                })
                .on('replacesectionstate.test', function(stateUrl) {
                    state = window.history.state;
                    assert.ok(true, 'replacesectionstate have been called');
                    assert.equal(stateUrl, data.expectedUrl);
                    assert.equal(state.sectionId, data.sectionId, 'section id param has been correctly set');
                    assert.equal(state.restoreWith, data.restoreWith, 'restoreWith param has been correctly set');
                    assert.equal(state.nodeUri, data.nodeUri, 'nodeUri param has been correctly set');
                    ready();
                });

            assert.equal(state, null, 'state is null');

            generisRouter.pushSectionState(data.baseUrl, data.sectionId, data.restoreWith);
        });

    QUnit
        .cases.init([
            {
                title: 'SectionId is the same as the existing section',
                baseUrl: baseUrlAbs + '&section=authoring',
                sectionId: 'authoring'
            },
            {
                title: 'SectionId parameter is missing',
                baseUrl: baseUrlAbs + '&section=authoring'
            }
        ])
        .test('Does not change state', function(data, assert) {
            var ready = assert.async();
            generisRouter
                .off('.test')
                .on('pushsectionstate.test', function() {
                    assert.ok(false, 'I should not be called');
                    ready();
                })
                .on('replacesectionstate.test', function() {
                    assert.ok(false, 'I should not be called');
                    ready();
                });

            generisRouter.pushSectionState(data.baseUrl, data.sectionId);

            assert.ok(_.isNull(window.history.state), 'state has not been updated');
            ready();
        });

    QUnit.module('.pushNodeState()', {
        beforeEach: function(assert) {
            window.history.replaceState(null, '', testerUrl);
        },
        afterEach: function(assert) {
            window.history.replaceState(null, '', testerUrl);
        }
    });

    QUnit
        .cases.init([
            {
                title: 'Change the uri parameter. No section param, no existing state.',
                baseUrl: baseUrlAbs + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i555555555555555',
                nodeUri: 'http://tao/mytao.rdf#i888888888888888',
                expectedUrl: baseUrlRel + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i888888888888888',
                expectedSectionId: '',
                expectedRestoreWith: 'activate',
                expectedNodeUri: 'http://tao/mytao.rdf#i888888888888888',
                setExistingState: _.noop
            },
            {
                title: 'Change the uri parameter. With section param, no existing state.',
                baseUrl: baseUrlAbs + '&section=manage_items' + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i555555555555555',
                nodeUri: 'http://tao/mytao.rdf#i888888888888888',
                expectedUrl: baseUrlRel + '&section=manage_items' + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i888888888888888',
                expectedSectionId: 'manage_items',
                expectedRestoreWith: 'activate',
                expectedNodeUri: 'http://tao/mytao.rdf#i888888888888888',
                setExistingState: _.noop
            },
            {
                title: 'Change the uri parameter. No section param, existing state.',
                baseUrl: baseUrlAbs + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i555555555555555',
                nodeUri: 'http://tao/mytao.rdf#i888888888888888',
                expectedUrl: baseUrlRel + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i888888888888888',
                expectedSectionId: 'authoring',
                expectedRestoreWith: 'show',
                expectedNodeUri: 'http://tao/mytao.rdf#i888888888888888',
                setExistingState: function setExistingState(gr) {
                    gr.pushSectionState(baseUrlAbs, 'authoring', 'show');
                }
            },
            {
                title: 'Change the uri parameter. Section param, existing state, different sections (should never happen. This is only to assert the assessment priority.',
                baseUrl: baseUrlAbs + '&section=manage_items' + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i555555555555555',
                nodeUri: 'http://tao/mytao.rdf#i888888888888888',
                expectedUrl: baseUrlRel + '&section=manage_items' + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i888888888888888',
                expectedSectionId: 'authoring',
                expectedRestoreWith: 'show',
                expectedNodeUri: 'http://tao/mytao.rdf#i888888888888888',
                setExistingState: function setExistingState(gr) {
                    gr.pushSectionState(baseUrlAbs, 'authoring', 'show');
                }
            }
        ])
        .test('Push new state in history when uri parameter already exists', function(data, assert) {
            var state = window.history.state;
            var ready = assert.async();

            assert.expect(6);

            generisRouter
                .off('.test')
                .on('pushnodestate.test', function(stateUrl) {
                    state = window.history.state;
                    assert.ok(true, 'pushnodestate have been called');
                    assert.equal(stateUrl, data.expectedUrl);
                    assert.equal(state.sectionId, data.expectedSectionId, 'section id param has been correctly set');
                    assert.equal(state.restoreWith, data.expectedRestoreWith, 'restoreWith param has been correctly set');
                    assert.equal(state.nodeUri, data.expectedNodeUri, 'nodeUri param has been correctly set');
                    ready();
                })
                .on('replacenodestate.test', function() {
                    assert.ok(false, 'I should not be called');
                    ready();
                });

            assert.equal(state, null, 'state is null');

            data.setExistingState(generisRouter);

            generisRouter.pushNodeState(data.baseUrl, data.nodeUri);
        });

    QUnit
        .cases.init([
            {
                title: 'Add the uri parameter. No section param, no existing state.',
                baseUrl: baseUrlAbs,
                nodeUri: 'http://tao/mytao.rdf#i888888888888888',
                expectedUrl: baseUrlRel + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i888888888888888',
                expectedSectionId: '',
                expectedRestoreWith: 'activate',
                expectedNodeUri: 'http://tao/mytao.rdf#i888888888888888',
                setExistingState: _.noop
            },
            {
                title: 'Add the uri parameter. With section param, no existing state.',
                baseUrl: baseUrlAbs + '&section=manage_items',
                nodeUri: 'http://tao/mytao.rdf#i888888888888888',
                expectedUrl: baseUrlRel + '&section=manage_items' + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i888888888888888',
                expectedSectionId: 'manage_items',
                expectedRestoreWith: 'activate',
                expectedNodeUri: 'http://tao/mytao.rdf#i888888888888888',
                setExistingState: _.noop
            },
            {
                title: 'Add the uri parameter. No section param, existing state.',
                baseUrl: baseUrlAbs,
                nodeUri: 'http://tao/mytao.rdf#i888888888888888',
                expectedUrl: baseUrlRel + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i888888888888888',
                expectedSectionId: 'authoring',
                expectedRestoreWith: 'show',
                expectedNodeUri: 'http://tao/mytao.rdf#i888888888888888',
                setExistingState: function setExistingState(gr) {
                    gr.pushSectionState(baseUrlAbs, 'authoring', 'show');
                }
            },
            {
                title: 'Change the uri parameter. Section param, existing state, different sections (should never happen. This is only to assess priority.',
                baseUrl: baseUrlAbs + '&section=manage_items',
                nodeUri: 'http://tao/mytao.rdf#i888888888888888',
                expectedUrl: baseUrlRel + '&section=manage_items' + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i888888888888888',
                expectedSectionId: 'authoring',
                expectedRestoreWith: 'show',
                expectedNodeUri: 'http://tao/mytao.rdf#i888888888888888',
                setExistingState: function setExistingState(gr) {
                    gr.pushSectionState(baseUrlAbs, 'authoring', 'show');
                }
            }
        ])
        .test('Replace current state when uri parameter does not exists', function(data, assert) {
            var state = window.history.state;
            var ready = assert.async();
            assert.expect(6);

            generisRouter
                .off('.test')
                .on('pushnodestate.test', function() {
                    assert.ok(false, 'I should not be called');
                    ready();
                })
                .on('replacenodestate.test', function(stateUrl) {
                    state = window.history.state;
                    assert.ok(true, 'replacenodestate have been called');
                    assert.equal(stateUrl, data.expectedUrl);
                    assert.equal(state.sectionId, data.expectedSectionId, 'section id param has been correctly set');
                    assert.equal(state.restoreWith, data.expectedRestoreWith, 'restoreWith param has been correctly set');
                    assert.equal(state.nodeUri, data.expectedNodeUri, 'nodeUri param has been correctly set');
                    ready();
                });

            assert.equal(state, null, 'state is null');

            data.setExistingState(generisRouter);

            generisRouter.pushNodeState(data.baseUrl, data.nodeUri);
        });

    QUnit
        .cases.init([
            {
                title: 'Uri parameter is the same',
                baseUrl: baseUrlAbs + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i888888888888888',
                nodeUri: 'http://tao/mytao.rdf#i888888888888888'
            },
            {
                title: 'Uri parameter is missing',
                baseUrl: baseUrlAbs + '&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i888888888888888'
            }
        ])
        .test('Does not change state', function(data, assert) {
            var ready = assert.async();
            generisRouter
                .off('.test')
                .on('pushnodestate.test', function() {
                    assert.ok(false, 'I should not be called');
                    ready();
                })
                .on('replacenodestate.test', function() {
                    assert.ok(false, 'I should not be called');
                    ready();
                });

            generisRouter.pushNodeState(data.baseUrl, data.nodeUri);

            assert.ok(_.isNull(window.history.state), 'state has not been updated');
            ready();
        });

    QUnit.module('Back, same extension (popstate)', {
        beforeEach: function(assert) {
            window.history.replaceState(null, '', testerUrl);
        },
        afterEach: function(assert) {
            window.history.replaceState(null, '', testerUrl);
        }
    });

    QUnit.test('On move back with a section change, trigger the sectionactivate event if previous state was pushed with the "activate" param', function(assert) {
        var ready = assert.async();
        var url1 = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=authoring';
        var url2 = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=manage_items';

        assert.expect(2);

        generisRouter
            .off('.test')
            .on('sectionactivate.test', function(sectionId) {
                assert.ok(true, 'sectionactivate has been called');
                assert.equal(sectionId, 'manage_items', 'correct param is passed to the callback');
                ready();
            })
            .on('sectionshow.test', function() {
                assert.ok(false, 'sectionshow should not be called');
                ready();
            })
            .on('urichange.test', function() {
                assert.ok(false, 'urichange should not be called');
                ready();
            });

        generisRouter.pushSectionState(url1, 'manage_items', 'activate');
        generisRouter.pushSectionState(url2, 'authoring', 'activate');

        window.history.back();
    });

    QUnit.test('On move back with a section change, trigger the sectionshow event if previous state was pushed with the "show" param', function(assert) {
        var ready = assert.async();
        var url1 = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=authoring';
        var url2 = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=manage_items';

        assert.expect(2);

        generisRouter
            .off('.test')
            .on('sectionactivate.test', function() {
                assert.ok(false, 'sectionactivate should not be called');
                ready();
            })
            .on('sectionshow.test', function(sectionId) {
                assert.ok(true, 'sectionshow has been called');
                assert.equal(sectionId, 'manage_items', 'correct param is passed to the callback');
                ready();
            })
            .on('urichange.test', function() {
                assert.ok(false, 'urichange should not be called');
                ready();
            });

        generisRouter.pushSectionState(url1, 'manage_items', 'show');
        generisRouter.pushSectionState(url2, 'authoring', 'show');

        window.history.back();
    });

    QUnit.test('On move back with uri change and no section change, trigger the urichange event', function(assert) {
        var ready = assert.async();
        var url1 = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=manage_items';
        var url2 = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=manage_items&uri=http%3A%2F%2Ftao%2Fmytao.rdf%23i555555555555555';

        assert.expect(2);

        generisRouter
            .off('.test')
            .on('sectionshow.test', function() {
                assert.ok(false, 'sectionshow should not be called');
                ready();
            })
            .on('sectionactivate.test', function() {
                assert.ok(false, 'sectionactivate should not be called');
                ready();
            })
            .on('urichange.test', function(uri) {
                assert.ok(true, 'urichange has been called');
                assert.equal(uri, 'http://tao/mytao.rdf#i111111111111111', 'correct param is passed to the callback');
                ready();
            });

        generisRouter.pushNodeState(url1, 'http://tao/mytao.rdf#i111111111111111');
        generisRouter.pushNodeState(url2, 'http://tao/mytao.rdf#i888888888888888');

        window.history.back();
    });

    QUnit.module('Back, different extension', {
        beforeEach: function(assert) {
            window.history.replaceState(null, '', testerUrl);
        },
        afterEach: function(assert) {
            window.history.replaceState(null, '', testerUrl);
        }
    });

    QUnit.test('On move back with a page reload (= different extension), trigger sectionactivate ', function(assert) {
        var ready = assert.async();
        var url = '/tao/Main/index?structure=tests&ext=taoItems&section=manage_tests';

        assert.expect(2);

        generisRouter
            .off('.test')
            .on('sectionshow.test', function() {
                assert.ok(false, 'sectionshow should not be called');
                ready();
            })
            .on('sectionactivate.test', function(sectionId) {
                assert.ok(true, 'sectionactivate has been called');
                assert.equal(sectionId, 'manage_tests', 'correct param is passed to the callback');
                ready();
            })
            .on('urichange.test', function() {

                assert.ok(false, 'urichange should not be called');
                ready();
            });

        window.history.pushState({
            sectionId: 'manage_tests',
            restoreWith: 'activate'
        }, '', url);

        // When page reloads and there is a restorable state, then .restoreState() gets called
        generisRouter.restoreState();
    });

});
