define(function(){
    return {
        encode : function(uri){
            var encoded = uri;
            if (/^http/.test(uri)) {
                encoded = encoded
                            .replace(/:\/\//g, '_2_')
                            .replace(/#/g, '_3_')
                            .replace(/:/g,'_4_')
                            .replace(/\//g,'_1_')
                            .replace(/\./g,'_0_');
                } 
            return encoded;
        },
        decode : function(uri){
            var encoded = uri;
            if (/^http/.test(uri)) {
                encoded = encoded
                            .replace(/_0_/g, '.')
                            .replace(/_1_/g, '/')
                            .replace(/_2_/g, '://')
                            .replace(/_3_/g, '#')
                            .replace(/_4_/g, ':');
                } 
            return encoded;
        }
    };
});


