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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

/**
 * Test the module util/url
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['util/url', 'context'], function(urlUtil, context){
    'use strict';

    var parseDataProvider;
    var isAbsoluteDataProvider;
    var isB64DataProvider;
    var attributesDataProvider;
    var buildDataProvider;
    var routeDataProvider;

    QUnit.module('API');

    QUnit.test('util api', function(assert){
        QUnit.expect(8);

        assert.ok(typeof urlUtil === 'object', "The urlUtil module exposes an object");
        assert.ok(typeof urlUtil.parse === 'function', "urlUtil exposes a parse method");
        assert.ok(typeof urlUtil.isAbsolute === 'function', "urlUtil exposes a isAbsolute method");
        assert.ok(typeof urlUtil.isRelative === 'function', "urlUtil exposes a isRelative method");
        assert.ok(typeof urlUtil.isBase64 === 'function', "urlUtil exposes a isBase64 method");
        assert.ok(typeof urlUtil.build === 'function', "urlUtil exposes a build method");
        assert.ok(typeof urlUtil.encodeAsXmlAttr === 'function', "urlUtil exposes a encodeAsXmlAttr method");
        assert.ok(typeof urlUtil.route === 'function', "urlUtil exposes a route method");
    });


    QUnit.module('Parse');

    parseDataProvider = [{
        title    : 'absolute URL',
        url      : 'http://tao.localdomain/test/test.html',
        expected : { host : 'tao.localdomain', path : '/test/test.html', protocol : 'http', port : '' },
    }, {
        title    : 'absolute  URL with port and params',
        url      : 'http://tao.localdomain:8080/test/test.html?coverage=true&run=test1',
        expected : { host : 'tao.localdomain', path : '/test/test.html', protocol : 'http', port : '8080', queryString : 'coverage=true&run=test1' },
    }, {
        title    : 'absolute  URL with SSL and a hash',
        url      : 'https://tao.localdomain/test/test.html#foo',
        expected : { host : 'tao.localdomain', path : '/test/test.html', protocol : 'https', hash : 'foo' },
    }, {
        title    : 'custom protocol resource',
        url      : 'taomedia://tao.localdomain/getFile.php',
        expected : { host : 'tao.localdomain', path : '/getFile.php', protocol : 'taomedia', file : 'getFile.php', 'directory' : '/' },
    }, {
        title    : 'relative URL from root',
        url      : '/tao/proxy/getFile.php',
        expected : { host : '', path : '/tao/proxy/getFile.php', protocol : '' },
    }, {
        title    : 'relative URL from current',
        url      : 'css/style.css',
        expected : { host : '', path : 'css/style.css', file : 'style.css', directory : 'css/' },
    }];

    QUnit
        .cases(parseDataProvider)
        .test('parse ', function(data, assert){
            var key;
            var result = urlUtil.parse(data.url);
            assert.ok(typeof result === 'object', 'The result is an object');
            for(key in data.expected){
                assert.equal(result[key], data.expected[key], key + ' has the expected value');
            }
        });


    QUnit.module('isSomething');

    isAbsoluteDataProvider = [{
        title    : 'absolute URL',
        url      : 'http://tao.localdomain/test/test.html',
        absolute : true,
    }, {
        title    : 'absolute  URL with port and params',
        url      : 'http://tao.localdomain:8080/test/test.html?coverage=true&run=test1',
        absolute : true,
    }, {
        title    : 'absolute  URL with SSL and a hash',
        url      : 'https://tao.localdomain/test/test.html#foo',
        absolute : true,
    }, {
        title    : 'absolute URL no protocol',
        url      : '//tao.localdomain/test/test.html',
        absolute : true,
    }, {
        title    : 'custom protocol resource',
        url      : 'taomedia://tao.localdomain/getFile.php',
        absolute : true,
    }, {
        title    : 'relative URL from root',
        url      : '/tao/proxy/getFile.php',
        absolute : false,
    }, {
        title    : 'relative URL from dir',
        url      : 'css/style.css',
        absolute : false,
    }, {
        title    : 'relative only file name',
        url      : 'style.css',
        absolute : false,
    }, {
        title    : 'relative URL from ./',
        url      : './style.css',
        absolute : false
    }];

    QUnit
        .cases(isAbsoluteDataProvider)
        .test('isAbsolute ', function(data, assert){
            assert.equal(urlUtil.isAbsolute(data.url), data.absolute, 'The URL ' + data.url + ' ' + (data.absolute ? 'is' : 'is not') + ' absolute');
            assert.equal(urlUtil.isAbsolute(urlUtil.parse(data.url)), data.absolute, 'The parsed URL ' + data.url + ' ' + (data.absolute ? 'is' : 'is not') + ' absolute');
        });

    QUnit
        .cases(isAbsoluteDataProvider)
        .test('isRelative ', function(data, assert){
            assert.equal(urlUtil.isRelative(data.url), !data.absolute, 'The URL ' + data.url + ' ' + (!data.absolute ? 'is' : 'is not') + ' relative');
            assert.equal(urlUtil.isRelative(urlUtil.parse(data.url)), !data.absolute, 'The parsed URL ' + data.url + ' ' + (!data.absolute ? 'is' : 'is not') + ' relative');
        });

    isB64DataProvider = [{
        title    : 'absolute URL',
        url      : 'http://tao.localdomain/test/test.html',
        b64      : false,
    }, {
        title    : 'relative URL',
        url      : '/data/base64',
        b64      : false,
    }, {
        title    : 'custom protocol',
        url      : 'data://tao.localdomain/test/test.html#foo',
        b64      : false,
    }, {
        title    : 'base64 image',
        url      : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFiSURBVBgZpcEhbpRRGIXh99x7IU0asGBJWEIdCLaAqcFiCArFCkjA0KRJF0EF26kkFbVVdEj6/985zJ0wBjfp8ygJD6G3n358fP3m5NvtJscJYBObchEHx6QKJ6SKsnn6eLm7urr5/PP76cU4eXVy/ujouD074hDHd5s6By7GZknb3P7mUH+WNLZGKnx595JDvf96zTQSM92vRYA4lMEEO5RNraHWUDH3FV48f0K5mAYJk5pQQpqIgixaE1JDKtRDd2OsYfJaTKNcTA2IBIIesMAOPdDUGYJSqGYml5lGHHYkSGhAJBBIkAoWREAT3Z3JLqZhF3uS2EloQCQ8xLBxoAEWO7aZxros7EgISIIkwlZCY6s1OlAJTWFal5VppMzUgbAlQcIkiT0DXSI2U2ymYZs9AWJL4n+df3pncsI0bn5dX344W05dhctUFbapZcE2ToiLVHBMbGymS7aUhIdoPNBf7Jjw/gQ77u4AAAAASUVORK5CYII=',
        b64      : true,
    }];

    QUnit
        .cases(isB64DataProvider)
        .test('isBase64 ', function(data, assert){
            assert.equal(urlUtil.isBase64(data.url), data.b64, 'The URL ' + (data.b64 ? 'is' : 'is not') + ' encoded in base 64');
            assert.equal(urlUtil.isBase64(urlUtil.parse(data.url)), data.b64, 'The URL ' + (data.b64 ? 'is' : 'is not') + ' encoded in base 64');
        });


    QUnit.module('encodeAsXmlAttr');

    attributesDataProvider = [
        {
            title: 'string allowed characters only',
            url: 'téstïg',
            encoded: 'téstïg'
        }, {
            title: 'string with one encodable >',
            url: 'te<st',
            encoded: 'te%3Cst'
        }
        , {
            title: 'string with one encodable <',
            url: 'te>st',
            encoded: 'te%3Est'
        }, {
            title: 'string with one encodable &',
            url: 'te&st',
            encoded: 'te%26st'
        }, {
            title: 'string with multiply encodable',
            url: 'te&s<t',
            encoded: 'te%26s%3Ct'
        }
    ];

    QUnit
        .cases(attributesDataProvider)
        .test('encodeAsXmlAttr ', function (data, assert) {
            assert.equal(urlUtil.encodeAsXmlAttr(data.url), data.encoded);
            assert.equal(decodeURIComponent(data.encoded), data.url);
        });


    QUnit.module('Build URL');

    buildDataProvider = [{
        title    : 'no params',
    }, {
        title    : 'string path',
        path      : 'http://tao.localdomain:8080/test/test.html',
        expected : 'http://tao.localdomain:8080/test/test.html'
    }, {
        title    : 'array path',
        path      : ['http://tao.localdomain:8080', 'test', 'test.html'],
        expected : 'http://tao.localdomain:8080/test/test.html'
    }, {
        title    : 'array path with dupe slashes',
        path      : ['http://tao.localdomain:8080/', '/test', 'foo/' , '/test.html'],
        expected : 'http://tao.localdomain:8080/test/foo/test.html'
    }, {
        title    : 'path and params',
        path      : 'http://tao.localdomain:8080/test/test.html',
        params      : { foo : true, bar : 'baz'},
        expected : 'http://tao.localdomain:8080/test/test.html?foo=true&bar=baz'
    }, {
        title    : 'path with params and params',
        path      : 'http://tao.localdomain:8080/test/test.html?moo=noob',
        params      : { foo : true, bar : 'baz'},
        expected : 'http://tao.localdomain:8080/test/test.html?moo=noob&foo=true&bar=baz'
    }, {
        title    : 'path and params to encode',
        path      : 'http://tao.localdomain:8080/test/test.html',
        params      : { foo : 'f o oBAR! +/ 1'},
        expected : 'http://tao.localdomain:8080/test/test.html?foo=f%20o%20oBAR!%20%2B%2F%201'
    },
    {
        title    : 'path with object params to encode',
        path      : 'http://tao.localdomain:8080/test/test.html',
        params      : { foo : {bar: 'f o oBAR! +/ 1', foo2: 23}},
        expected : 'http://tao.localdomain:8080/test/test.html?foo[bar]=f%20o%20oBAR!%20%2B%2F%201&foo[foo2]=23&'
    },
    {
        title    : 'path with array inside object params to encode',
        path      : 'http://tao.localdomain:8080/test/test.html',
        params      : { foo : ['f o oBAR! +/ 1', 23]},
        expected : 'http://tao.localdomain:8080/test/test.html?foo=f%20o%20oBAR!%20%2B%2F%201%2C23'
    }];

    QUnit
        .cases(buildDataProvider)
        .test('from ', function(data, assert){
            var result = urlUtil.build(data.path, data.params);
            assert.equal(result, data.expected, 'The URL is built');
        });


    QUnit.module('TAO route URLs', {
        setup: function setup(){
            context.root_url = 'http://tao.lu/';
        }
    });

    routeDataProvider = [{
        title    : 'no params',
        exception : true
    }, {
        title    : 'action only',
        action   :  'index',
        exception : true
    }, {
        title    : 'action & controller only',
        action   :  'index',
        controller   :  'Foo',
        exception : true
    }, {
        title    : 'basic route',
        action   :  'index',
        controller   :  'Foo',
        extension   :  'taoFoo',
        result : 'http://tao.lu/taoFoo/Foo/index'
    }, {
        title    : 'basic route with other root',
        action   :  'index',
        controller   :  'Foo',
        extension   :  'taoFoo',
        rootUrl   :  'https://oat.com/tao',
        result : 'https://oat.com/tao/taoFoo/Foo/index'
    }, {
        title    : 'basic route with other root and slashes ',
        action   :  '/index',
        controller   :  '/Foo/',
        extension   :  'taoFoo/',
        rootUrl   :  'https://oat.com/tao/',
        result : 'https://oat.com/tao/taoFoo/Foo/index'
    }, {
        title    : 'basic route with params',
        action   :  'index',
        controller   :  'Foo',
        extension   :  'taoFoo',
        params      : { foo : true, bar : 'baz'},
        result : 'http://tao.lu/taoFoo/Foo/index?foo=true&bar=baz'
    }, {
        title    : 'basic route with params to encode',
        action   :  'index',
        controller   :  'Foo',
        extension   :  'taoFoo',
        params      : { foo : 'f o oBAR! +/ 1'},
        result : 'http://tao.lu/taoFoo/Foo/index?foo=f%20o%20oBAR!%20%2B%2F%201'
    }];

    QUnit
        .cases(routeDataProvider)
        .test('route ', function(data, assert){
            var result;
            if(data.exception){
                assert.throws(function(){
                    urlUtil.route(data.action, data.controller, data.extension, data.params, data.rootUrl);
                }, TypeError, 'The given parameter set is forbidden');
            } else {
                result = urlUtil.route(data.action, data.controller, data.extension, data.params, data.rootUrl);
                assert.equal(result, data.result, 'The URL matches');
            }
        });
});


