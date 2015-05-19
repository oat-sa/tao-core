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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define(['jquery', 'ui/autocomplete', 'lib/simulator/jquery.keystroker'], function($, autocompleteUI, keystroker) {

    'use strict';

    /**
     * Gets an handle to a stopper that terminates an async test after a period of time
     * @param {QUnit.assert} assert
     * @param {String} [message]
     * @param {Number} [delay]
     * @param {Boolean} [result]
     * @returns {Number}
     */
    var openAsyncTestStopper = function(assert, message, delay, result) {
        return setTimeout(function() {
            assert.ok(!!result, message || 'Test never ends');
            QUnit.start();
        }, delay || 1000);
    };

    /**
     * Disposes an async test stopper.
     * @param {Number} handle
     */
    var closeAsyncTestStopper = function(handle) {
        handle && clearTimeout(handle);
    };

    /**
     * Checks the API
     */

    QUnit.module('API');

    QUnit.test('ui/autocomplete module', 3, function(assert) {
        assert.ok(typeof autocompleteUI === 'function', "The ui/autocomplete module exposes a function");
        assert.ok(typeof autocompleteUI() === 'object', "The ui/autocomplete factory produces an object");
        assert.ok(autocompleteUI() !== autocompleteUI(), "The ui/autocomplete factory provides a different object on each call");
    });

    QUnit.test('ui/autocomplete instance API', function(assert) {
        var instance = autocompleteUI();
        var autocompleteApi = [
            'init',
            'destroy',
            'setOptions',
            'trigger',
            'on',
            'off',
            'getElement',
            'getValue',
            'setValue',
            'getLabel',
            'setLabel',
            'getOntology',
            'setOntology',
            'getValueField',
            'setValueField',
            'getLabelField',
            'setLabelField',
            'getIsProvider',
            'setIsProvider',
            'getParamsRoot',
            'setParamsRoot',
            'getParams',
            'setParams',
            'getQueryParam',
            'setQueryParam',
            'getOntologyParam',
            'setOntologyParam',
            'getUrl',
            'setUrl',
            'getType',
            'setType',
            'getDelay',
            'setDelay',
            'getMinChars',
            'setMinChars',
            'enable',
            'disable',
            'hide',
            'clear',
            'clearCache',
            'reset'
        ];
        var len = autocompleteApi.length;
        var name;

        QUnit.expect(len);
        while (len --) {
            name = autocompleteApi[len];
            assert.ok(typeof instance[name] === 'function', "The ui/autocomplete instance exposes a " + name + " function");
        }
    });

    QUnit.test('ui/autocomplete getter/setter', function(assert) {
        var instance = autocompleteUI();
        var expected;

        expected = '12';
        instance.setValue(expected);
        assert.equal(instance.getValue(), expected, 'The ui/autocomplete instance must provide bidirectional access to "value"');

        expected = 'Twelve';
        instance.setLabel(expected);
        assert.equal(instance.getLabel(), expected, 'The ui/autocomplete instance must provide bidirectional access to "label"');

        expected = 'http://www.tao.lu/Ontologies/TAO.rdf#User';
        instance.setOntology(expected);
        assert.equal(instance.getOntology(), expected, 'The ui/autocomplete instance must provide bidirectional access to "ontology"');

        expected = 'value1234';
        instance.setValueField(expected);
        assert.equal(instance.getValueField(), expected, 'The ui/autocomplete instance must provide bidirectional access to "valueField"');

        expected = 'label1234';
        instance.setLabelField(expected);
        assert.equal(instance.getLabelField(), expected, 'The ui/autocomplete instance must provide bidirectional access to "labelField"');

        expected = true;
        instance.setIsProvider(expected);
        assert.equal(instance.getIsProvider(), expected, 'The ui/autocomplete instance must provide bidirectional access to "isProvider"');
        expected = false;
        instance.setIsProvider(expected);
        assert.equal(instance.getIsProvider(), expected, 'The ui/autocomplete instance must provide bidirectional access to "isProvider"');

        expected = 'fragment';
        instance.setQueryParam(expected);
        assert.equal(instance.getQueryParam(), expected, 'The ui/autocomplete instance must provide bidirectional access to "queryParam"');

        expected = 'ontology';
        instance.setOntologyParam(expected);
        assert.equal(instance.getOntologyParam(), expected, 'The ui/autocomplete instance must provide bidirectional access to "ontologyParam"');

        expected = 'params';
        instance.setParamsRoot(expected);
        assert.equal(instance.getParamsRoot(), expected, 'The ui/autocomplete instance must provide bidirectional access to "paramsRoot"');
        assert.equal(instance.getQueryParam(), 'params[fragment]', 'The ui/autocomplete instance must provide adjusted value for "queryParam" when "paramsRoot" is defined');
        assert.equal(instance.getOntologyParam(), 'params[ontology]', 'The ui/autocomplete instance must provide adjusted value for "ontologyParam" when "paramsRoot" is defined');

        expected = 'http://tao.dev/tao/Search/search';
        instance.setUrl(expected);
        assert.equal(instance.getUrl(), expected, 'The ui/autocomplete instance must provide bidirectional access to "url"');

        expected = 'POST';
        instance.setType(expected);
        assert.equal(instance.getType(), expected, 'The ui/autocomplete instance must provide bidirectional access to "type"');

        expected = 'GET';
        instance.setType(null);
        assert.equal(instance.getType(), expected, 'The ui/autocomplete instance must provide default value for "type"');

        expected = 100;
        instance.setDelay(expected);
        assert.equal(instance.getDelay(), expected, 'The ui/autocomplete instance must provide bidirectional access to "delay"');

        expected = 0;
        instance.setDelay(null);
        assert.equal(instance.getDelay(), expected, 'The ui/autocomplete instance must provide default value for "delay"');
        instance.setDelay(-1);
        assert.equal(instance.getDelay(), expected, 'The ui/autocomplete instance must provide default value for "delay"');

        expected = 4;
        instance.setMinChars(expected);
        assert.equal(instance.getMinChars(), expected, 'The ui/autocomplete instance must provide bidirectional access to "minChars"');

        expected = 1;
        instance.setMinChars(null);
        assert.equal(instance.getMinChars(), expected, 'The ui/autocomplete instance must provide default value for "minChars"');
        instance.setMinChars(-1);
        assert.equal(instance.getMinChars(), expected, 'The ui/autocomplete instance must provide default value for "minChars"');

        expected = {
            params: {
                ontology: 'http://www.tao.lu/Ontologies/TAO.rdf#User'
            }
        };
        assert.deepEqual(instance.getParams(), expected, 'The ui/autocomplete instance must provide nested "params" when "paramsRoot" is defined');

        expected = {
            ontology: 'http://www.tao.lu/Ontologies/TAO.rdf#User'
        };
        instance.setParamsRoot(null);
        assert.deepEqual(instance.getParams(), expected, 'The ui/autocomplete instance must provide "params" when "paramsRoot" is not defined');

        expected = {
            ontology: 'http://www.tao.lu/Ontologies/TAO.rdf#User',
            page: 1,
            count: 2
        };
        instance.setParams({
            page: 1,
            count: 2
        });
        assert.deepEqual(instance.getParams(), expected, 'The ui/autocomplete instance must provide bidirectional access to "params"');
    });

    /**
     * Checks the behavior
     */

    QUnit.module('Behavior');

    QUnit.asyncTest('ui/autocomplete install', function(assert) {
        var instance = autocompleteUI('#autocomplete1');
        var element = instance.getElement();
        var stopper;
        var listenerInstalled;
        var eventListener = function() {
            if (listenerInstalled) {
                assert.ok(true, 'The ui/autocomplete instance can handle custom events');
            } else {
                assert.ok(false, 'The ui/autocomplete instance must be able to remove custom events');
            }

            closeAsyncTestStopper(stopper);
            QUnit.start();
        };

        // basic
        assert.ok(typeof instance === 'object', "An installed autocomplete relies on an object");
        assert.ok(!!element && element.length === 1, "The ui/autocomplete instance relies on a nested element");

        // add custom event
        stopper = openAsyncTestStopper(assert, 'The ui/autocomplete instance fails to handle custom events', 250, false);
        instance.on('test', eventListener);
        listenerInstalled = true;
        instance.trigger('test');

        // remove custom event
        QUnit.stop();
        stopper = openAsyncTestStopper(assert, 'The ui/autocomplete instance can remove custom events', 250, true);
        instance.off('test');
        listenerInstalled = false;
        instance.trigger('test');

        // remove the component
        instance.destroy();
    });

    QUnit.asyncTest('ui/autocomplete successful query', function(assert) {
        var instance = autocompleteUI('#autocomplete2', {
            url : 'js/test/ui/autocomplete/test.success.json',

            onSearchStart : function() {
                assert.ok(true, 'The ui/autocomplete instance must fire the searchSearch event when the user inputs a query');

                closeAsyncTestStopper(stopper);
                stopper = openAsyncTestStopper(assert, 'The ui/autocomplete instance fails to handle searchComplete event', 500, false);
            },

            onSearchComplete : function() {
                assert.ok(true, 'The ui/autocomplete instance must fire the searchComplete event after server response');

                closeAsyncTestStopper(stopper);
                stopper = openAsyncTestStopper(assert, 'The ui/autocomplete instance fails to handle selectItem event', 500, false);
                setTimeout(function() {
                    keystroker.keystroke(element, keystroker.keyCode.ENTER);
                }, 250);
            },

            onSelectItem : function() {
                assert.ok(true, 'The ui/autocomplete instance must fire the selectItem event when an item is selected');

                assert.equal(element.val(), "user", 'The ui/autocomplete instance keep the value of the selected item in the textbox');
                assert.equal(instance.getValue(), "http://tao.dev/tao-dev.rdf#i1431522022337107", 'The ui/autocomplete instance must keep the value of the selected item');
                assert.equal(instance.getLabel(), "user", 'The ui/autocomplete instance must keep the label of the selected item');

                closeAsyncTestStopper(stopper);
                QUnit.start();
            }
        });
        var element = instance.getElement();
        var stopper = openAsyncTestStopper(assert, 'The ui/autocomplete instance fails to handle searchStart event', 500, false);

        keystroker.puts(element, "user");
    });

    QUnit.asyncTest('ui/autocomplete failed query', function(assert) {
        var instance = autocompleteUI('#autocomplete3', {
            url : 'js/test/ui/autocomplete/test.blank.json',

            onSearchStart : function() {
                assert.ok(true, 'The ui/autocomplete instance must fire the searchSearch event when the user inputs a query');

                closeAsyncTestStopper(stopper);
                stopper = openAsyncTestStopper(assert, 'The ui/autocomplete instance fails to handle searchComplete event', 500, false);
            },

            onSearchComplete : function() {
                assert.ok(true, 'The ui/autocomplete instance must fire the searchComplete event after server response');

                closeAsyncTestStopper(stopper);
                stopper = openAsyncTestStopper(assert, 'The ui/autocomplete instance must not fire the selectItem event when no item is selectable', 500, true);
                setTimeout(function() {
                    keystroker.keystroke(element, keystroker.keyCode.ENTER);
                }, 250);
            },

            onSelectItem : function() {
                assert.ok(false, 'The ui/autocomplete instance must not fire the selectItem event when no item is selectable');

                closeAsyncTestStopper(stopper);
                QUnit.start();
            }
        });
        var element = instance.getElement();
        var stopper = openAsyncTestStopper(assert, 'The ui/autocomplete instance fails to handle searchStart event', 500, false);

        keystroker.puts(element, "test");
    });

    QUnit.asyncTest('ui/autocomplete as provider', function(assert) {
        var instance = autocompleteUI('#autocomplete4', {
            url : 'js/test/ui/autocomplete/test.success.json',
            isProvider : true,

            onSearchStart : function() {
                assert.ok(true, 'The ui/autocomplete instance must fire the searchSearch event when the user inputs a query');

                closeAsyncTestStopper(stopper);
                stopper = openAsyncTestStopper(assert, 'The ui/autocomplete instance fails to handle searchComplete event', 500, false);
            },

            onSearchComplete : function() {
                assert.ok(true, 'The ui/autocomplete instance must fire the searchComplete event after server response');

                closeAsyncTestStopper(stopper);
                stopper = openAsyncTestStopper(assert, 'The ui/autocomplete instance fails to handle selectItem event', 500, false);
                setTimeout(function() {
                    keystroker.keystroke(element, keystroker.keyCode.ENTER);
                }, 250);
            },

            onSelectItem : function() {
                assert.ok(true, 'The ui/autocomplete instance must fire the selectItem event when an item is selected');

                assert.equal(element.val(), '', 'The ui/autocomplete instance must clear the value of the selected item in the textbox');
                assert.equal(instance.getValue(), "http://tao.dev/tao-dev.rdf#i1431522022337107", 'The ui/autocomplete instance must keep the value of the selected item');
                assert.equal(instance.getLabel(), "user", 'The ui/autocomplete instance must keep the label of the selected item');

                closeAsyncTestStopper(stopper);
                QUnit.start();
            }
        });
        var element = instance.getElement();
        var stopper = openAsyncTestStopper(assert, 'The ui/autocomplete instance fails to handle searchStart event', 500, false);

        keystroker.puts(element, "user");
    });

});
