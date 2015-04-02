define(['jquery'], function($){
    
    return {
        html : function(html){
        	return document.createElement( 'a' ).appendChild( 
        	        document.createTextNode( html ) ).parentNode.innerHTML;
        }
    }
    
});