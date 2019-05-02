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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
define(['jquery', 'lodash', 'lib/jquery.mockjax/jquery.mockjax','uri', 'generis.tree.select'], function($, _, ajaxMock, uri, GenerisTreeSelectClass) {
    'use strict';

    QUnit.module('generis.tree.select');

    var $fixture = $('#qunit-fixture');
    var fixture = 'qunit-fixture';
    var fixtureVisible = 'qunit-fixture-visible';

    $.mockjaxSettings.logger = null;
    $.mockjaxSettings.responseTime = 1;

    QUnit.test('generis.tree.select initialization usual and paginated', function(assert) {
        var done = assert.async();
        var rootNode = 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject';
        var selected = [];
        var testTakers1000 = {"data":"Test-taker","type":"class","attributes":{"id":"http_2_www_0_tao_0_lu_1_Ontologies_1_TAOSubject_0_rdf_3_Subject","class":"node-class","data-uri":"http:\/\/www.tao.lu\/Ontologies\/TAOSubject.rdf#Subject","data-classUri":null,"data-signature":"4837dfe9a9ac93af9b404605843ac4bc31a16e4f898075a132a718c1c04d5406"},"state":"open","children":[{"data":"Generation yeGf","type":"class","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529018304924","class":"node-class","data-uri":"http:\/\/localhost:88\/#i1556529018304924","data-classUri":"http:\/\/www.tao.lu\/Ontologies\/TAOSubject.rdf#Subject","data-signature":"994b71c8921c9a75beef2d9395e1f7fd757a1fe202fa30ffc2cefcd36a5e98fe"},"state":"open","children":[{"data":"Test-Taker  0","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529019327625","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529019327625","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"dff3eff0857ea118c317c01f76f736bc3930f18b1afca81bafdeb3d2bcb6f534"}},{"data":"Test-Taker  1","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529019723426","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529019723426","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"8d813c7e5d1ad20dc31fd5d32f34b4a3c9a9b8ab6cf3c48cbb7c148b33949eed"}},{"data":"Test-Taker  10","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529021538635","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529021538635","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"7ccb3b451f2f435ce35008bb2112d40af8572cb199ab99d7ced9ad9951ac5b63"}},{"data":"Test-Taker  100","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290355669125","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290355669125","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"cf9735098a9ef2a33170d2deac6c5156e404fd6d26f7f4d3b4723759eb606b65"}},{"data":"Test-Taker  101","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529035778126","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529035778126","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"012ec45aa31d943f59a9b819d63dc3b4ea216e11ed487af6820f2fdf3a3a10ca"}},{"data":"Test-Taker  102","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290356882127","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290356882127","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"f6a7319336205ae4b1e75131e1115d691834aaddf7c6f6550b12dac6ecd00576"}},{"data":"Test-Taker  103","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290362076128","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290362076128","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"9b4960b4df95020c34e3a92b6e182dbf8895fc2c91fbdb5ef20351293febd716"}},{"data":"Test-Taker  104","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290369733129","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290369733129","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"130022af77f553fd0cc125159c0a353242a13da7841e554f47fd8b55bb9f3231"}},{"data":"Test-Taker  105","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290362440130","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290362440130","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"5af77f61d488d3f7482a73c764df232f7965301156c670a894d0e89adb9b2d0a"}},{"data":"Test-Taker  106","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290363000131","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290363000131","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"a06020054544ed2fcfe17be9bc07efe85ad51ab0ddfb8a06b46cdf7143d58ec2"}}],"count":1000}],"count":0};
        var testTakers10 = {"data":"Test-taker","type":"class","attributes":{"id":"http_2_www_0_tao_0_lu_1_Ontologies_1_TAOSubject_0_rdf_3_Subject","class":"node-class","data-uri":"http:\/\/www.tao.lu\/Ontologies\/TAOSubject.rdf#Subject","data-classUri":null,"data-signature":"4837dfe9a9ac93af9b404605843ac4bc31a16e4f898075a132a718c1c04d5406"},"state":"open","children":[{"data":"Generation yeGf","type":"class","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529018304924","class":"node-class","data-uri":"http:\/\/localhost:88\/#i1556529018304924","data-classUri":"http:\/\/www.tao.lu\/Ontologies\/TAOSubject.rdf#Subject","data-signature":"994b71c8921c9a75beef2d9395e1f7fd757a1fe202fa30ffc2cefcd36a5e98fe"},"state":"open","children":[{"data":"Test-Taker  0","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529019327625","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529019327625","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"dff3eff0857ea118c317c01f76f736bc3930f18b1afca81bafdeb3d2bcb6f534"}},{"data":"Test-Taker  1","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529019723426","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529019723426","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"8d813c7e5d1ad20dc31fd5d32f34b4a3c9a9b8ab6cf3c48cbb7c148b33949eed"}},{"data":"Test-Taker  10","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529021538635","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529021538635","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"7ccb3b451f2f435ce35008bb2112d40af8572cb199ab99d7ced9ad9951ac5b63"}},{"data":"Test-Taker  100","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290355669125","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290355669125","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"cf9735098a9ef2a33170d2deac6c5156e404fd6d26f7f4d3b4723759eb606b65"}},{"data":"Test-Taker  101","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529035778126","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529035778126","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"012ec45aa31d943f59a9b819d63dc3b4ea216e11ed487af6820f2fdf3a3a10ca"}},{"data":"Test-Taker  102","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290356882127","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290356882127","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"f6a7319336205ae4b1e75131e1115d691834aaddf7c6f6550b12dac6ecd00576"}},{"data":"Test-Taker  103","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290362076128","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290362076128","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"9b4960b4df95020c34e3a92b6e182dbf8895fc2c91fbdb5ef20351293febd716"}},{"data":"Test-Taker  104","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290369733129","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290369733129","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"130022af77f553fd0cc125159c0a353242a13da7841e554f47fd8b55bb9f3231"}},{"data":"Test-Taker  105","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290362440130","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290362440130","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"5af77f61d488d3f7482a73c764df232f7965301156c670a894d0e89adb9b2d0a"}},{"data":"Test-Taker  106","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290363000131","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290363000131","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"a06020054544ed2fcfe17be9bc07efe85ad51ab0ddfb8a06b46cdf7143d58ec2"}}],"count":10}],"count":0};
        var options = {
            dataUrl: "/tao/GenerisTree/getData",
            dataUrl1000: "/tao/GenerisTree/getData1000",
            deliveriesOrder: "http://www.tao.lu/Ontologies/TAODelivery.rdf#AssembledDeliveryCompilationTime",
            deliveriesOrderdir: "desc",
            deliveryTreeId: "eligible-delivery-tree-2",
            editingMode: false,
            isDacEnabled: false,
            subjectTreeId: "eligible-testTaker-tree-1",
            testTakerDataUrl: "/taoTestCenter/TestCenterManager/getData",
            title: "Add Eligibility"
        };

        var treeContainerSelector = '#' + fixture +' .t10';
        var treeContainerSelectorPaginated = '#' + fixture +' .t1000';
        var paginateAllSelector = '.paginate-all';
        var paginateMoreSelector = '.paginate-more';

        $.mockjax({
            url: options.dataUrl,
            status: 200,
            responseText: testTakers10
        });
        $.mockjax({
            url: options.dataUrl1000,
            status: 200,
            responseText: testTakers1000
        });

         var tree10 = new GenerisTreeSelectClass(treeContainerSelector, options.dataUrl, {
            actionId : 'treeOptions.actionId',
            saveUrl : 'treeOptions.saveUrl',
            saveData : {},
            checkedNodes : _.map(selected, uri.encode), //generis tree uses "encoded uri" to check nodes
            serverParameters : {
                openParentNodes : selected, //generis tree uses normal if to open nodes...
                rootNode : rootNode
            },
            paginate : 10,
            checkResourcePermissions: options.isDacEnabled
        });
        var tree1000 = new GenerisTreeSelectClass(treeContainerSelectorPaginated, options.dataUrl1000, {
            actionId : 'treeOptions.actionId',
            saveUrl : 'treeOptions.saveUrl',
            saveData : {},
            checkedNodes : _.map(selected, uri.encode), //generis tree uses "encoded uri" to check nodes
            serverParameters : {
                openParentNodes : selected, //generis tree uses normal if to open nodes...
                rootNode : rootNode
            },
            paginate : 10,
            checkResourcePermissions: options.isDacEnabled
        });
        assert.expect(29);
        assert.ok(tree10,  'tree was initialized and prepared');
        assert.ok(tree1000,  ' paginated tree was initialized and prepared');

        assert.equal(tree10.checkedNodes.length, 0, 'checked nodes default ');
        assert.equal(tree10.hiddenNodes.length, 0, 'hidden nodes default ');
        assert.equal(tree10.state, 0, 'default state');


        assert.ok(tree10.treeOptions.callback.beforecheck,  'beforecheck callback is defined');
        assert.ok(tree10.treeOptions.callback.beforedata,  'beforedata callback is defined');
        assert.ok(tree10.treeOptions.callback.beforeopen,  'beforeopen callback is defined');
        assert.ok(tree10.treeOptions.callback.beforeuncheck,  'beforeuncheck callback is defined');
        assert.ok(tree10.treeOptions.callback.onchange,  'onchange callback is defined');
        assert.ok(tree10.treeOptions.callback.ondata,  'ondata callback is defined');
        assert.ok(tree10.treeOptions.callback.onload,  'onload callback is defined');
        assert.ok(tree10.treeOptions.callback.onopen,  'onopen callback is defined');
        assert.ok(tree10.treeOptions.callback.onselect,  'onselect callback is defined');

        assert.ok(tree10.__proto__.check,  'check method is defined');
        assert.ok(tree10.__proto__.checkPermissionsRecursively,  'checkPermissionsRecursively method is defined');
        assert.ok(tree10.__proto__.convertDataWithPermissions,  'convertDataWithPermissions method is defined');
        assert.ok(tree10.__proto__.getChecked,  'getChecked method is defined');
        assert.ok(tree10.__proto__.init,  'init method is defined');
        assert.ok(tree10.__proto__.paginateInstances,  'paginateInstances method is defined');
        assert.ok(tree10.__proto__.removeHiddenNodes,  'removeHiddenNodes method is defined');
        assert.ok(tree10.__proto__.saveData,  'saveData method is defined');
        assert.ok(tree10.__proto__.trace,  'trace method is defined');


        _.delay(function () {
            assert.equal($(treeContainerSelector + ' '+ paginateAllSelector).length, 0, 'usual tree doesent have "pagination all" button');
            assert.equal($(treeContainerSelector + ' '+ paginateMoreSelector).length, 0, 'usual tree doesent have "pagination more" button');
            assert.equal($(treeContainerSelector + ' '+ '.leaf').length, 10, 'all received items were rendered');

            assert.equal($(treeContainerSelectorPaginated + ' '+ paginateAllSelector).length, 1, 'paginated tree  has 1 "pagination all" button');
            assert.equal($(treeContainerSelectorPaginated + ' '+ paginateMoreSelector).length, 1, 'paginated tree has 1 "pagination more" button');
            assert.equal($(treeContainerSelectorPaginated + ' '+ '.leaf').length, 12, 'all received items were rendered + pagination buttons as tree leafs');

            done();
        },10);

    });

    QUnit.test('check/uncheck functionality', function(assert) {
        var done = assert.async();
        var rootNode = 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject';
        var selected = [];
        var testTakers10 = {"data":"Test-taker","type":"class","attributes":{"id":"http_2_www_0_tao_0_lu_1_Ontologies_1_TAOSubject_0_rdf_3_Subject","class":"node-class","data-uri":"http:\/\/www.tao.lu\/Ontologies\/TAOSubject.rdf#Subject","data-classUri":null,"data-signature":"4837dfe9a9ac93af9b404605843ac4bc31a16e4f898075a132a718c1c04d5406"},"state":"open","children":[{"data":"Generation yeGf","type":"class","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529018304924","class":"node-class","data-uri":"http:\/\/localhost:88\/#i1556529018304924","data-classUri":"http:\/\/www.tao.lu\/Ontologies\/TAOSubject.rdf#Subject","data-signature":"994b71c8921c9a75beef2d9395e1f7fd757a1fe202fa30ffc2cefcd36a5e98fe"},"state":"open","children":[{"data":"Test-Taker  0","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529019327625","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529019327625","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"dff3eff0857ea118c317c01f76f736bc3930f18b1afca81bafdeb3d2bcb6f534"}},{"data":"Test-Taker  1","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529019723426","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529019723426","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"8d813c7e5d1ad20dc31fd5d32f34b4a3c9a9b8ab6cf3c48cbb7c148b33949eed"}},{"data":"Test-Taker  10","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529021538635","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529021538635","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"7ccb3b451f2f435ce35008bb2112d40af8572cb199ab99d7ced9ad9951ac5b63"}},{"data":"Test-Taker  100","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290355669125","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290355669125","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"cf9735098a9ef2a33170d2deac6c5156e404fd6d26f7f4d3b4723759eb606b65"}},{"data":"Test-Taker  101","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i1556529035778126","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i1556529035778126","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"012ec45aa31d943f59a9b819d63dc3b4ea216e11ed487af6820f2fdf3a3a10ca"}},{"data":"Test-Taker  102","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290356882127","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290356882127","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"f6a7319336205ae4b1e75131e1115d691834aaddf7c6f6550b12dac6ecd00576"}},{"data":"Test-Taker  103","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290362076128","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290362076128","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"9b4960b4df95020c34e3a92b6e182dbf8895fc2c91fbdb5ef20351293febd716"}},{"data":"Test-Taker  104","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290369733129","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290369733129","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"130022af77f553fd0cc125159c0a353242a13da7841e554f47fd8b55bb9f3231"}},{"data":"Test-Taker  105","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290362440130","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290362440130","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"5af77f61d488d3f7482a73c764df232f7965301156c670a894d0e89adb9b2d0a"}},{"data":"Test-Taker  106","type":"instance","attributes":{"id":"http_2_localhost_4_88_1__3_i15565290363000131","class":"node-instance","data-uri":"http:\/\/localhost:88\/#i15565290363000131","data-classUri":"http:\/\/localhost:88\/#i1556529018304924","data-signature":"a06020054544ed2fcfe17be9bc07efe85ad51ab0ddfb8a06b46cdf7143d58ec2"}}],"count":10}],"count":0};
        var options = {
            dataUrl: "/tao/GenerisTree/getData",
            deliveriesOrder: "http://www.tao.lu/Ontologies/TAODelivery.rdf#AssembledDeliveryCompilationTime",
            deliveriesOrderdir: "desc",
            deliveryTreeId: "eligible-delivery-tree-2",
            editingMode: false,
            isDacEnabled: false,
            subjectTreeId: "eligible-testTaker-tree-1",
            testTakerDataUrl: "/taoTestCenter/TestCenterManager/getData",
            title: "Add Eligibility"
        };

        var treeContainerSelector = '#' + fixture +' .t10';
        var itemSelector = treeContainerSelector + ' .node-instance.leaf';

        $.mockjax({
            url: options.dataUrl,
            status: 200,
            responseText: testTakers10
        });

        var tree10 = new GenerisTreeSelectClass(treeContainerSelector, options.dataUrl, {
            actionId : 'treeOptions.actionId',
            saveUrl : 'treeOptions.saveUrl',
            saveData : {},
            checkedNodes : _.map(selected, uri.encode), //generis tree uses "encoded uri" to check nodes
            serverParameters : {
                openParentNodes : selected, //generis tree uses normal if to open nodes...
                rootNode : rootNode
            },
            paginate : 10,
            checkResourcePermissions: options.isDacEnabled
        });

        assert.ok(tree10,  'tree was initialized and prepared');

        _.delay(function () {
            $(itemSelector).eq(0).children('a').click();
            assert.ok($(itemSelector).eq(0).children('a').hasClass('clicked'), 'item reacts to click and \'clicked\' class appended');
            assert.ok($(itemSelector).eq(0).children('a').hasClass('checked'), 'item reacts to click and \'checked\' class appended');

            $(itemSelector).eq(1).children('a').click();

            assert.ok($(itemSelector + ' > a.checked').length === 2, 'several items are able to be selected');

            done();
        },10);

    });

});

