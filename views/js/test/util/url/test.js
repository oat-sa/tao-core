define(['util/url'], function(urlUtil){

    QUnit.module('API');

    QUnit.test('util api', 4, function(assert){
        assert.ok(typeof urlUtil === 'object', "The urlUtil module exposes an object");
        assert.ok(typeof urlUtil.parse === 'function', "urlUtil exposes a parse method");
        assert.ok(typeof urlUtil.isAbsolute === 'function', "urlUtil exposes a isAbsolute method");
        assert.ok(typeof urlUtil.isRelative === 'function', "urlUtil exposes a isRelative method");
    });

    QUnit.module('Parse');

    var parseDataProvider = [{
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


    QUnit.module('isAbsolute/isRelative');

    var isAbsoluteDataProvider = [{
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
});


