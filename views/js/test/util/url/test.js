define(['util/url'], function(urlUtil){

    QUnit.module('API');

    QUnit.test('util api', 2, function(assert){
        assert.ok(typeof urlUtil === 'object', "The urlUtil module exposes an object");
        assert.ok(typeof urlUtil.parse === 'function', "urlUtil exposes a parse method");
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
            console.log(result);
            assert.ok(typeof result === 'object', 'The result is an object');
            for(key in data.expected){
                assert.equal(result[key], data.expected[key], key + ' has the expected value');
            }
        });
});


