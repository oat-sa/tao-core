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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
define([
    'jquery',
    'util/download'
], function($, download){
    'use strict';

    var testObject = {
        text: 'This should be serialized and downloaded'
    };

    QUnit.module('util/download');

    QUnit.test('module', function(assert) {
        QUnit.expect(1);
        assert.equal(typeof download, 'function', "The download module exposes a function");
    });

    QUnit.test('Standard usage', function(assert){
        assert.ok(download('QunitDownloadObject.json', testObject), 'The download function ran (object content)');
        assert.equal($('body > a').length, 0, 'No link was left behind in the DOM');
    });

    QUnit.test('Non-standard usage', function(assert){
        assert.ok(download('QunitDownloadEmptyString.json', ''), 'The download function ran (empty string content)');
        assert.ok(download('QunitDownloadNull.json', null), 'The download function ran (null content)');
    });

    QUnit.test('Mis-usage', function(assert){
        assert.throws(function(){
            download('');
        }, TypeError, 'Invalid filename');
        assert.throws(function(){
            download('QunitDownloadUndefined.json');
        }, TypeError, 'Invalid content');
    });

    QUnit.module('Visual test');

    $("#visual-test button").on('click', function() {
        download('QunitDownloadObjectManual.json', testObject);
    });

});


