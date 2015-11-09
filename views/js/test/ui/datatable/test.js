define(['jquery', 'json!tao/test/ui/datatable/data.json', 'ui/datatable'], function($, dataset){

    "use strict";
    
    QUnit.module('DataTable Test', {
        teardown : function(){
            //reset the container
            $('#container-1').empty().off('.datatable');
        }
    });
   
    QUnit.test('plugin', function(assert){
       QUnit.expect(1);
       assert.ok(typeof $.fn.datatable === 'function', 'The datatable plugin is registered');
    });

    QUnit.asyncTest('Initialization', function(assert){
        QUnit.expect(2);
        
        var $elt = $('#container-1');
        assert.ok($elt.length === 1, 'Test the fixture is available');

        
        $elt.on('create.datatable', function(){
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            QUnit.start();
        });
        $elt.datatable({
            url : 'js/test/ui/datatable/data.json'
        });
    });

    QUnit.asyncTest('Model loading using AJAX', function(assert){
        QUnit.expect(11);
        
        var $elt = $('#container-1');
        assert.ok($elt.length === 1, 'Test the fixture is available');

        QUnit.stop(3);

        $elt.on('create.datatable', function(){
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.datatable thead th').length === 6, 'the table contains 7 heads elements (id included)');
            assert.equal($elt.find('.datatable thead th:eq(0) div').text(), 'Login', 'the login label is created');
            assert.equal($elt.find('.datatable thead th:eq(1) div').text(), 'Name', 'the name label is created');
            assert.equal($elt.find('.datatable thead th:eq(0) div').data('sort-by'), 'login', 'the login col is sortable');
            QUnit.start();
        });
        $elt.on('query.datatable', function(event, ajaxConfig) {
            assert.equal(typeof ajaxConfig, 'object', 'the query event is triggered and provides an object');
            assert.equal(typeof ajaxConfig.url, 'string', 'the query event provides an object containing the target url');
            assert.equal(typeof ajaxConfig.data, 'object', 'the query event provides an object containing the request parameters');
            QUnit.start();
        });
        $elt.on('beforeload.datatable', function(event, response) {
            assert.equal(typeof response, 'object', 'the beforeload event is triggered and provides the response data');
            QUnit.start();
        });
        $elt.on('load.datatable', function(event, response) {
            assert.equal(typeof response, 'object', 'the load event is triggered and provides the response data');
            QUnit.start();
        });
        $elt.datatable({
            url : 'js/test/ui/datatable/data.json',
            'model' : [{
                id : 'login',
                label : 'Login',
                sortable : true
            },{
                id : 'name',
                label : 'Name',
                sortable : true
            },{
                id : 'email',
                label : 'Email',
                sortable : true
            },{
                id : 'roles',
                label :'Roles',
                sortable : false
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

    QUnit.asyncTest('Model loading using predefined data', function(assert){
        QUnit.expect(8);

        var $elt = $('#container-1');
        assert.ok($elt.length === 1, 'Test the fixture is available');

        QUnit.stop(2);

        $elt.on('create.datatable', function(){
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.datatable thead th').length === 6, 'the table contains 7 heads elements (id included)');
            assert.equal($elt.find('.datatable thead th:eq(0) div').text(), 'Login', 'the login label is created');
            assert.equal($elt.find('.datatable thead th:eq(1) div').text(), 'Name', 'the name label is created');
            assert.equal($elt.find('.datatable thead th:eq(0) div').data('sort-by'), 'login', 'the login col is sortable');
            QUnit.start();
        });
        $elt.on('query.datatable', function(event, ajaxConfig) {
            assert.ok(false, 'the query event must not be triggered!');
        });
        $elt.on('beforeload.datatable', function(event, response) {
            assert.equal(typeof response, 'object', 'the beforeload event is triggered and provides the response data');
            QUnit.start();
        });
        $elt.on('load.datatable', function(event, response) {
            assert.equal(typeof response, 'object', 'the load event is triggered and provides the response data');
            QUnit.start();
        });
        $elt.datatable({
            url : 'js/test/ui/datatable/data.json',
            'model' : [{
                id : 'login',
                label : 'Login',
                sortable : true
            },{
                id : 'name',
                label : 'Name',
                sortable : true
            },{
                id : 'email',
                label : 'Email',
                sortable : true
            },{
                id : 'roles',
                label :'Roles',
                sortable : false
            },{
                id : 'dataLg',
                label : 'Data Language',
                sortable : true
            },{
                id: 'guiLg',
                label : 'Interface Language',
                sortable : true
            }]
        }, dataset);
    });

    QUnit.asyncTest('Data rendering', function(assert){
        QUnit.expect(13);

        var renderCalled = false;
        var $elt = $('#container-1');
        assert.ok($elt.length === 1, 'Test the fixture is available');

        QUnit.stop(5);

        $elt.on('create.datatable', function(){
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.datatable thead th').length === 6, 'the table contains 7 heads elements (id included)');
            assert.equal($elt.find('.datatable thead th:eq(0) div').text(), 'Login', 'the login label is created');
            assert.equal($elt.find('.datatable thead th:eq(1) div').text(), 'Name', 'the name label is created');
            assert.equal($elt.find('.datatable thead th:eq(0) div').data('sort-by'), 'login', 'the login col is sortable');
            QUnit.start();
        });
        $elt.on('query.datatable', function(event, ajaxConfig) {
            assert.equal(typeof ajaxConfig, 'object', 'the query event is triggered and provides an object');
            assert.equal(typeof ajaxConfig.url, 'string', 'the query event provides an object containing the target url');
            assert.equal(typeof ajaxConfig.data, 'object', 'the query event provides an object containing the request parameters');
            QUnit.start();
        });
        $elt.on('beforeload.datatable', function(event, response) {
            assert.equal(typeof response, 'object', 'the beforeload event is triggered and provides the response data');
            QUnit.start();
        });
        $elt.on('load.datatable', function(event, response) {
            assert.equal(typeof response, 'object', 'the load event is triggered and provides the response data');
            QUnit.start();

            if (!renderCalled) {
                renderCalled = true;
                setTimeout(function() {
                    $elt.datatable('render', response);
                }, 1);
            }
        });
        $elt.datatable({
            url : 'js/test/ui/datatable/data.json',
            'model' : [{
                id : 'login',
                label : 'Login',
                sortable : true
            },{
                id : 'name',
                label : 'Name',
                sortable : true
            },{
                id : 'email',
                label : 'Email',
                sortable : true
            },{
                id : 'roles',
                label :'Roles',
                sortable : false
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

    QUnit.asyncTest('Pagination disabled', function(assert){
        QUnit.expect(6);
        
        var $elt = $('#container-1');
        assert.ok($elt.length === 1, 'Test the fixture is available');
        
        
        $elt.on('create.datatable', function(){
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.datatable-backward').length === 2, 'there is 2 backward buttons');
            assert.ok($elt.find('.datatable-forward').length === 2, 'there is 2 forward buttons');
            assert.ok($elt.find('.datatable-backward:first').prop('disabled'), 'the backward button is disabled');
            assert.ok($elt.find('.datatable-forward:last').prop('disabled'), 'the forward button is disabled');
            QUnit.start();
        });
        $elt.datatable({
            url : 'js/test/ui/datatable/data.json',
            'model' : [{
                id : 'login',
                label : 'Login',
                sortable : true
            },{
                id : 'name',
                label : 'Name',
                sortable : true
            },{
                id : 'email',
                label : 'Email',
                sortable : true
            },{
                id : 'roles',
                label :'Roles',
                sortable : false
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

    QUnit.asyncTest('Pagination enabled', function(assert){
        QUnit.expect(7);
        
        var $elt = $('#container-1');
        assert.ok($elt.length === 1, 'Test the fixture is available');
        
        
        $elt.on('create.datatable', function(){
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.datatable-backward').length === 2, 'there is 2 backward buttons');
            assert.ok($elt.find('.datatable-forward').length === 2, 'there is 2 forward buttons');
            assert.ok($elt.find('.datatable-forward:first').prop('disabled') === false, 'the forward button is enabled');
            assert.ok($elt.find('.datatable-forward:last').prop('disabled') === false, 'the forward button is disabled');
            assert.ok($elt.find('.datatable-backward:first').prop('disabled'), 'the backward button is disabled (on the 1st page)');
            QUnit.start();
        });
        $elt.datatable({
            url : 'js/test/ui/datatable/largedata.json',
            'model' : [{
                id : 'login',
                label : 'Login',
                sortable : true
            },{
                id : 'password',
                label : 'Pass',
                sortable : true
            },{
                id : 'title',
                label : 'Title',
                sortable : true
            },{
                id : 'firstname',
                label : 'First',
                sortable : true
            },{
                id : 'lastname',
                label :'Last',
                sortable : true
            },{
                id : 'gender',
                label : 'Gender',
                sortable : true
            },{
                id: 'email',
                label : 'Email',
                sortable : true
            },{
                id: 'picture',
                label : 'picture',
                sortable : true
            },{
                id: 'address',
                label : 'Address',
                sortable : true
            }]
        });
    });

    QUnit.asyncTest('Selection disabled', function(assert){
        QUnit.expect(4);

        var $elt = $('#container-1');
        assert.ok($elt.length === 1, 'Test the fixture is available');


        $elt.on('create.datatable', function(){
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.checkboxes').length === 0, 'there is no selection checkboxes');
            assert.ok($elt.datatable('selection').length === 0, 'the selection is empty');
            QUnit.start();
        });
        $elt.datatable({
            url : 'js/test/ui/datatable/data.json',
            'model' : [{
                id : 'login',
                label : 'Login',
                sortable : true
            },{
                id : 'name',
                label : 'Name',
                sortable : true
            },{
                id : 'email',
                label : 'Email',
                sortable : true
            },{
                id : 'roles',
                label :'Roles',
                sortable : false
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

    QUnit.asyncTest('Selection enabled', function(assert){
        QUnit.expect(11);

        var $elt = $('#container-1');
        assert.ok($elt.length === 1, 'Test the fixture is available');


        $elt.on('create.datatable', function(){
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.equal($elt.find('.checkboxes').length, 4, 'there are selection checkboxes');
            assert.equal($elt.datatable('selection').length, 0, 'the selection is empty');

            $elt.find('td.checkboxes input').trigger('click');
            assert.equal($elt.datatable('selection').length, 3, 'select each line: the selection is full');

            $elt.find('th.checkboxes input').trigger('click');
            assert.equal($elt.datatable('selection').length, 0, 'click on the checkall button: the selection is empty');

            $elt.find('th.checkboxes input').trigger('click');
            assert.equal($elt.datatable('selection').length, 3, 'click on the checkall button: the selection is full');

            $elt.find('td.checkboxes input').first().trigger('click');
            assert.equal($elt.datatable('selection').length, 2, 'unselect a line: the selection contains all items but the unchecked item');

            $elt.find('th.checkboxes input').trigger('click');
            assert.equal($elt.datatable('selection').length, 3, 'click on the checkall button: the selection is full');

            $elt.find('td.checkboxes input').trigger('click');
            assert.equal($elt.datatable('selection').length, 0, 'unselect each line: the selection is empty');

            $elt.find('td.checkboxes input').first().trigger('click');
            assert.equal($elt.datatable('selection').length, 1, 'select a line: the selection contains only the checked item');

            QUnit.start();
        });
        $elt.datatable({
            url : 'js/test/ui/datatable/data.json',
            selectable : true,
            'model' : [{
                id : 'login',
                label : 'Login',
                sortable : true
            },{
                id : 'name',
                label : 'Name',
                sortable : true
            },{
                id : 'email',
                label : 'Email',
                sortable : true
            },{
                id : 'roles',
                label :'Roles',
                sortable : false
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

    QUnit.asyncTest('Selectable rows', function (assert) {
        QUnit.expect(10);

        var $elt = $('#container-1');
        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('create.datatable', function () {
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.datatable thead th').length === 6, 'the table contains 7 heads elements (id included)');

            $elt.find('.datatable tbody tr:eq(1) td:eq(1)').trigger('click');
        });

        $elt.on('selected.datatable', function () {
            assert.equal($elt.find('.datatable tbody tr.selected td:eq(0)').text(), 'jdoe', 'the login field in selected row is correct');
            assert.equal($elt.find('.datatable tbody tr.selected td:eq(1)').text(), 'John Doe', 'the name field in selected row is correct');
            assert.equal($elt.find('.datatable tbody tr.selected td:eq(2)').text(), 'jdoe@nowhere.org', 'the mail field in selected row is correct');
            assert.equal($elt.find('.datatable tbody tr.selected td:eq(3)').text(), 'Items Manager', 'the roles field in selected row is correct');
            assert.equal($elt.find('.datatable tbody tr.selected td:eq(4)').text(), 'English', 'the dataLg field in selected row is correct');
            assert.equal($elt.find('.datatable tbody tr.selected td:eq(5)').text(), 'English', 'the guiLg field in selected row is correct');

            QUnit.start();
        });

        $elt.datatable({
            url : 'js/test/ui/datatable/data.json',
            rowSelection: true,
            'model' : [{
                id : 'login',
                label : 'Login',
                sortable : true
            },{
                id : 'name',
                label : 'Name',
                sortable : true
            },{
                id : 'email',
                label : 'Email',
                sortable : true
            },{
                id : 'roles',
                label :'Roles',
                sortable : false
            },{
                id : 'dataLg',
                label : 'Data Language',
                sortable : true
            },{
                id: 'guiLg',
                label : 'Interface Language',
                sortable : true
            }],
            listeners: {
                selected: function () {
                    assert.ok(true, 'the handler was attached and caused');
                }
            }
        });
    });

    QUnit.asyncTest('Default filtering enabled', function (assert) {
        QUnit.expect(7);

        var $elt = $('#container-1');
        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('create.datatable', function () {
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.datatable thead th').length === 6, 'the table contains 7 heads elements (id included)');
            assert.ok($elt.find('.datatable-wrapper aside.filter').length, 'the filter is enabled');

            $elt.find('.datatable-wrapper aside.filter input').val('abcdef');
            $elt.find('.datatable-wrapper aside.filter button').trigger('click');
        });

        $elt.on('filter.datatable', function (event, options) {
            assert.equal(options.filterquery, 'abcdef', 'the filter set right search query');
            assert.deepEqual(options.filtercolumns, ["login", "name"], 'the filter set right columns');
            setTimeout(function() {
                assert.equal($elt.find('.datatable-wrapper aside.filter input').hasClass('focused'), true, 'the filter is focusable after refreshing');
                QUnit.start();
            }, 100);
        });

        $elt.datatable({
            url : 'js/test/ui/datatable/data.json',
            filter: {
                columns: ['login', 'name']
            },
            'model' : [{
                id : 'login',
                label : 'Login',
                sortable : true
            },{
                id : 'name',
                label : 'Name',
                sortable : true
            },{
                id : 'email',
                label : 'Email',
                sortable : true
            },{
                id : 'roles',
                label :'Roles',
                sortable : false
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

    QUnit.asyncTest('Column filtering enabled', function (assert) {
        QUnit.expect(8);

        var $elt = $('#container-1');
        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('create.datatable', function () {
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.datatable thead th').length === 6, 'the table contains 7 heads elements (id included)');
            assert.equal($elt.find('.datatable thead th:eq(0) aside.filter').data('column'), 'login', 'the login col is filterable');
            assert.equal($elt.find('.datatable thead th:eq(2) aside.filter').data('column'), 'email', 'the email col is filterable');

            $elt.find('aside.filter[data-column="login"] input').val('abcdef');
            $elt.find('aside.filter[data-column="login"] button').trigger('click');
        });

        $elt.on('filter.datatable', function (event, options) {
            assert.equal(options.filtercolumns, 'login', 'the filter set right column');
            assert.equal(options.filterquery, 'abcdef', 'the filter set right search query');
            setTimeout(function() {
                assert.equal($elt.find('aside.filter[data-column="login"] input').hasClass('focused'), true, 'the login column filter is focusable after refreshing');
                QUnit.start();
            }, 100);

        });

        $elt.datatable({
            url : 'js/test/ui/datatable/data.json',
            filter: true,
            'model' : [{
                id : 'login',
                label : 'Login',
                sortable : true,
                filterable : true
            },{
                id : 'name',
                label : 'Name',
                sortable : true
            },{
                id : 'email',
                label : 'Email',
                sortable : true,
                filterable : true
            },{
                id : 'roles',
                label :'Roles',
                sortable : false
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

    QUnit.asyncTest('Transform', function(assert) {

        var $elt = $('#container-1');
        var renderFullName = function(row) {
            return row.firstname + ' ' + row.lastname;
        };
        var transform = function(value, row, field, index, data) {
            assert.equal(typeof row, 'object', 'The row is provided');
            assert.equal(typeof field, 'object', 'The field is provided');
            assert.equal(typeof index, 'number', 'The row index is provided');
            assert.equal(typeof data, 'object', 'The dataset is provided');
            assert.equal(data, dataset, 'The provided dataset is the right dataset');

            assert.equal(row, dataset[index], 'The provided row is the exact row at index');
            assert.equal(typeof field.id, 'string', 'The field id is provided');
            assert.equal(value, row[field.id], 'The right value is provided');

            QUnit.start();
            return renderFullName(row);
        };
        var model = [{
            id: 'fullName',
            label: 'Full name',
            transform: transform
        }, {
            id: 'email',
            label: 'Email'
        }];
        var dataset = [{
            id: 1,
            firstname: 'John',
            lastname: 'Smith',
            email: 'john.smith@mail.com'
        }, {
            id: 1,
            firstname: 'Jane',
            lastname: 'Doe',
            email: 'jane.doe@mail.com'
        }];

        QUnit.expect(26);
        QUnit.stop(2);

        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('create.datatable', function () {
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.datatable thead th').length === 2, 'the table contains 2 heads elements');
            assert.equal($elt.find('.datatable thead th:eq(0)').text().trim(), model[0].label, 'The first column contains the right header');
            assert.equal($elt.find('.datatable thead th:eq(1)').text().trim(), model[1].label, 'The second column contains the right header');

            assert.equal($elt.find('.datatable tbody tr').length, dataset.length, 'The table contains the same lines number as in the dataset');

            assert.equal($elt.find('.datatable tbody tr:eq(0) td:eq(0)').text().trim(), renderFullName(dataset[0]), 'The first line contains the right full name');
            assert.equal($elt.find('.datatable tbody tr:eq(0) td:eq(1)').text().trim(), dataset[0].email, 'The first line contains the right email');

            assert.equal($elt.find('.datatable tbody tr:eq(1) td:eq(0)').text().trim(), renderFullName(dataset[1]), 'The second line contains the right full name');
            assert.equal($elt.find('.datatable tbody tr:eq(1) td:eq(1)').text().trim(), dataset[1].email, 'The second line contains the right email');

            QUnit.start();
        });

        $elt.datatable({
            model: model
        }, {
            data: dataset
        });

    });
});
