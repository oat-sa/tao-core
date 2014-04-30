define(['jquery', 'ui/resourcemgr'], function($){

    console.log($('#uri').val())


    $('#launcher').resourcemgr({
        browseUrl   : '/taoItems/ItemContent/files',
        uploadUrl   : '/taoItems/ItemContent/upload',
        deleteUrl   : '/taoItems/ItemContent/delete',
        downloadUrl : '/taoItems/ItemContent/download',
        params : {
            uri : $('#uri').val(),
            lang : 'en-US'
        },
        pathParam : 'path',
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


