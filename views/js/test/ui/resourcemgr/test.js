define(['jquery', 'ui/resourcemgr/main'], function($, resourcemgr){
    
    
    module('Incrementer Stand Alone Test');
   
    test('plugin', function(){
        expect(1);
        ok(typeof resourcemgr === 'function', 'The resourcemgr expose a function');
    });

    test('initialization', function(){
        expect(0);
        resourcemgr($('#main'), '');
    });
});


