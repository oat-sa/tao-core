define(['jquery', 'ui/mediasizer'], function($) {


    module('MediaSizer Stand Alone Test');

    test('plugin', function() {
        expect(1);
        console.log($.fn)
        ok(typeof $.fn.mediasizer === 'function', 'The MediaSizer plugin is registered');
    });

    asyncTest('Initialization', function() {
        expect(4);

        var $container = $('#qunit-fixture-tmp');
        ok($container.length === 1, 'Test the fixture is available');

        var $elt = $('#media-sizer-container', $container);
        ok($elt.length === 1, 'MediaSizer link is available');

        var $target = $('#cat-container-natural img', $container);
        ok($target.length === 1, 'Target is available');

        $elt.on('create.mediasizer', function() {
            var data = $elt.data('ui.mediasizer');
            ok(typeof data === 'object', 'The element is running the plugin');

            start();
        });
        $elt.mediasizer({
            target: $target
        });
    });



});