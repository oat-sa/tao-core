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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define( [  "jquery", "lodash", "core/promise", "ui/dialog" ], function(  $, _, Promise, dialog ) {
    "use strict";

    QUnit.module( "dialog" );

    QUnit.test( "module", function( assert ) {
        assert.equal( typeof dialog, "function", "The dialog module exposes a function" );
        assert.equal( typeof dialog(), "object", "The dialog factory produces an object" );
        assert.notStrictEqual( dialog(), dialog(), "The dialog factory provides a different object on each call" );
    } );

    var dialogApi = [
        { name: "init", title: "init" },
        { name: "destroy", title: "destroy" },
        { name: "setButtons", title: "setButtons" },
        { name: "render", title: "render" },
        { name: "show", title: "show" },
        { name: "hide", title: "hide" },
        { name: "trigger", title: "trigger" },
        { name: "on", title: "on" },
        { name: "off", title: "off" },
        { name: "getDom", title: "getDom" }
    ];

    QUnit
        .cases.init( dialogApi )
        .test( "instance API ", function( data, assert ) {
            var instance = dialog();
            assert.equal( typeof instance[ data.name ], "function", 'The dialog instance exposes a "' + data.title + '" function' );
        } );

    QUnit.test( "install", function( assert ) {
        var ready1 = assert.async();
        var ready = assert.async(4);
        var heading = "heading";
        var message = "test";
        var content = "12345";
        var renderTo = "#qunit-fixture";
        var modal = dialog( {
            heading: heading,
            message: message,
            content: content,
            renderTo: renderTo
        } );
        var expectedEvents = 4;
        var resolvers = [];
        var promises = _.times( expectedEvents, function() {
            return new Promise( function( resolve ) {

                // Extract the resolve function to an array of resolvers
                // because some promised events will occur more than one time.
                // So we need to use anonymous promises, only the quantity matters.
                resolvers.push( resolve );
            } );
        } );
        var resolve = function() {

            // Just resolve one promise
            ( resolvers.pop() )();
            ready();
        };

        Promise.all( promises ).then( function() {
            modal.destroy();
            assert.ok( null === modal.getDom(), "The dialog instance does not have a DOM element anymore" );
            assert.equal( $( renderTo ).children().length, 0, "The container does not contains the dialog box anymore" );

            ready1();
        } );

        modal.on( "opened.modal", function() {

            // This should occur twice
            assert.ok( true, "The dialog box is now visible" );
            resolve();
        } );
        modal.on( "closed.modal", function() {

            // This should occur only once
            assert.ok( true, "The dialog box is now hidden" );
            resolve();
        } );
        modal.on( "create.modal", function() {

            // This should occur only once
            assert.ok( modal.getDom().parent().is( renderTo ), "When rendered, the dialog box is rendered into target element" );
            resolve();
        } );

        assert.equal( typeof modal, "object", "The dialog instance is an object" );
        assert.equal( typeof modal.getDom(), "object", "The dialog instance gets a DOM element" );
        assert.ok( !!modal.getDom().length, "The dialog instance gets a DOM element" );
        assert.equal( modal.getDom().parent().length, 0, "The dialog box is not rendered by default" );
        assert.equal( modal.getDom().find( "h4" ).text(), heading, "The dialog box displays the heading" );
        assert.equal( modal.getDom().find( ".message" ).text(), message, "The dialog box displays the message" );
        assert.equal( modal.getDom().find( ".content" ).text(), content, "The dialog box displays an additional content" );

        modal.render();
        modal.hide();
        modal.show();
    } );

    QUnit.test( "events", function( assert ) {
        var ready1 = assert.async();
        var message = "test";
        var eventRemoved = false;
        var modal = dialog( {
            message: message
        } );

        var ready = assert.async();

        modal.on( "custom", function() {
            if ( eventRemoved ) {
                assert.ok( false, "The dialog box has triggered a removed event" );
            } else {
                assert.ok( true, "The dialog box has triggered the custom event" );
                modal.off( "custom" );
                eventRemoved = true;
                setTimeout( function() {
                    assert.ok( true, "The dialog box has not triggered the remove event" );
                    ready();

                }, 250 );
                modal.trigger( "custom" );
            }
            ready1();
        } );

        assert.equal( typeof modal, "object", "The dialog instance is an object" );
        assert.equal( typeof modal.getDom(), "object", "The dialog instance gets a DOM element" );
        assert.ok( !!modal.getDom().length, "The dialog instance gets a DOM element" );
        assert.equal( modal.getDom().parent().length, 0, "The dialog box is not rendered by default" );
        assert.equal( modal.getDom().find( ".message" ).text(), message, "The dialog box displays the message" );

        modal.trigger( "custom" );
    } );

    QUnit.test( "buttons", function( assert ) {
        var ready5 = assert.async();
        var ready4 = assert.async();
        var ready3 = assert.async();
        var ready2 = assert.async(2);
        var ready1 = assert.async();
        var message = "test";
        var modal = dialog( {
            message: message,
            buttons: "yes,no,ok,cancel",
            onYesBtn: function( event, btn ) {
                assert.ok( "true", "[yes button] The button has been activated" );
                assert.equal( typeof btn, "object", "[yes button] The button descriptor is provided" );
                assert.equal( btn.id, "yes", "[yes button] The right button descriptor is provided" );

                ready();
            },

            onNoBtn: function( event, btn ) {
                assert.ok( "true", "[no button] The button has been activated" );
                assert.equal( typeof btn, "object", "[no button] The button descriptor is provided" );
                assert.equal( btn.id, "no", "[no button] The right button descriptor is provided" );

                ready1();
            },

            onOkBtn: function( event, btn ) {
                assert.ok( "true", "[ok button] The button has been activated" );
                assert.equal( typeof btn, "object", "[ok button] The button descriptor is provided" );
                assert.equal( btn.id, "ok", "[ok button] The right button descriptor is provided" );

                ready2();
            },

            onCancelBtn: function( event, btn ) {
                assert.ok( "true", "[cancel button] The button has been activated" );
                assert.equal( typeof btn, "object", "[cancel button] The button descriptor is provided" );
                assert.equal( btn.id, "cancel", "[cancel button] The right button descriptor is provided" );

                ready3();
            }
        } );

        var ready = assert.async();

        assert.equal( typeof modal, "object", "The dialog instance is an object" );
        assert.equal( typeof modal.getDom(), "object", "The dialog instance gets a DOM element" );
        assert.ok( !!modal.getDom().length, "The dialog instance gets a DOM element" );
        assert.equal( modal.getDom().parent().length, 0, "The dialog box is not rendered by default" );
        assert.equal( modal.getDom().find( ".message" ).text(), message, "The dialog box displays the message" );

        assert.equal( modal.getDom().find( "button" ).length, 4, "The dialog box displays 4 buttons" );
        assert.equal( modal.getDom().find( 'button[data-control="yes"]' ).length, 1, "The dialog box displays a 'yes' button" );
        assert.equal( modal.getDom().find( 'button[data-control="no"]' ).length, 1, "The dialog box displays a 'no' button" );
        assert.equal( modal.getDom().find( 'button[data-control="ok"]' ).length, 1, "The dialog box displays a 'ok' button" );
        assert.equal( modal.getDom().find( 'button[data-control="cancel"]' ).length, 1, "The dialog box displays a 'cancel' button" );

        modal.getDom().find( 'button[data-control="yes"]' ).click();
        modal.getDom().find( 'button[data-control="no"]' ).click();
        modal.getDom().find( 'button[data-control="ok"]' ).click();
        modal.getDom().find( 'button[data-control="cancel"]' ).click();

        modal.setButtons( [ {
            id: "test",
            type: "info",
            icon: "test",
            label: "test"
        } ] ).on( "testbtn.modal", function( event, btn ) {
            assert.ok( "true", "[test button] The button has been activated" );
            assert.equal( typeof btn, "object", "[test button] The button descriptor is provided" );
            assert.equal( btn.id, "test", "[test button] The right button descriptor is provided" );

            ready4();
        } );

        assert.equal( modal.getDom().find( "button" ).length, 1, "The dialog box displays only 1 button" );
        assert.equal( modal.getDom().find( 'button[data-control="test"]' ).length, 1, "The dialog box displays a 'test' button" );
        assert.equal( modal.getDom().find( 'button[data-control="test"]' ).text().trim(), "test", "The dialog box displays has a 'test' label" );
        assert.ok( modal.getDom().find( 'button[data-control="test"]' ).hasClass( "btn-info" ), "The 'test' button has the 'info' class" );
        assert.ok( modal.getDom().find( 'button[data-control="test"]' ).hasClass( "test" ), "The 'test' button has the 'test' class" );
        assert.equal( modal.getDom().find( "button .icon-test" ).length, 1, "The 'test' button has a 'test' icon" );

        modal.getDom().find( 'button[data-control="test"]' ).click();

        modal.setButtons( [ "ok", {
            id: "done",
            type: "info",
            icon: "done",
            label: "done"
        } ] ).on( "donebtn.modal", function( event, btn ) {
            assert.ok( "true", "[done button] The button has been activated" );
            assert.equal( typeof btn, "object", "[done button] The button descriptor is provided" );
            assert.equal( btn.id, "done", "[done button] The right button descriptor is provided" );

            ready5();
        } );

        assert.equal( modal.getDom().find( "button" ).length, 2, "The dialog box displays 2 buttons" );
        assert.equal( modal.getDom().find( 'button[data-control="ok"]' ).length, 1, "The dialog box displays a 'ok' button" );
        assert.equal( modal.getDom().find( 'button[data-control="done"]' ).length, 1, "The dialog box displays a 'done' button" );
        assert.equal( modal.getDom().find( 'button[data-control="done"]' ).text().trim(), "done", "The dialog box displays has a 'done' label" );
        assert.ok( modal.getDom().find( 'button[data-control="done"]' ).hasClass( "btn-info" ), "The 'done' button has the 'info' class" );
        assert.ok( modal.getDom().find( 'button[data-control="done"]' ).hasClass( "done" ), "The 'done' button has the 'done' class" );
        assert.equal( modal.getDom().find( "button .icon-done" ).length, 1, "The 'done' button has a 'done' icon" );

        modal.getDom().find( 'button[data-control="ok"]' ).click();
        modal.getDom().find( 'button[data-control="done"]' ).click();
    } );

    QUnit.test( "destroy", function( assert ) {
        var ready = assert.async();

        var message = "foo";
        var content = "bar";
        var renderTo = "#qunit-fixture";

        var modal = dialog( {
            message: message,
            content: content,
            renderTo: renderTo
        } );

        assert.expect( 4 );

        modal.on( "create.modal", function() {
            assert.equal( $( renderTo + " .modal" ).length, 1, "The modal element is created" );
            assert.equal( $( renderTo + " .message" ).text(), message, "The modal message is correct" );

            modal.destroy();
        } );
        modal.on( "destroy.modal", function() {

            assert.equal( $( renderTo + " .modal" ).length, 1, "The modal element is still there due to the way the modal works" );
            assert.equal( modal.destroyed, true, "The dialog has the destroyed state" );

            ready();
        } );

        modal.render();
    } );
} );
