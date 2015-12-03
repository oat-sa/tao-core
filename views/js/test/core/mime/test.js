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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'core/promise',
    'core/mime'
], function($, Promise, mime) {
    'use strict';

    QUnit.module('mime');


    QUnit.test('module', 1, function(assert) {
        assert.equal(typeof mime, 'function', "The mime module exposes a function");
    });


    var resources = [
        { url: 'js/test/core/mime/samples/audio.mp3', type: 'audio/mpeg', error: false, title : 'MP3' },
        { url: 'js/test/core/mime/samples/video.mp4', type: 'video/mp4', error: false, title : 'MP4' },
        { url: 'js/test/core/mime/samples/unknown', type: null, error: true, title : 'Unknown resource' }
    ];

    QUnit
        .cases(resources)
        .asyncTest('mime of ', function(data, assert) {
            QUnit.stop();

            var p = mime(data.url, function(err, type) {
                assert.equal(!!err, data.error, 'The callback accept an error');
                if (!data.error) {
                    assert.equal(type, data.type, 'The callback received the correct MIME type');
                }
                QUnit.start();
            });

            assert.equal(p instanceof Promise, true, 'The mime function return a promise.');

            p.then(function(type) {
                if (data.error) {
                    assert.ok(false, 'The promise must throw an error!');
                } else {
                    assert.equal(type, data.type, 'The promise resolved with the correct MIME type');
                }
                QUnit.start();
            }).catch(function(err) {
                assert.equal(!!err, data.error, 'The promise thrown an error!');
                QUnit.start();
            });
        });
});
