define(['jquery', 'cards',  'ui/incrementer'], function($, cards, incrementer){
    
    
    module('Incrementer Stand Alone Test');
   
    test('plugin', function(){
       expect(1);
       ok(typeof $.fn.incrementer === 'function', 'The Durationer plugin is registered');
    });
   
    asyncTest('initialization', function(){
        expect(4);
        
        var $container = $('#container-1');
        ok($container.length === 1, 'Test the fixture is available');
        
        var $elt = $(':text', $container);
        ok($elt.length === 1, 'Test input is available');
        
        $elt.on('create.incrementer', function(){
            ok(typeof $elt.data('cards.incrementer') === 'object');
            
            var $control = $container.find('.ctrl > a');
            equal($control.length, 2, 'The plugins has created controls');
            
            start();
        });
        $elt.incrementer();
    });
      
     asyncTest('update seconds', function(){
        expect(3);
        
        var $container = $('#container-1');
        ok($container.length === 1, 'Test the fixture is available');
        
        var $elt = $(':text', $container);
        ok($elt.length === 1, 'Test input is available');
        
        $elt.on('create.incrementer', function(){
            $container.find('.ctrl > a.inc').click();
        });
         $elt.on('increment.incrementer', function(){
            equal($elt.val(), 2, "The value has been incremented");
            
            start();
        });
        
        $elt.incrementer({
            min : 0,
            max : 10,
            step: 2
        });
    });
    
     module('Incrementer Data Attr Test');
     
     asyncTest('initialization', function(){
        expect(3);
        
        var $container = $('#container-2');
        ok($container.length === 1, 'Test the fixture is available');
        
        var $elt = $(':text', $container);
        ok($elt.length === 1, 'Test input is available');
        
        $elt.on('create.incrementer', function(){
            $container.find('.ctrl > a.inc').click();
        });
         $elt.on('increment.incrementer', function(){
            equal($elt.val(), 5, "The value has been incremented");
            
            start();
        });
       
        incrementer($container);
    });
});


