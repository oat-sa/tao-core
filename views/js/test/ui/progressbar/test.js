define(['jquery', 'ui/progressbar'], function($) {
    'use strict';

    var containerId = 'mypg';

    QUnit.module('ProgressBar');

    QUnit.test('plugin', function(assert) {
        assert.expect(1);
        assert.ok(typeof $.fn.progressbar === 'function', 'The progressbar plugin is registered');
    });

    QUnit.test('Initialization', function(assert) {
        var ready = assert.async();
        assert.expect(3);

        var $elt = $('#' + containerId);

        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('create.progressbar', function() {

            assert.ok($elt.hasClass('progressbar'), 'the element has the right class');
            assert.equal($elt.find('span').length, 1, 'the sub element has been inserted');

            ready();
        });
        $elt.progressbar();
    });

    QUnit.test('Success style', function(assert) {
        var ready = assert.async();
        assert.expect(3);

        var $elt = $('#' + containerId);

        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('create.progressbar', function() {

            assert.equal($elt.find('span').length, 1, 'the sub element has been inserted');
            assert.ok($elt.hasClass('success'), 'the sub element has the success CSS class');

            ready();
        });
        $elt.progressbar({
            style: 'success'
        });
    });

    QUnit.test('Destroy', function(assert) {
        var ready = assert.async();
        assert.expect(5);

        var $elt = $('#' + containerId);

        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('create.progressbar', function() {

            assert.ok($elt.hasClass('progressbar'), 'the element has the right class');
            assert.equal($elt.find('span').length, 1, 'the sub element has been inserted');

        }).on('destroy.progressbar', function() {

            assert.ok(!$elt.hasClass('progressbar'), 'the element class has been removed');
            assert.equal($elt.find('span').length, 0, 'the sub element has been removed');

            ready();
        });

        $elt.progressbar()
            .progressbar('destroy');
    });

    QUnit.test('update', function(assert) {
        var ready = assert.async();
        assert.expect(3);

        var $elt = $('#' + containerId);

        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('update.progressbar', function(e, value) {
            assert.equal(value, 55, 'the given value matches');
            assert.equal($elt.find('span')[0].style.width, '55%', 'the sub element width matches the value');

            ready();
        });

        $elt.progressbar()
            .progressbar('update', 55);
    });

    QUnit.test('set value', function(assert) {
        var ready = assert.async();
        assert.expect(3);

        var $elt = $('#' + containerId);

        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('update.progressbar', function(e, value) {
            assert.equal(value, 42, 'the given value matches');
            assert.equal($elt.find('span')[0].style.width, '42%', 'the sub element width matches the value');

            ready();
        });

        $elt.progressbar()
            .progressbar('value', 42);
    });

    QUnit.test('get value', function(assert) {
        var ready = assert.async();
        assert.expect(2);

        var $elt = $('#' + containerId);

        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('update.progressbar', function() {

            assert.equal($elt.progressbar('value'), 66, 'Get the current progress value');

            ready();
        });

        $elt.progressbar()
            .progressbar('value', 66);
    });

    QUnit.test('show progress', function(assert) {
        var ready = assert.async();
        assert.expect(2);

        var $elt = $('#' + containerId);

        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('update.progressbar', function(e, value) {
            assert.equal($elt.find('span').text(), '38%', 'the sub element contains the progress text');

            ready();
        });

        $elt.progressbar({showProgress: true})
            .progressbar('update', 38);
    });
});

