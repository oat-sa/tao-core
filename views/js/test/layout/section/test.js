define(['jquery', 'layout/section'], function($, section){
        
    module('layout/section');
   
    test('module', function(){
        ok(typeof section === 'object', 'The module expose an object');
    });

    test('init multiple sections', function(){
        var $testScope = $('#qunit-fixture .multiple');

        equal($testScope.length, 1, 'the test scope exists');

        ok(typeof section.init === 'function', 'section has an init function');
        ok(typeof section.init($testScope) === 'object', 'section.init() returns an object');

        ok($('.tab-container li:first', $testScope).hasClass('active'), 'the first opener is active');
        ok(!$('.tab-container li:eq(2)', $testScope).hasClass('active'), 'the 3rd opener is not active');
        ok(!$('.tab-container li:last', $testScope).hasClass('active'), 'the last opener is not active');
        
        ok($('.content-panel:first', $testScope).css('display') !== 'none', 'the first panel is shown');
        ok($('.content-panel:eq(2)', $testScope).css('display') === 'none', 'the 3rd panel is hidden');
        ok($('.content-panel:last', $testScope).css('display') === 'none', 'the last panel is hidden');
    });

    asyncTest('init single sections', function(){
        expect(5);
        var $testScope = $('#qunit-fixture .single');

        equal($testScope.length, 1, 'the test scope exists');

        ok(typeof section.init($testScope) === 'object', 'section.init() returns an object');

        setTimeout(function(){
            ok($('.tab-container li:first', $testScope).hasClass('active'), 'the first opener is active');
            ok($('.tab-container', $testScope).css('display') === 'none', 'the openers are hidden');
            ok($('.content-panel:first', $testScope).css('display') !== 'none', 'the panel is shown');

            start();
        }, 200);
    });

    asyncTest('switch from sections', function(){
        expect(10);
        var $testScope = $('#qunit-fixture .multiple');

        equal($testScope.length, 1, 'the test scope exists');

        ok(typeof section.init($testScope) === 'object', 'section.init() returns an object');

        ok($('.tab-container li:first', $testScope).hasClass('active'), 'the first opener is active');
        ok(!$('.tab-container li:eq(1)', $testScope).hasClass('active'), 'the 2nd opener is not active');
        
        ok($('.content-panel:first', $testScope).css('display') !== 'none', 'the first panel is shown');
        ok($('.content-panel:eq(1)', $testScope).css('display') === 'none', 'the 2nd panel is hidden');

        $('.tab-container li:eq(1)').trigger('click');

        setTimeout(function(){

            ok(!$('.tab-container li:first', $testScope).hasClass('active'), 'the first opener isn\'t active anymore');
            ok($('.tab-container li:eq(1)', $testScope).hasClass('active'), 'the 2nd opener is now active');
            
            ok($('.content-panel:first', $testScope).css('display') === 'none', 'the first panel is now hidden');
            ok($('.content-panel:eq(1)', $testScope).css('display') !== 'none', 'the 2nd panel is now shown');

            start();
        }, 100);
    });
});


