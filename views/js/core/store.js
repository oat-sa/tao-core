define(['core/promise', 'lib/store/db'], function(Promise, db){
    'use strict';


    var store = function store(id){

        return {
            getItem : function getItem(key){

            },

            setItem :  function setItem(key, value){

            },

            removeItem : function removeItem(key){

            },

            clear : function clear(){

            }
        };
    };

    return store;
});
