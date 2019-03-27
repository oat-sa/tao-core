define(['jquery', 'ui/uploader'], function($) {
    'use strict';

    QUnit.test('plugin', function(assert) {
        assert.expect(1);
        assert.ok(typeof $.fn.uploader === 'function', 'The uploader plugin is registered');
    });

});
