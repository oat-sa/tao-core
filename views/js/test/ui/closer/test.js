define(['jquery', 'cards', 'ui/closer'], function($, cards, closer){
    
    
    module('Closer Stand Alone Test');
   
    test('plugin', function(){
       expect(1);
       ok(typeof $.fn.closer === 'function', 'The Closer plugin is registered');
    });
   
    asyncTest('Initialization', function(){
        expect(4);
        
        var $container = $('#container-1');
        ok($container.length === 1, 'Test the fixture is available');
        
        var $elt = $('.closer', $container);
        ok($elt.length === 1, 'Closer link is available');
        
        var $target = $('.content', $container);
        ok($target.length === 1, 'Target is available');
        
        $elt.on('create.closer', function(){
            ok(typeof $elt.data('cards.closer') === 'object', 'The element is runing the plugin');
            start();
        });
        $elt.closer({ 
            target : $target,
            confirm : false
        });
    });
    
    asyncTest('Closing', function(){
        expect(4);
        
        var $container = $('#container-1');
        ok($container.length === 1, 'Test the fixture is available');
        
        var $elt = $('.closer', $container);
        ok($elt.length === 1, 'Closer link is available');
        
        var $target = $('.content', $container);
        ok($target.length === 1, 'Target is available');
        
        $elt.on('create.closer', function(){
            $elt.trigger('click');
        });
        
         $elt.on('closed.closer', function(){
            ok($('.content', $container).length === 0, 'Target doesn\'t exists anymore');
             
            start();
        });
        $elt.closer({ 
            target : $target,
            confirm : false
        });
    });
    
    
    module('Closer Data Attr Test');
     
     asyncTest('Initialization', function(){
        expect(4);
        
        //prevent the confirm message to lock the test
        window.confirm = function(){
            return true;
        };
        
        var $container = $('#container-2');
        ok($container.length === 1, 'Test the fixture is available');
        
        var $elt = $('.closer', $container);
        ok($elt.length === 1, 'Closer link is available');
        
        var $target = $('.content', $container);
        ok($target.length === 1, 'Target is available');
        
        $elt.on('closed.closer', function(){
            ok($('.content', $container).length === 0, 'Target doesn\'t exists anymore');
            start();
        });
        
        closer($container);
        $elt.trigger('click');
    });
   
});


