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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
define(['urlParser'], function(UrlParser) {
    'use strict';

    var url  = 'http://example.com:3000/extension/module/action?p1=v1&p2=v2#hash';
    var url2 = 'https://example.com/extension/module/action.php?a=1';
    var url3 = 'https://example.com/extension/module/action?a=1';
    var url4 = 'https://example.com?p=a&c=b';

    var base64Wav = 'data:audio/wav;base64,UklGRhwMAABXQVZFZm10IBAAAAABAAEAgD4AAIA+AAABAAgAZGF0Ya4LAACAgICAgICAgICAgICAgICAgICAgICAgICAf3hxeH+AfXZ1eHx6dnR5fYGFgoOKi42aloubq6GOjI2Op7ythXJ0eYF5aV1AOFFib32HmZSHhpCalIiYi4SRkZaLfnhxaWptb21qaWBea2BRYmZTVmFgWFNXVVVhaGdbYGhZbXh1gXZ1goeIlot1k6yxtKaOkaWhq7KonKCZoaCjoKWuqqmurK6ztrO7tbTAvru/vb68vbW6vLGqsLOfm5yal5KKhoyBeHt2dXBnbmljVlJWUEBBPDw9Mi4zKRwhIBYaGRQcHBURGB0XFxwhGxocJSstMjg6PTc6PUxVV1lWV2JqaXN0coCHhIyPjpOenqWppK6xu72yxMu9us7Pw83Wy9nY29ve6OPr6uvs6ezu6ejk6erm3uPj3dbT1sjBzdDFuMHAt7m1r7W6qaCupJOTkpWPgHqAd3JrbGlnY1peX1hTUk9PTFRKR0RFQkRBRUVEQkdBPjs9Pzo6NT04Njs+PTxAPzo/Ojk6PEA5PUJAQD04PkRCREZLUk1KT1BRUVdXU1VRV1tZV1xgXltcXF9hXl9eY2VmZmlna3J0b3F3eHyBfX+JgIWJiouTlZCTmpybnqSgnqyrqrO3srK2uL2/u7jAwMLFxsfEv8XLzcrIy83JzcrP0s3M0dTP0drY1dPR1dzc19za19XX2dnU1NjU0dXPzdHQy8rMysfGxMLBvLu3ta+sraeioJ2YlI+MioeFfX55cnJsaWVjXVlbVE5RTktHRUVAPDw3NC8uLyknKSIiJiUdHiEeGx4eHRwZHB8cHiAfHh8eHSEhISMoJyMnKisrLCszNy8yOTg9QEJFRUVITVFOTlJVWltaXmNfX2ZqZ21xb3R3eHqAhoeJkZKTlZmhpJ6kqKeur6yxtLW1trW4t6+us7axrbK2tLa6ury7u7u9u7vCwb+/vr7Ev7y9v8G8vby6vru4uLq+tri8ubi5t7W4uLW5uLKxs7G0tLGwt7Wvs7avr7O0tLW4trS4uLO1trW1trm1tLm0r7Kyr66wramsqaKlp52bmpeWl5KQkImEhIB8fXh3eHJrbW5mYGNcWFhUUE1LRENDQUI9ODcxLy8vMCsqLCgoKCgpKScoKCYoKygpKyssLi0sLi0uMDIwMTIuLzQ0Njg4Njc8ODlBQ0A/RUdGSU5RUVFUV1pdXWFjZGdpbG1vcXJ2eXh6fICAgIWIio2OkJGSlJWanJqbnZ2cn6Kkp6enq62srbCysrO1uLy4uL+/vL7CwMHAvb/Cvbq9vLm5uba2t7Sysq+urqyqqaalpqShoJ+enZuamZqXlZWTkpGSkpCNjpCMioqLioiHhoeGhYSGg4GDhoKDg4GBg4GBgoGBgoOChISChISChIWDg4WEgoSEgYODgYGCgYGAgICAgX99f398fX18e3p6e3t7enp7fHx4e3x6e3x7fHx9fX59fn1+fX19fH19fnx9fn19fX18fHx7fHx6fH18fXx8fHx7fH1+fXx+f319fn19fn1+gH9+f4B/fn+AgICAgH+AgICAgIGAgICAgH9+f4B+f35+fn58e3t8e3p5eXh4d3Z1dHRzcXBvb21sbmxqaWhlZmVjYmFfX2BfXV1cXFxaWVlaWVlYV1hYV1hYWVhZWFlaWllbXFpbXV5fX15fYWJhYmNiYWJhYWJjZGVmZ2hqbG1ub3Fxc3V3dnd6e3t8e3x+f3+AgICAgoGBgoKDhISFh4aHiYqKi4uMjYyOj4+QkZKUlZWXmJmbm52enqCioqSlpqeoqaqrrK2ur7CxsrGys7O0tbW2tba3t7i3uLe4t7a3t7i3tre2tba1tLSzsrKysbCvrq2sq6qop6alo6OioJ+dnJqZmJeWlJKSkI+OjoyLioiIh4WEg4GBgH9+fXt6eXh3d3V0c3JxcG9ubWxsamppaWhnZmVlZGRjYmNiYWBhYGBfYF9fXl5fXl1dXVxdXF1dXF1cXF1cXF1dXV5dXV5fXl9eX19gYGFgYWJhYmFiY2NiY2RjZGNkZWRlZGVmZmVmZmVmZ2dmZ2hnaGhnaGloZ2hpaWhpamlqaWpqa2pra2xtbGxtbm1ubm5vcG9wcXBxcnFycnN0c3N0dXV2d3d4eHh5ent6e3x9fn5/f4CAgIGCg4SEhYaGh4iIiYqLi4uMjY2Oj5CQkZGSk5OUlJWWlpeYl5iZmZqbm5ybnJ2cnZ6en56fn6ChoKChoqGio6KjpKOko6SjpKWkpaSkpKSlpKWkpaSlpKSlpKOkpKOko6KioaKhoaCfoJ+enp2dnJybmpmZmJeXlpWUk5STkZGQj4+OjYyLioqJh4eGhYSEgoKBgIB/fn59fHt7enl5eHd3dnZ1dHRzc3JycXBxcG9vbm5tbWxrbGxraWppaWhpaGdnZ2dmZ2ZlZmVmZWRlZGVkY2RjZGNkZGRkZGRkZGRkZGRjZGRkY2RjZGNkZWRlZGVmZWZmZ2ZnZ2doaWhpaWpra2xsbW5tbm9ub29wcXFycnNzdHV1dXZ2d3d4eXl6enp7fHx9fX5+f4CAgIGAgYGCgoOEhISFhoWGhoeIh4iJiImKiYqLiouLjI2MjI2OjY6Pj46PkI+QkZCRkJGQkZGSkZKRkpGSkZGRkZKRkpKRkpGSkZKRkpGSkZKRkpGSkZCRkZCRkI+Qj5CPkI+Pjo+OjY6Njo2MjYyLjIuMi4qLioqJiomJiImIh4iHh4aHhoaFhoWFhIWEg4SDg4KDgoKBgoGAgYCBgICAgICAf4CAf39+f35/fn1+fX59fHx9fH18e3x7fHt6e3p7ent6e3p5enl6enl6eXp5eXl4eXh5eHl4eXh5eHl4eXh5eHh3eHh4d3h4d3h3d3h4d3l4eHd4d3h3eHd4d3h3eHh4eXh5eHl4eHl4eXh5enl6eXp5enl6eXp5ent6ent6e3x7fHx9fH18fX19fn1+fX5/fn9+f4B/gH+Af4CAgICAgIGAgYCBgoGCgYKCgoKDgoOEg4OEg4SFhIWEhYSFhoWGhYaHhoeHhoeGh4iHiIiHiImIiImKiYqJiYqJiouKi4qLiouKi4qLiouKi4qLiouKi4qLi4qLiouKi4qLiomJiomIiYiJiImIh4iIh4iHhoeGhYWGhYaFhIWEg4OEg4KDgoOCgYKBgIGAgICAgH+Af39+f359fn18fX19fHx8e3t6e3p7enl6eXp5enl6enl5eXh5eHh5eHl4eXh5eHl4eHd5eHd3eHl4d3h3eHd4d3h3eHh4d3h4d3h3d3h5eHl4eXh5eHl5eXp5enl6eXp7ent6e3p7e3t7fHt8e3x8fHx9fH1+fX59fn9+f35/gH+AgICAgICAgYGAgYKBgoGCgoKDgoOEg4SEhIWFhIWFhoWGhYaGhoaHhoeGh4aHhoeIh4iHiIeHiIeIh4iHiIeIiIiHiIeIh4iHiIiHiIeIh4iHiIeIh4eIh4eIh4aHh4aHhoeGh4aHhoWGhYaFhoWFhIWEhYSFhIWEhISDhIOEg4OCg4OCg4KDgYKCgYKCgYCBgIGAgYCBgICAgICAgICAf4B/f4B/gH+Af35/fn9+f35/fn1+fn19fn1+fX59fn19fX19fH18fXx9fH18fXx9fH18fXx8fHt8e3x7fHt8e3x7fHt8e3x7fHt8e3x7fHt8e3x7fHt8e3x8e3x7fHt8e3x7fHx8fXx9fH18fX5+fX59fn9+f35+f35/gH+Af4B/gICAgICAgICAgICAgYCBgIGAgIGAgYGBgoGCgYKBgoGCgYKBgoGCgoKDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KDgoOCg4KCgoGCgYKBgoGCgYKBgoGCgYKBgoGCgYKBgoGCgYKBgoGCgYKBgoGCgYKBgoGBgYCBgIGAgYCBgIGAgYCBgIGAgYCBgIGAgYCBgIGAgYCAgICBgIGAgYCBgIGAgYCBgIGAgYCBgExJU1RCAAAASU5GT0lDUkQMAAAAMjAwOC0wOS0yMQAASUVORwMAAAAgAAABSVNGVBYAAABTb255IFNvdW5kIEZvcmdlIDguMAAA'

    QUnit.test('parser structure', function(assert) {
        QUnit.expect(4);

        assert.ok(typeof UrlParser === 'function');
        assert.ok(typeof UrlParser.prototype.get === 'function');
        assert.ok(typeof UrlParser.prototype.getPaths === 'function');
        assert.ok(typeof UrlParser.prototype.getParams === 'function');
    });

    QUnit.test('parsing', function(assert) {
        var parser;
        QUnit.expect(2);

        parser = new UrlParser(url);
        assert.equal(parser.url, url);
        assert.deepEqual(parser.data, {
            hash: "#hash",
            host: "example.com:3000",
            hostname: "example.com",
            pathname: "/extension/module/action",
            port: "3000",
            protocol: "http:",
            search: "?p1=v1&p2=v2"
        });
    });

    QUnit.test('get parts', function(assert) {
        var parser;
        QUnit.expect(7);

        parser = new UrlParser(url);
        assert.equal(parser.get('hash'), "#hash");
        assert.equal(parser.get('host'), "example.com:3000");
        assert.equal(parser.get('hostname'), "example.com");
        assert.equal(parser.get('pathname'), "/extension/module/action");
        assert.equal(parser.get('port'), "3000");
        assert.equal(parser.get('protocol'), "http:");
        assert.equal(parser.get('search'), "?p1=v1&p2=v2");
    });

    QUnit.test('get params', function(assert) {
        var parser;
        QUnit.expect(1);

        parser = new UrlParser(url);
        assert.deepEqual(parser.getParams(), {
            'p1': 'v1',
            'p2': 'v2'
        });
    });

    QUnit.test('get paths', function(assert) {
        var parser;
        QUnit.expect(1);

        parser = new UrlParser(url);
        assert.deepEqual(parser.getPaths(), [
            'extension',
            'module',
            'action'
        ]);
    });

    QUnit.test('getUrl', function(assert) {
        QUnit.expect(4);

        assert.equal(new UrlParser(url).getUrl(), url);
        assert.equal(new UrlParser(url2).getUrl(), url2);
        assert.equal(new UrlParser(url3).getUrl(), url3);
        assert.equal(new UrlParser(url4).getUrl(), "https://example.com/?p=a&c=b"); //slash is added
    });

    QUnit.test('getShortUrl', function(assert) {
        var parser;
        QUnit.expect(2);

        parser = new UrlParser(url);
        assert.equal(parser.getUrl(['host', 'params', 'hash']), '/extension/module/action');
        assert.equal(parser.getUrl(['params', 'hash']), 'http://example.com:3000/extension/module/action');
    });

    QUnit.test('getBaseUrl', function(assert) {
        var parser;
        QUnit.expect(1);

        parser = new UrlParser(url2);
        assert.equal(parser.getBaseUrl(), 'https://example.com/extension/module/');
    });

    QUnit.test('changeParam', function(assert) {
        var parsed3;
        var parsed4;
        QUnit.expect(2);

        parsed3 = new UrlParser(url3);
        parsed3.setParams({
            'b': '2'
        });
        assert.equal(parsed3.getUrl(), "https://example.com/extension/module/action?b=2");

        parsed4 = new UrlParser(url4);
        parsed4.addParam('b', '4');
        assert.equal(parsed4.getUrl(), "https://example.com/?p=a&c=b&b=4");
    });

    QUnit.cases([{
        title : 'different paths',
        url1 : 'http://example.com/foo',
        url2 : 'http://example.com/bar',
        expected : true
    }, {
        title : 'wrong url type',
        url1 : 'http://example.com/foo',
        url2 : ['http://example.com/bar'],
        expected : new TypeError('Invalid url format')
    }, {
        title : 'wrong url type',
        url1 : 'http://example.com/foo',
        url2 : { url : 'http://example.com/bar'},
        expected : new TypeError('Invalid url format')
    }, {
        title : 'http vs https',
        url1 : 'http://example.com/foo',
        url2 : 'https://example.com/foo',
        expected : false
    }, {
        title : 'default port',
        url1 : 'http://example.com/foo',
        url2 : 'http://example.com:80/foo',
        expected : true
    }, {
        title : 'default port https',
        url1 : 'https://example.com:443/foo',
        url2 : 'https://example.com/foo',
        expected : true
    }, {
        title : 'default port https',
        url1 : 'https://example.com:443/foo',
        url2 : 'https://example.com/foo',
        expected : true
    }, {
        title : 'different ports',
        url1 : 'http://example.com:80/foo',
        url2 : 'http://example.com:8000/foo',
        expected : false
    }, {
        title : 'data url',
        url1 : 'http://example.com/foo',
        url2 : base64Wav,
        expected : true
    }, {
        title : 'data url',
        url1 : base64Wav,
        url2 : 'http://example.com/foo',
        expected : true
    }, {
        title : 'parsed url',
        url1 : 'http://example.com/foo',
        url2 : new UrlParser('http://example.com/bar'),
        expected : true
    }, {
        title : 'current window',
        url1 : 'http://example.com/foo',
        expected : false
    }]).test('sameDomain ', function(data, assert){
        var parser;
        QUnit.expect(1);

        parser = new UrlParser(data.url1);
        if(data.expected instanceof Error){
            assert.throws(function(){
                parser.sameDomain(data.url2);
            }, data.expected.name, data.expected.message);
        } else {
            assert.equal(parser.sameDomain(data.url2), data.expected);
        }
    });
});
