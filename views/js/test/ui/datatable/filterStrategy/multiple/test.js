
define([
    'jquery',
    'lodash',
    'json!tao/test/ui/datatable/data.json',
    'json!tao/test/ui/datatable/largedata.json',
    'ui/datatable'
], function($, _, dataset, largeDataset){
    "use strict";

    QUnit.module('DataTable filter strategy test', {
        teardown : function(){
            //reset the container
            $('#container-1').empty().off('.datatable');
        }
    });

    QUnit.asyncTest('test getQueryData', function(assert){
        QUnit.expect(2);

        var $elt = $('#container-1');
        var dom;

        $elt.on('create.datatable', function () {

            dom = $elt.find('tbody').get();
            $elt.find('aside.filter[data-column="login"] input').val('login value');
            $elt.find('aside.filter[data-column="name"] input').val('name value');
            $elt.find('aside.filter[data-column="login"] button').trigger('click');
        });

        $elt.on('filter.datatable', function (event, options) {
            assert.equal(options.filtercolumns.login, 'login value', 'First filtercolumn has right value');
            assert.equal(options.filtercolumns.name, 'name value', 'Second filtercolumn has right value');
            QUnit.start();
        });

        $elt.datatable({
            url : 'js/test/ui/datatable/data.json',
            filter: true,
            filterStrategy: 'multiple',
            model : [{
                id : 'login',
                label : 'Login',
                sortable : true,
                filterable : true
            },{
                id : 'name',
                label : 'Name',
                sortable : true,
                filterable : true
            },{
                id : 'email',
                label : 'Email',
                sortable : true,
                filterable : true
            },{
                id : 'roles',
                label :'Roles',
                sortable : true
            },{
                id : 'dataLg',
                label : 'Data Language',
                sortable : true
            },{
                id: 'guiLg',
                label : 'Interface Language',
                sortable : true
            }]
        });
    });


    QUnit.asyncTest('test render', function(assert){
        QUnit.expect(5);

        var $elt = $('#container-1');
        var dom;

        $elt.on('create.datatable', function () {
            dom = $elt.find('tbody').get();
            $elt.find('aside.filter[data-column="login"] input').val('login value');
            $elt.find('aside.filter[data-column="name"] input').val('name value');
            $elt.find('aside.filter[data-column="roles"] select').val('Doe');
            $elt.find('aside.filter[data-column="login"] button').trigger('click');
        });

        $elt.on('filter.datatable', function (event, options) {
            $elt.on('load.datatable', function () {
                assert.equal($elt.find('aside.filter[data-column="login"] input').val(), 'login value', 'First input has right value');
                assert.equal($elt.find('aside.filter[data-column="roles"] select').val(), 'Doe', 'Second input has right value');
                assert.equal($elt.find('aside.filter[data-column="name"] input').val(), 'name value', 'Third input has right value');
                QUnit.start();
            });
        });

        $elt.datatable({
            url : 'js/test/ui/datatable/data.json',
            filter: true,
            filterStrategy: 'multiple',
            model : [{
                id : 'login',
                label : 'Login',
                sortable : true,
                filterable : true
            },{
                id : 'name',
                label : 'Name',
                sortable : true,
                filterable : true
            },{
                id : 'email',
                label : 'Email',
                sortable : true,
                filterable : true
            },{
                id : 'roles',
                label :'Roles',
                sortable : true,
                filterable : true,
                customFilter : {
                    template : '<select name="role"><option selected></option><option value="Smith">Smith</option><option value="Doe">Doe</option></select>',
                    callback : function($filter){
                        //should be called twice, after initialization render and after filter render.
                        assert.ok(true, "Custom filter callback called");
                    }
                }
            },{
                id : 'dataLg',
                label : 'Data Language',
                sortable : true
            },{
                id: 'guiLg',
                label : 'Interface Language',
                sortable : true
            }]
        });
    });
});
