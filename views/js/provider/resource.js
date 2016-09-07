define([
    'jquery',
    'lodash',
    'helpers',
    'core/promise'
], function($, _, helpers, Promise) {
    'use strict';

    return function resourceProviderFactory(type) {

        return {
            getClasses: function getClasses(classUri) {

                return new Promise(function(resolve, reject) {

                    $.ajax({
                        url: helpers._url('getClasses', 'Foo', 'tao'),
                        data: {
                            classUri: classUri
                        }
                    })
                    .success(resolve)
                    .fail(function(xhr) {
                        return reject(new Error(xhr.responseText));
                    });
                });
            }
        };
    };
});
