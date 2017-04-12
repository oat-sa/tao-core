/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'ui/resource/tree',
    'json!test/ui/resource/tree/root.json',
    'json!test/ui/resource/tree/node.json'
], function($, resourceTreeFactory, rootData, nodeData) {
    'use strict';

    //var resourceSelectorApi = [
        //{ name : 'init', title : 'init' },
        //{ name : 'destroy', title : 'destroy' },
        //{ name : 'render', title : 'render' },
        //{ name : 'show', title : 'show' },
        //{ name : 'hide', title : 'hide' },
        //{ name : 'enable', title : 'enable' },
        //{ name : 'disable', title : 'disable' },
        //{ name : 'is', title : 'is' },
        //{ name : 'setState', title : 'setState' },
        //{ name : 'getContainer', title : 'getContainer' },
        //{ name : 'getElement', title : 'getElement' },
        //{ name : 'getTemplate', title : 'getTemplate' },
        //{ name : 'setTemplate', title : 'setTemplate' }
    //];

    //QUnit.module('API');


    //QUnit.test('module', function(assert) {
        //QUnit.expect(3);

        //assert.equal(typeof resourceSelector, 'function', "The resourceSelector module exposes a function");
        //assert.equal(typeof resourceSelector(), 'object', "The resourceSelector factory produces an object");
        //assert.notStrictEqual(resourceSelector(), resourceSelector(), "The resourceSelector factory provides a different object on each call");
    //});

    //QUnit
        //.cases(resourceSelectorApi)
        //.test('instance API ', function(data, assert) {
            //var instance = resourceSelector();
            //assert.equal(typeof instance[data.name], 'function', 'The resourceSelector instance exposes a "' + data.title + '" function');
        //});



    QUnit.module('Visual');


    QUnit.asyncTest('playground', function(assert) {

        var container = document.getElementById('visual');
        var config = {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            nodes : rootData
        };


        resourceTreeFactory(container, config)
            .on('query', function(params){
                this.update(nodeData, params);
            })
            .on('render', function(){
                assert.ok(true);
            })
            .on('change', function(selected){
                console.log(selected);
            });



        //var resourceProvider = {

            //getAllClasses : function getAllClasses(){
                //return Promise.resolve(classesData);
            //},


            //getTree : function getTree (classUri){


            //},

            //getResources : function getResources (classUri, pattern, paging){

                //var offset = paging.offset || 0;
                //var size   = paging.size   || 25;

                //return new Promise(function(resolve){
                    //setTimeout(function(){
                        //var result, dataSet, criterions;

                        //if(!_.isEmpty(pattern)){
                            //criterions = _.transform(pattern.trim().split(/\s/), function(acc, criteria) {
                                //var propertyCriteria = criteria.split(':');
                                //if(propertyCriteria.length === 2){
                                    //acc[propertyCriteria[0]] = propertyCriteria[1];
                                //} else if(!_.isEmpty(criteria)) {
                                    //acc.label = criteria;
                                //}
                                //return acc;
                            //}, {});

                            //result = _(searchData[classUri]).filter( function(value){
                                //var match = false;
                                //_.forEach(criterions, function(pat, key){
                                    //if(value[key] && new RegExp(pat).test(value[key])){
                                        //match = true;
                                        //return false;
                                    //}
                                //});
                                //return match;
                            //})
                            //.sortBy('label')
                            //.value();

                        //} else {
                            //result = _.sortBy(searchData[classUri], 'label');
                        //}
                        //dataSet = result.slice(offset, offset + size);

                        //resolve({
                            //total : result.length,
                            //data  : dataSet
                        //});
                    //}, 700);
                //});
            //},

            //getSearchParams : function getSearchParams() {
                //return Promise.resolve({
                    //'http://www.tao.lu/Ontologies/generis.rdf#userFirstName' : 'First Name',
                    //'http://www.tao.lu/Ontologies/generis.rdf#userLastName' : 'Last Name',
                    //'http://www.tao.lu/Ontologies/generis.rdf#login' : 'Login',
                    //'http://www.tao.lu/Ontologies/generis.rdf#userMail' : 'Mail'
                //});
            //}
        //};

        QUnit.expect(1);


    });

});
