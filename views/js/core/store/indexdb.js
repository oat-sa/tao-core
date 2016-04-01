
define(['lodash', 'core/promise', 'lib/store/db'], function(_, Promise, db){
    'use strict';


    var server;
    var dbName = 'tao-store';

    var indexDbBackend = function indexDbBackend(id){


        var getServer = function getServer(){
            var options = {
                server: dbName,
                version: 1,
                noServerMethods: true,
                schema : {}
            };
            options.schema[id] = {
                key: {
                    keyPath: 'id',
                    autoIncrement : true
                },
                indexes : {
                    key: { unique: true }
                }
            };

            console.log(options);
            if(server && !server.isClosed()){
                return Promise.resolve(server);
            }
            return db.open(options)

            .then(function(s){
                server = s;
                return Promise.resolve(server);
            })
            .catch(function(err){
                console.error(err);
            });
        };

        if(_.isEmpty(id) || !_.isString(id)){
            throw new TypeError('The store identifier is required');
        }

        return {
            getItem : function getItem(key){
                return getServer().then(function (s){
                    return s.get(id, key);
                });
            },

            setItem :  function setItem(key, value){
                return getServer().then(function (s){

                    console.log(s.isClosed());
                    var entry = {
                        key : key,
                        value : value
                    };
                    //return server.get(id, { key : key }).then(function(result){
                        //if(result)
                            return s.add(id, entry).then(function(res){
                                    console.log(res);
                            });
                        //}
                        //return server.add(id, entry);
                    //});
                });
            },

            removeItem : function removeItem(key){

            },

            clear : function clear(){

            }
        };
   };
    return indexDbBackend;
});
