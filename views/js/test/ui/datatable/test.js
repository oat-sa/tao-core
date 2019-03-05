
define( [
    
    'jquery',
    'lodash',
    'json!tao/test/ui/datatable/data.json',
    'json!tao/test/ui/datatable/largedata.json',
    'ui/datatable'
], function(  $, _, dataset, largeDataset ) {
    'use strict';

    QUnit.module( 'DataTable Test', {
        afterEach: function( assert ) {

            //Reset the container
            $( '#container-1' ).empty().off( '.datatable' );
        }
    } );

    QUnit.test( 'Plugin', function( assert ) {
       assert.expect( 1 );
       assert.ok( typeof $.fn.datatable === 'function', 'The datatable plugin is registered' );
    } );

    QUnit.test( 'Initialization', function( assert ) {
        var ready = assert.async();
        assert.expect( 3 );

        var $elt = $( '#container-1' );
        var firstUrl = 'js/test/ui/datatable/data.json';
        var secondUrl = 'js/test/ui/datatable/largedata.json';
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        $elt.one( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );

            // *** Check the reinit of the datatable
            $elt.one( 'create.datatable', function() {
                assert.ok( false, 'The create event must not be triggered when reinit' );
            } );

            $elt.one( 'load.datatable', function() {
                var data = $elt.data( 'ui.datatable' );
                assert.equal( data && data.url, secondUrl, 'The options must be updated by reinit' );
                ready();
            } );

            $elt.datatable( {
                url: secondUrl
            } );

            // *** End reinit check
        } );
        $elt.datatable( {
            url: firstUrl
        } );
    } );

    QUnit.test( 'Options', function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        var $elt = $( '#container-1' );
        var firstOptions = {
            url: 'js/test/ui/datatable/data.json'
        };
        var secondOptions = {
            url: 'js/test/ui/datatable/largedata.json',
            tools: [ {
                id: 'test',
                label: 'TEST'
            } ]
        };
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );

            var data = $elt.data( 'ui.datatable' ) || {};
            assert.equal( data.url, firstOptions.url, 'The options must be set' );

            $elt.datatable( 'options', secondOptions );

            data = $elt.data( 'ui.datatable' ) || {};
            assert.equal( data.url, secondOptions.url, 'The url option must be updated' );
            assert.deepEqual( data.tools, secondOptions.tools, 'The tools options must be added' );
            ready();
        } );
        $elt.datatable( firstOptions );
    } );

    QUnit.test( 'Model loading using AJAX', function( assert ) {
        var ready3 = assert.async();
        var ready2 = assert.async();
        var ready1 = assert.async();
        assert.expect( 11 );

        var $elt = $( '#container-1' );
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        var ready = assert.async();

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.ok( $elt.find( '.datatable thead th' ).length === 6, 'the table contains 6 heads elements' );
            assert.equal( $elt.find( '.datatable thead th:eq(0) div' ).text(), 'Login', 'the login label is created' );
            assert.equal( $elt.find( '.datatable thead th:eq(1) div' ).text(), 'Name', 'the name label is created' );
            assert.equal( $elt.find( '.datatable thead th:eq(0) div' ).data( 'sort-by' ), 'login', 'the login col is sortable' );
            ready();
        } );
        $elt.on( 'query.datatable', function( event, ajaxConfig ) {
            assert.equal( typeof ajaxConfig, 'object', 'the query event is triggered and provides an object' );
            assert.equal( typeof ajaxConfig.url, 'string', 'the query event provides an object containing the target url' );
            assert.equal( typeof ajaxConfig.data, 'object', 'the query event provides an object containing the request parameters' );
            ready1();
        } );
        $elt.on( 'beforeload.datatable', function( event, response ) {
            assert.equal( typeof response, 'object', 'the beforeload event is triggered and provides the response data' );
            ready2();
        } );
        $elt.on( 'load.datatable', function( event, response ) {
            assert.equal( typeof response, 'object', 'the load event is triggered and provides the response data' );
            ready3();
        } );
        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ]
        } );
    } );

    QUnit.test( 'Model loading using predefined data', function( assert ) {
        var ready3 = assert.async();
        var ready2 = assert.async();
        var ready1 = assert.async(2);
        assert.expect( 12 );

        var $elt = $( '#container-1' );
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        var ready = assert.async();

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.ok( $elt.find( '.datatable thead th' ).length === 6, 'the table contains 6 heads elements' );
            assert.equal( $elt.find( '.datatable thead th:eq(0) div' ).text(), 'Login', 'the login label is created' );
            assert.equal( $elt.find( '.datatable thead th:eq(1) div' ).text(), 'Name', 'the name label is created' );
            assert.equal( $elt.find( '.datatable thead th:eq(0) div' ).data( 'sort-by' ), 'login', 'the login col is sortable' );
            ready();
        } );
        $elt.on( 'query.datatable', function( event, ajaxConfig ) {
            assert.ok( false, 'the query event must not be triggered!' );
        } );
        $elt.on( 'beforeload.datatable', function( event, response ) {
            assert.equal( typeof response, 'object', 'the beforeload event is triggered and provides the response data' );
            ready1();
        } );
        $elt.one( 'load.datatable', function( event, response ) {
            assert.equal( typeof response, 'object', 'the load event is triggered and provides the response data' );
            assert.equal( $elt.find( '.datatable tbody tr' ).length, dataset.data.length, 'the lines from the small dataset are rendered' );

            ready2();

            // *** Check the refresh with predefined data
            _.defer( function() {
                $elt.one( 'load.datatable', function( event, response ) {
                    assert.equal( typeof response, 'object', 'the load event is triggered and provides the response data' );
                    assert.equal( $elt.find( '.datatable tbody tr' ).length, largeDataset.data.length, 'the lines from the large dataset are rendered' );
                    ready3();
                } );

                $elt.datatable( 'refresh', largeDataset );
            } );
        } );
        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ]
        }, dataset );
    } );

    QUnit.test( 'Model loading with the "action" type property using predefined data', function( assert ) {
        var ready3 = assert.async();
        var ready2 = assert.async();
        var ready1 = assert.async(2);
        assert.expect( 15 );

        var $elt = $( '#container-1' );
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        var ready = assert.async();

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.equal( $elt.find( '.datatable thead th' ).length, 8, 'the table contains 8 heads elements' );
            assert.equal( $elt.find( '.datatable thead th:eq(6) div' ).text(), 'Pause', 'the Pause label is created' );
            assert.equal( $elt.find( '.datatable thead th:eq(7) div' ).text(), 'Administration', 'the Administration label is created' );

            $( '[data-item-identifier="1"] button.run:eq(0)', $elt ).trigger( 'click' );
            $( '[data-item-identifier="3"] button.run:eq(0)', $elt ).trigger( 'click' );
            $( '[data-item-identifier="2"] button.pause:eq(1)', $elt ).click();
            $( '[data-item-identifier="2"] button.pause:eq(0)', $elt ).click();

            ready();
        } );
        $elt.on( 'query.datatable', function( event ) {
            assert.ok( false, 'the query event must not be triggered!' );
        } );
        $elt.on( 'beforeload.datatable', function( event, response ) {
            assert.equal( typeof response, 'object', 'the beforeload event is triggered and provides the response data' );
            ready1();
        } );
        $elt.one( 'load.datatable', function( event, response ) {
            assert.equal( typeof response, 'object', 'the load event is triggered and provides the response data' );
            assert.equal( $elt.find( '.datatable tbody tr' ).length, dataset.data.length, 'the lines from the small dataset are rendered' );

            ready2();

            // *** Check the refresh with predefined data
            _.defer( function() {
                $elt.one( 'load.datatable', function( event, response ) {
                    assert.equal( typeof response, 'object', 'the load event is triggered and provides the response data' );
                    assert.equal( $elt.find( '.datatable tbody tr' ).length, largeDataset.data.length, 'the lines from the large dataset are rendered' );
                    ready3();
                } );

                $elt.datatable( 'refresh', largeDataset );
            } );
        } );
        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            }, {
                id: 'pauseCl',
                label: 'Pause',
                type: 'actions',
                actions: [ {
                    id: 'pause',
                    icon: 'pause',
                    label: 'Pause me',
                    title: 'Press to pause process',
                    action: function( id ) {
                        assert.ok( true, 'In the pause action, id: ' + id );
                    }
                } ]
            }, {
                id: 'administration',
                label: 'Administration',
                type: 'actions',
                actions: [ {
                    id: 'run',
                    icon: 'play',
                    label: 'Play',
                    title: 'Run action',
                    action: function( id ) {
                        assert.ok( true, 'In the run action, id: ' + id );
                    }
                }, {
                    id: 'pause',
                    icon: 'pause',
                    label: 'Pause me',
                    title: 'Press to pause process',
                    action: function( id ) {
                        assert.ok( true, 'In the pause action, id: ' + id );
                    }
                }, {
                    id: 'stop',
                    icon: 'stop',
                    label: 'Stop',
                    title: 'Press to stop process',
                    action: function() {
                        assert.ok( true, 'In the stop action' );
                    }
                } ]

            } ]
        }, dataset );
    } );

    QUnit.test( 'Model loading with actions column', function( assert ) {
        var ready3 = assert.async();
        var ready2 = assert.async();
        var ready1 = assert.async(2);
        assert.expect( 13 );

        var $elt = $( '#container-1' );
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        var ready = assert.async();

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.equal( $elt.find( '.datatable thead th' ).length, 7, 'the table contains 7 heads elements' );
            assert.equal( $elt.find( '.datatable thead th:eq(6)' ).text(), 'Actions', 'the Actions label is created' );

            $( '[data-item-identifier="1"] button.run:eq(0)', $elt ).trigger( 'click' );
            $( '[data-item-identifier="3"] button.run:eq(0)', $elt ).trigger( 'click' );
            $( '[data-item-identifier="2"] button.pause:eq(1)', $elt ).click();
            $( '[data-item-identifier="2"] button.pause:eq(0)', $elt ).click();

            ready();
        } );
        $elt.on( 'query.datatable', function( event ) {
            assert.ok( false, 'the query event must not be triggered!' );
        } );
        $elt.on( 'beforeload.datatable', function( event, response ) {
            assert.equal( typeof response, 'object', 'the beforeload event is triggered and provides the response data' );
            ready1();
        } );
        $elt.one( 'load.datatable', function( event, response ) {
            assert.equal( typeof response, 'object', 'the load event is triggered and provides the response data' );
            assert.equal( $elt.find( '.datatable tbody tr' ).length, dataset.data.length, 'the lines from the small dataset are rendered' );

            ready2();

            // *** Check the refresh with predefined data
            _.defer( function() {
                $elt.one( 'load.datatable', function( event, response ) {
                    assert.equal( typeof response, 'object', 'the load event is triggered and provides the response data' );
                    assert.equal( $elt.find( '.datatable tbody tr' ).length, largeDataset.data.length, 'the lines from the large dataset are rendered' );
                    ready3();
                } );

                $elt.datatable( 'refresh', largeDataset );
            } );
        } );
        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            actions: [ {
                id: 'run',
                icon: 'play',
                label: 'Play',
                title: 'Run action',
                action: function( id ) {
                    assert.ok( true, 'In the run action, id: ' + id );
                }
            }, {
                id: 'pause',
                icon: 'pause',
                label: 'Pause me',
                title: 'Press to pause process',
                action: function( id ) {
                    assert.ok( true, 'In the pause action, id: ' + id );
                }
            }, {
                id: 'stop',
                icon: 'stop',
                label: 'Stop',
                title: 'Press to stop process',
                action: function() {
                    assert.ok( true, 'In the stop action' );
                }
            } ],
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ]
        }, dataset );
    } );

    QUnit.test( 'Data rendering', function( assert ) {
        var ready3 = assert.async(2);
        var ready2 = assert.async(2);
        var ready1 = assert.async();
        assert.expect( 13 );

        var renderCalled = false;
        var $elt = $( '#container-1' );
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        var ready = assert.async();

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.ok( $elt.find( '.datatable thead th' ).length === 6, 'the table contains 6 heads elements' );
            assert.equal( $elt.find( '.datatable thead th:eq(0) div' ).text(), 'Login', 'the login label is created' );
            assert.equal( $elt.find( '.datatable thead th:eq(1) div' ).text(), 'Name', 'the name label is created' );
            assert.equal( $elt.find( '.datatable thead th:eq(0) div' ).data( 'sort-by' ), 'login', 'the login col is sortable' );
            ready();
        } );
        $elt.on( 'query.datatable', function( event, ajaxConfig ) {
            assert.equal( typeof ajaxConfig, 'object', 'the query event is triggered and provides an object' );
            assert.equal( typeof ajaxConfig.url, 'string', 'the query event provides an object containing the target url' );
            assert.equal( typeof ajaxConfig.data, 'object', 'the query event provides an object containing the request parameters' );
            ready1();
        } );
        $elt.on( 'beforeload.datatable', function( event, response ) {
            assert.equal( typeof response, 'object', 'the beforeload event is triggered and provides the response data' );
            ready2();
        } );
        $elt.on( 'load.datatable', function( event, response ) {
            assert.equal( typeof response, 'object', 'the load event is triggered and provides the response data' );
            ready3();

            if ( !renderCalled ) {
                renderCalled = true;
                setTimeout( function() {
                    $elt.datatable( 'render', response );
                }, 1 );
            }
        } );
        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ]
        } );
    } );

    QUnit.test( 'Selection disabled', function( assert ) {
        var ready = assert.async();
        assert.expect( 4 );

        var $elt = $( '#container-1' );
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.ok( $elt.find( '.checkboxes' ).length === 0, 'there is no selection checkboxes' );
            assert.ok( $elt.datatable( 'selection' ).length === 0, 'the selection is empty' );
            ready();
        } );
        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ]
        } );
    } );

    QUnit.test( 'Selection enabled', function( assert ) {
        var ready = assert.async();
        assert.expect( 11 );

        var $elt = $( '#container-1' );
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.equal( $elt.find( '.checkboxes' ).length, 4, 'there are selection checkboxes' );
            assert.equal( $elt.datatable( 'selection' ).length, 0, 'the selection is empty' );

            $elt.find( 'td.checkboxes input' ).trigger( 'click' );
            assert.equal( $elt.datatable( 'selection' ).length, 3, 'select each line: the selection is full' );

            $elt.find( 'th.checkboxes input' ).trigger( 'click' );
            assert.equal( $elt.datatable( 'selection' ).length, 0, 'click on the checkall button: the selection is empty' );

            $elt.find( 'th.checkboxes input' ).trigger( 'click' );
            assert.equal( $elt.datatable( 'selection' ).length, 3, 'click on the checkall button: the selection is full' );

            $elt.find( 'td.checkboxes input' ).first().trigger( 'click' );
            assert.equal( $elt.datatable( 'selection' ).length, 2, 'unselect a line: the selection contains all items but the unchecked item' );

            $elt.find( 'th.checkboxes input' ).trigger( 'click' );
            assert.equal( $elt.datatable( 'selection' ).length, 3, 'click on the checkall button: the selection is full' );

            $elt.find( 'td.checkboxes input' ).trigger( 'click' );
            assert.equal( $elt.datatable( 'selection' ).length, 0, 'unselect each line: the selection is empty' );

            $elt.find( 'td.checkboxes input' ).first().trigger( 'click' );
            assert.equal( $elt.datatable( 'selection' ).length, 1, 'select a line: the selection contains only the checked item' );

            ready();
        } );
        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            selectable: true,
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ]
        } );
    } );

    QUnit.test( 'Selectable rows', function( assert ) {
        var ready = assert.async();
        assert.expect( 10 );

        var $elt = $( '#container-1' );
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.ok( $elt.find( '.datatable thead th' ).length === 6, 'the table contains 6 heads elements' );

            $elt.find( '.datatable tbody tr:eq(1) td:eq(1)' ).trigger( 'click' );
        } );

        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            rowSelection: true,
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ],
            listeners: {
                selected: function() {
                    assert.ok( true, 'the handler was attached and caused' );
                    assert.equal( $elt.find( '.datatable tbody tr.selected td:eq(0)' ).text(), 'jdoe', 'the login field in selected row is correct' );
                    assert.equal( $elt.find( '.datatable tbody tr.selected td:eq(1)' ).text(), 'John Doe', 'the name field in selected row is correct' );
                    assert.equal( $elt.find( '.datatable tbody tr.selected td:eq(2)' ).text(), 'jdoe@nowhere.org', 'the mail field in selected row is correct' );
                    assert.equal( $elt.find( '.datatable tbody tr.selected td:eq(3)' ).text(), 'Items Manager', 'the roles field in selected row is correct' );
                    assert.equal( $elt.find( '.datatable tbody tr.selected td:eq(4)' ).text(), 'English', 'the dataLg field in selected row is correct' );
                    assert.equal( $elt.find( '.datatable tbody tr.selected td:eq(5)' ).text(), 'English', 'the guiLg field in selected row is correct' );
                    ready();
                }
            }
        } );
    } );

    QUnit.test( 'Default filtering enabled', function( assert ) {
        var ready = assert.async();
        assert.expect( 8 );

        var $elt = $( '#container-1' );
        assert.ok( $elt.length === 1, 'Test the fixture is available' );
        var dom;

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.ok( $elt.find( '.datatable thead th' ).length === 6, 'the table contains 6 heads elements' );
            assert.ok( $elt.find( '.datatable-wrapper aside.filter' ).length, 'the filter is enabled' );

            $elt.find( '.datatable-wrapper aside.filter input' ).val( 'abcdef' );
            $elt.find( '.datatable-wrapper aside.filter button' ).trigger( 'click' );
            dom = $elt.find( 'tbody' ).get();
        } );

        $elt.on( 'filter.datatable', function( event, options ) {
            assert.equal( options.filterquery, 'abcdef', 'the filter set right search query' );
            assert.deepEqual( options.filtercolumns, [ 'login', 'name' ], 'the filter set right columns' );
            $elt.on( 'load.datatable', function() {
                assert.equal( $elt.find( '.datatable-wrapper aside.filter input' ).hasClass( 'focused' ), true, 'the filter is focusable after refreshing' );
                assert.notEqual( dom, $elt.find( 'tbody' ).get(), 'content has been changed' );
                ready();
            } );
        } );

        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            filter: {
                columns: [ 'login', 'name' ]
            },
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ]
        } );
    } );

    QUnit.test( 'Column filtering (input) enabled', function( assert ) {
        var ready = assert.async();
        assert.expect( 10 );

        var $elt = $( '#container-1' );
        var dom;
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.ok( $elt.find( '.datatable thead th' ).length === 6, 'the table contains 6 heads elements' );
            assert.equal( $elt.find( '.datatable thead th:eq(0) aside.filter' ).data( 'column' ), 'login', 'the login col is filterable' );
            assert.equal( $elt.find( '.datatable thead th:eq(2) aside.filter' ).data( 'column' ), 'email', 'the email col is filterable' );
            assert.equal( $elt.find( '.datatable thead th:eq(2) input' ).attr( 'placeholder' ), 'Search by email', 'Email filter input has right placeholder' );

            dom = $elt.find( 'tbody' ).get();
            $elt.find( 'aside.filter[data-column="login"] input' ).val( 'abcdef' );
            $elt.find( 'aside.filter[data-column="login"] button' ).trigger( 'click' );
        } );

        $elt.on( 'filter.datatable', function( event, options ) {
            assert.equal( options.filtercolumns, 'login', 'the filter set right column' );
            assert.equal( options.filterquery, 'abcdef', 'the filter set right search query' );
            assert.notEqual( dom, $elt.find( 'tbody' ).get(), 'content has been changed' );
            $elt.on( 'load.datatable', function() {
                assert.equal( $elt.find( 'aside.filter[data-column="login"] input' ).hasClass( 'focused' ), true, 'the login column filter is focusable after refreshing' );
                ready();
            } );

        } );

        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            filter: true,
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true,
                filterable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true,
                filterable: {
                    placeholder: 'Search by email'
                }
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ]
        } );
    } );

    QUnit.test( 'Column filtering (select) enabled', function( assert ) {
        var ready = assert.async();
        assert.expect( 10 );

        var $elt = $( '#container-1' );
        var dom;
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.ok( $elt.find( '.datatable thead th' ).length === 6, 'the table contains 6 heads elements' );
            assert.equal( $elt.find( '.datatable thead th:eq(1) aside.filter' ).data( 'column' ), 'name', 'the name col is filterable' );
            assert.equal( $elt.find( '.datatable thead th:eq(2) aside.filter' ).data( 'column' ), 'email', 'the email col is filterable' );
            dom = $elt.find( 'tbody' ).get();

            assert.ok( $elt.find( 'aside.filter[data-column="name"] select' ).hasClass( 'test' ), 'filter callback has been called' );

            $elt.find( 'aside.filter[data-column="name"] select' ).val( 'John Doe' );
            $elt.find( 'aside.filter[data-column="name"] select' ).trigger( 'change' );
        } );

        $elt.on( 'filter.datatable', function( event, options ) {
            assert.equal( options.filtercolumns, 'name', 'the filter set right column' );
            assert.equal( options.filterquery, 'John Doe', 'the filter set right search query' );
            $elt.on( 'load.datatable', function() {
                assert.equal( $elt.find( 'aside.filter[data-column="name"] select' ).val(), 'John Doe', 'the name column filter has proper value after refreshing' );
                assert.notEqual( dom, $elt.find( 'tbody' ).get(), 'content has been changed' );
                ready();
            } );

        } );

        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            filter: true,
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true,
                filterable: true,
                customFilter: {
                    template: '<select><option selected></option><option value="Smith">Smith</option><option value="John Doe">Doe</option> </select>',
                    callback: function( $filter ) { $filter.addClass( "test" );}
                }
            }, {
                id: 'email',
                label: 'Email',
                sortable: true,
                filterable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ]
        } );
    } );

    QUnit.test( 'Transform', function( assert ) {
        var ready = assert.async(2);
        var ready1 = assert.async();

        var $elt = $( '#container-1' );
        var renderFullName = function( row ) {
            return row.firstname + ' ' + row.lastname;
        };
        var transform = function( value, row, field, index, data ) {
            assert.equal( typeof row, 'object', 'The row is provided' );
            assert.equal( typeof field, 'object', 'The field is provided' );
            assert.equal( typeof index, 'number', 'The row index is provided' );
            assert.equal( typeof data, 'object', 'The dataset is provided' );
            assert.equal( data, dataset, 'The provided dataset is the right dataset' );

            assert.equal( row, dataset[ index ], 'The provided row is the exact row at index' );
            assert.equal( typeof field.id, 'string', 'The field id is provided' );
            assert.equal( value, row[ field.id ], 'The right value is provided' );

            ready();
            return renderFullName( row );
        };
        var model = [ {
            id: 'fullName',
            label: 'Full name',
            transform: transform
        }, {
            id: 'email',
            label: 'Email'
        } ];
        var dataset = [ {
            id: 1,
            firstname: 'John',
            lastname: 'Smith',
            email: 'john.smith@mail.com'
        }, {
            id: 1,
            firstname: 'Jane',
            lastname: 'Doe',
            email: 'jane.doe@mail.com'
        } ];

        assert.expect( 26 );

        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.ok( $elt.find( '.datatable thead th' ).length === 2, 'the table contains 2 heads elements' );
            assert.equal( $elt.find( '.datatable thead th:eq(0)' ).text().trim(), model[ 0 ].label, 'The first column contains the right header' );
            assert.equal( $elt.find( '.datatable thead th:eq(1)' ).text().trim(), model[ 1 ].label, 'The second column contains the right header' );

            assert.equal( $elt.find( '.datatable tbody tr' ).length, dataset.length, 'The table contains the same lines number as in the dataset' );

            assert.equal( $elt.find( '.datatable tbody tr:eq(0) td:eq(0)' ).text().trim(), renderFullName( dataset[ 0 ] ), 'The first line contains the right full name' );
            assert.equal( $elt.find( '.datatable tbody tr:eq(0) td:eq(1)' ).text().trim(), dataset[ 0 ].email, 'The first line contains the right email' );

            assert.equal( $elt.find( '.datatable tbody tr:eq(1) td:eq(0)' ).text().trim(), renderFullName( dataset[ 1 ] ), 'The second line contains the right full name' );
            assert.equal( $elt.find( '.datatable tbody tr:eq(1) td:eq(1)' ).text().trim(), dataset[ 1 ].email, 'The second line contains the right email' );

            ready1();
        } );

        $elt.datatable( {
            model: model
        }, {
            data: dataset
        } );
    } );

    QUnit.test( 'Endless listeners on events', function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        var $elt = $( '#container-1' );
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        $elt.on( 'create.datatable', function() {
            assert.ok( $elt.find( '.datatable' ).length === 1, 'the layout has been inserted' );
            assert.ok( $elt.find( '.datatable thead th' ).length === 6, 'the table contains 6 heads elements' );

            // Run listener
            $elt.find( '.datatable tbody tr:eq(1) td:eq(1)' ).trigger( 'click' );

            // Sort list
            // and here we had render once again
            $elt.find( '.datatable thead tr:nth-child(1) th:eq(0) div' ).click();
        } );

        $elt.datatable( {
            url: 'js/test/ui/datatable/data.json',
            rowSelection: true,
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ],
            listeners: {
                selected: function selectRow( e ) {
                    assert.ok( true, 'the handler was attached and caused' );
                },
                sort: function() {
                    $elt.on( 'load.datatable', function() {
                        $elt.find( '.datatable tbody tr:eq(1) td:eq(1)' ).trigger( 'click' );
                        ready();
                    } );
                }
            }
        } );
    } );

    QUnit.test( 'Beforeload event', function( assert ) {
        var ready = assert.async();
        assert.expect( 5 );

        var firstLoad = true;
        var dataSetRef;

        var $elt = $( '#container-1' );
        assert.ok( $elt.length === 1, 'Test the fixture is available' );

        $elt.on( 'beforeload.datatable', function( e, loadedDataSet ) {
            if ( firstLoad ) {
                assert.equal( typeof loadedDataSet, 'object', 'The beforeload gives us an object' );
                assert.deepEqual( loadedDataSet, dataset, 'The dataset is correct' );

                dataSetRef = loadedDataSet;
                firstLoad = false;
                $elt.datatable( 'refresh' );
            } else {
                assert.ok( loadedDataSet !== dataSetRef, 'The given dataset is a copy' );
                assert.deepEqual( loadedDataSet, dataSetRef, 'The dataset is correct' );
                ready();
            }
        } )
        .datatable( {
            url: 'js/test/ui/datatable/data.json',
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label:'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            } ]
        } );
    } );

    QUnit.test( 'Sortable headers', function( assert ) {
        var ready = assert.async();
        var $container = $( '#container-1' );

        assert.expect( 14 );

        assert.equal( $container.length, 1, 'Test the fixture is available' );

        $container.on( 'create.datatable', function() {

            var $loginHead = $( '.datatable thead th:nth-child(1) > div', $container );
            var $emailHead = $( '.datatable thead th:nth-child(3) > div', $container );

            $loginHead.trigger( 'click' );

            assert.equal( $loginHead.length, 1, 'The login head exists' );
            assert.equal( $loginHead.text().trim(), 'Login', 'The login head contains the right text' );
            assert.ok( $loginHead.hasClass( 'sortable' ), 'The login column is sortable' );
            assert.equal( $loginHead.data( 'sort-by' ), 'login', 'The sort by data is correct' );
            assert.equal( $loginHead.data( 'sort-type' ), 'string', 'The sort type is correct' );

            assert.equal( $emailHead.length, 1, 'The email head exists' );
            assert.equal( $emailHead.text().trim(), 'Email', 'The email head contains the right text' );
            assert.ok( !$emailHead.hasClass( 'sortable' ), 'The email column is not sortable' );
            assert.ok( !$emailHead.data( 'sort-by' ), 'The sort by data does not exist' );
            assert.ok( !$emailHead.data( 'sort-type' ), 'The sort type does not exist' );

        } ).on( 'sort.datatable', function( e, sortby, sortorder, sorttype ) {

            assert.equal( sortby, 'login', 'The sort by data passed via event' );
            assert.notEqual( sortorder, undefined, 'The sort order passed via event' );
            assert.equal( sorttype, 'string', 'The sort type passed via event' );

            ready();
        } )
        .datatable( {
            url: 'js/test/ui/datatable/data.json',
            'model': [ {
                id: 'login',
                label: 'Login',
                sortable: true,
                sorttype: 'string'
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: false
            } ]
        } );
    } );

    QUnit.test( 'Hidden columns', function( assert ) {
        var ready = assert.async();
        var $container = $( '#container-1' );

        assert.expect( 5 );

        assert.equal( $container.length, 1, 'Test the fixture is available' );

        $container.on( 'create.datatable', function() {

            var $headerCells = $( '.datatable thead th', $container );

            assert.equal( $headerCells.length, 3, 'The login head exists' );
            assert.equal( $headerCells.eq( 0 ).text().trim(), 'Login' );
            assert.equal( $headerCells.eq( 1 ).text().trim(), 'Email' );
            assert.equal( $headerCells.eq( 2 ).text().trim(), 'Data Language' );

            ready();
        } )
            .datatable( {
                url: 'js/test/ui/datatable/data.json',
                'model': [ {
                    id: 'login',
                    label: 'Login',
                    sortable: true,
                    visible: true
                }, {
                    id: 'name',
                    label: 'Name',
                    sortable: true,
                    visible: false
                }, {
                    id: 'email',
                    label: 'Email',
                    sortable: false
                }, {
                    id: 'roles',
                    label: 'Roles',
                    sortable: false,
                    visible: function() {
                        return false;
                    }
                }, {
                    id: 'guiLg',
                    label: 'Data Language',
                    sortable: false,
                    visible: function() {
                        return true;
                    }
                } ]
            } );
    } );
} );
