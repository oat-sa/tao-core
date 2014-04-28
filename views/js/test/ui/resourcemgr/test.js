define(['jquery', 'ui/resourcemgr'], function($){


    $('#launcher').resourcemgr({
        url : 'test',
        create : function(e){

            console.log('create');
        },
        select : function(e, uris){
            console.log('select with ', uris);
        }
        
    });     
    
    //module('Incrementer Stand Alone Test');
   
    //test('plugin', function(){
        //expect(1);
        //ok(typeof resourcemgr === 'function', 'The resourcemgr expose a function');
    //});

    //test('initialization', function(){
        //expect(0);
        //resourcemgr($('#main'), '');
    //});
});


