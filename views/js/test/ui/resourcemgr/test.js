define(['jquery', 'ui/resourcemgr'], function($) {
    'use strict';

    QUnit.module('Init');

    QUnit.test('Resource manager Loading but not open', function(assert) {
        var ready = assert.async();
        assert.expect(3);
        var $launcher = $('#launcher');

        $launcher.on('create.resourcemgr', function() {
            assert.ok($('#outside-container .resourcemgr').length === 1, 'The resource manager modal is created');
            assert.ok($('#outside-container .modal-bg').length === 1, 'The background is set');
            assert.ok($('#outside-container .resourcemgr').hasClass('opened') === false, 'The modal is hidden');

            ready();

        });

        $launcher.on('open.resourcemgr', function() {
            assert.ok(false, 'This modal should not be open');
        });

        $launcher.resourcemgr({
            params: {
                filters: 'image/gif,audio/mpeg',
                uri: 'http://myUri',
                lang: 'en-US'
            },
            open: false
        });
    });

    QUnit.test('Resource manager Loading with eventBinding', function(assert) {
        var ready = assert.async();
        assert.expect(3);
        var $launcher = $('#launcher');

        $launcher.resourcemgr({
            params: {
                filters: 'image/gif,audio/mpeg',
                uri: 'http://myUri',
                lang: 'en-US'
            },
            open: false,
            select: function() {
                assert.ok(true, 'The resource manager bind correctly the select');
            },
            create: function() {
                assert.ok(true, 'The resource manager bind correctly the create');
                ready();
            },
            close: function() {
                assert.ok(true, 'The resource manager bind correctly the close');
            }

        });

        $('#outside-container .resourcemgr').trigger('select.resourcemgr');
    });

    QUnit.module('Loading');

    QUnit.test('Resource manager loading and open', function(assert) {
        var ready = assert.async();
        assert.expect(3);
        var $launcher = $('#launcher');

        $launcher.on('open.resourcemgr', function() {
            assert.ok($('#outside-container .resourcemgr').length === 1, 'The resource manager modal is created');
            assert.ok($('#outside-container .modal-bg').length === 1, 'The background is set');
            assert.ok($('#outside-container .resourcemgr').css('display') !== 'none', 'The modal is shown');
            ready();
        });
        $launcher.resourcemgr({
            params: {
                filters: 'image/gif,audio/mpeg',
                uri: 'http://myUri',
                lang: 'en-US'
            },
            open: true
        });
    });

    QUnit.test('Resource manager select and close', function(assert) {
        var ready = assert.async();
        assert.expect(1);
        var $launcher = $('#launcher');

        $launcher.on('close.resourcemgr', function() {
            assert.ok(true, 'the modal is closed on select resource');
            ready();

        });
        $launcher.resourcemgr({
            params: {
                filters: 'image/gif,audio/mpeg',
                uri: 'http://myUri',
                lang: 'en-US'
            },
            open: true
        });

        $('#outside-container .resourcemgr').trigger('select.resourcemgr');
    });

    QUnit.test('Resource manager close and reopen', function(assert) {
        var ready = assert.async();
        assert.expect(2);
        var $launcher = $('#launcher');

        $launcher.on('close.resourcemgr', function() {
            assert.ok(true, 'the modal is closed on select resource');

            $launcher.on('open.resourcemgr', function() {
                assert.ok(true, 'the modal is reopen');
                ready();
            });

            $launcher.on('create.resourcemgr', function() {
                assert.ok(true, 'the modal should not be created');
            });

            $launcher.resourcemgr({
                params: {
                    filters: 'image/gif,audio/mpeg',
                    uri: 'http://myUri',
                    lang: 'en-US'
                },
                open: true
            });

        });
        $launcher.resourcemgr({
            params: {
                filters: 'image/gif,audio/mpeg',
                uri: 'http://myUri',
                lang: 'en-US'
            },
            open: true
        });

        $('#outside-container .resourcemgr').trigger('select.resourcemgr');
    });

    QUnit.module('Destroy');

    QUnit.test('ResourceManager destroy', function(assert) {
        var ready = assert.async();
        assert.expect(1);
        var $launcher = $('#launcher');

        $launcher.on('open.resourcemgr', function() {
            $launcher.resourcemgr('destroy');
        });
        $launcher.on('destroy.resourcemgr', function() {
            assert.ok(true, 'resource manager is destoyed');
            ready();
        });

        $launcher.resourcemgr({
            params: {
                filters: 'image/gif,audio/mpeg',
                uri: 'http://myUri',
                lang: 'en-US'
            },
            open: true
        });
    });
});

