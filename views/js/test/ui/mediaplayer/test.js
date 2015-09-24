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
define([
    'jquery',
    'lodash',
    'core/promise',
    'ui/mediaplayer'
], function($, _, Promise, mediaplayer) {
    'use strict';

    QUnit.module('mediaplayer');


    QUnit.test('module', 3, function(assert) {
        assert.equal(typeof mediaplayer, 'function', "The mediaplayer module exposes a function");
        assert.equal(typeof mediaplayer(), 'object', "The mediaplayer factory produces an object");
        assert.notStrictEqual(mediaplayer(), mediaplayer(), "The mediaplayer factory provides a different object on each call");
    });


    var mediaplayerApi = [
        { name : 'init', title : 'init' },
        { name : 'destroy', title : 'destroy' },
        { name : 'render', title : 'render' },
        { name : 'seek', title : 'seek' },
        { name : 'play', title : 'play' },
        { name : 'stop', title : 'stop' },
        { name : 'pause', title : 'pause' },
        { name : 'resume', title : 'resume' },
        { name : 'loop', title : 'loop' },
        { name : 'mute', title : 'mute' },
        { name : 'unmute', title : 'unmute' },
        { name : 'setVolume', title : 'setVolume' },
        { name : 'getVolume', title : 'getVolume' },
        { name : 'getPosition', title : 'getPosition' },
        { name : 'getDuration', title : 'getDuration' },
        { name : 'getTimesPlayed', title : 'getTimesPlayed' },
        { name : 'resize', title : 'resize' },
        { name : 'is', title : 'is' },
        { name : 'enable', title : 'enable' },
        { name : 'disable', title : 'disable' },
        { name : 'show', title : 'show' },
        { name : 'hide', title : 'hide' },
        { name : 'trigger', title : 'trigger' },
        { name : 'on', title : 'on' },
        { name : 'off', title : 'off' },
        { name : 'getDom', title : 'getDom' },
        { name : 'addSource', title : 'addSource' }
    ];

    QUnit
        .cases(mediaplayerApi)
        .test('API ', function(data, assert) {
            var instance = mediaplayer();
            assert.equal(typeof instance[data.name], 'function', 'The mediaplayer instance exposes a "' + data.title + '" function');
        });


    QUnit.asyncTest('DOM [audio player]', function(assert) {
        var url = 'js/test/ui/mediaplayer/samples/audio.mp3';
        var $container = $('#fixture-1');
        var instance = mediaplayer({
            url: url,
            type: 'audio/mp3',
            renderTo: $container,
            onrender: function($dom) {
                var $player = $dom.find('.player');
                var $controls = $dom.find('.controls');
                var $overlay = $player.find('.overlay');
                var $media = $player.find('.media');
                var $source = $media.find('source');

                assert.ok(true, 'The media player has trigger the render event');

                assert.equal($container.find('.mediaplayer').length, 1, 'The media player has been inserted into the page');

                assert.equal(typeof $dom, 'object', 'The rendered content is returned by the render() method');
                assert.notEqual(typeof $dom.jquery, 'undefined', 'The rendered content is returned as a jQuery selection by the render() method');
                assert.equal($dom.length, 1, 'The rendered content contains a root element');

                assert.equal($player.length, 1, 'The rendered content contains a player element');
                assert.equal($player.find('.audio').length, 1, 'The player is related to audio');

                assert.equal($media.length, 1, 'The rendered content contains a media element');
                assert.equal($media.is('audio'), true, 'The rendered content uses an audio tag to embed the audio track');

                assert.equal($source.length, 1, 'The rendered content contains an audio source');
                assert.equal($source.attr('src'), url, 'Audio source targets the right URL');

                assert.equal($overlay.length, 1, 'The rendered content contains an overlay element');
                assert.equal($overlay.find('[data-control="play"]').length, 1, 'The overlay element contains a play control');
                assert.equal($overlay.find('[data-control="pause"]').length, 1, 'The overlay element contains a pause control');

                assert.equal($controls.length, 1, 'The rendered content contains a controls element');
                assert.equal($controls.find('.bar').length, 1, 'The controls element contains a bar element');
                assert.equal($controls.find('[data-control="play"]').length, 1, 'The controls element contains a play control');
                assert.equal($controls.find('[data-control="pause"]').length, 1, 'The controls element contains a pause control');
                assert.equal($controls.find('[data-control="time-cur"]').length, 1, 'The controls element contains a current position control');
                assert.equal($controls.find('[data-control="time-end"]').length, 1, 'The controls element contains a duration control');
                assert.equal($controls.find('[data-control="mute"]').length, 1, 'The controls element contains a mute control');
                assert.equal($controls.find('[data-control="unmute"]').length, 1, 'The controls element contains an unmute control');
                assert.equal($controls.find('.seek .slider').length, 1, 'The controls element contains seek slider control');
                assert.equal($controls.find('.volume .slider').length, 1, 'The controls element contains volume slider control');

                _.defer(function() {
                    instance.destroy();

                    assert.equal($container.find('.mediaplayer').length, 0, 'The media player has been removed by the destroy action');

                    QUnit.start();
                });
            }
        });
    });


    QUnit.asyncTest('DOM [video player]', function(assert) {
        var url = 'js/test/ui/mediaplayer/samples/video.mp4';
        var $container = $('#fixture-2');
        var instance = mediaplayer({
            url: url,
            type: 'video/mp4',
            renderTo: $container,
            onrender: function($dom) {
                var $player = $dom.find('.player');
                var $controls = $dom.find('.controls');
                var $overlay = $player.find('.overlay');
                var $media = $player.find('.media');
                var $source = $media.find('source');

                assert.ok(true, 'The media player has trigger the render event');

                assert.equal($container.find('.mediaplayer').length, 1, 'The media player has been inserted into the page');

                assert.equal(typeof $dom, 'object', 'The rendered content is returned by the render() method');
                assert.notEqual(typeof $dom.jquery, 'undefined', 'The rendered content is returned as a jQuery selection by the render() method');
                assert.equal($dom.length, 1, 'The rendered content contains a root element');

                assert.equal($player.length, 1, 'The rendered content contains a player element');
                assert.equal($player.find('.video').length, 1, 'The player is related to video');

                assert.equal($media.length, 1, 'The rendered content contains a media element');
                assert.equal($media.is('video'), true, 'The rendered content uses a video tag to embed the movie');

                assert.equal($source.length, 1, 'The rendered content contains a video source');
                assert.equal($source.attr('src'), url, 'Video source targets the right URL');

                assert.equal($overlay.length, 1, 'The rendered content contains an overlay element');
                assert.equal($overlay.find('[data-control="play"]').length, 1, 'The overlay element contains a play control');
                assert.equal($overlay.find('[data-control="pause"]').length, 1, 'The overlay element contains a pause control');

                assert.equal($controls.length, 1, 'The rendered content contains a controls element');
                assert.equal($controls.find('.bar').length, 1, 'The controls element contains a bar element');
                assert.equal($controls.find('[data-control="play"]').length, 1, 'The controls element contains a play control');
                assert.equal($controls.find('[data-control="pause"]').length, 1, 'The controls element contains a pause control');
                assert.equal($controls.find('[data-control="time-cur"]').length, 1, 'The controls element contains a current position control');
                assert.equal($controls.find('[data-control="time-end"]').length, 1, 'The controls element contains a duration control');
                assert.equal($controls.find('[data-control="mute"]').length, 1, 'The controls element contains a mute control');
                assert.equal($controls.find('[data-control="unmute"]').length, 1, 'The controls element contains an unmute control');
                assert.equal($controls.find('.seek .slider').length, 1, 'The controls element contains seek slider control');
                assert.equal($controls.find('.volume .slider').length, 1, 'The controls element contains volume slider control');

                _.defer(function() {
                    instance.destroy();

                    assert.equal($container.find('.mediaplayer').length, 0, 'The media player has been removed by the destroy action');

                    QUnit.start();
                });
            }
        });
    });


    QUnit.asyncTest('DOM [youtube player]', function(assert) {
        var videoId = 'YJWSVUPSQqw';
        var url = '//www.youtube.com/watch?v=' + videoId;
        var $container = $('#fixture-3');
        var instance = mediaplayer({
            url: url,
            type: 'video/youtube',
            renderTo: $container,
            onrender: function($dom) {
                var $player = $dom.find('.player');
                var $controls = $dom.find('.controls');
                var $overlay = $player.find('.overlay');
                var $media = $player.find('.media');

                assert.ok(true, 'The media player has trigger the render event');

                assert.equal($container.find('.mediaplayer').length, 1, 'The media player has been inserted into the page');

                assert.equal(typeof $dom, 'object', 'The rendered content is returned by the render() method');
                assert.notEqual(typeof $dom.jquery, 'undefined', 'The rendered content is returned as a jQuery selection by the render() method');
                assert.equal($dom.length, 1, 'The rendered content contains a root element');

                assert.equal($player.length, 1, 'The rendered content contains a player element');
                assert.equal($player.find('.video').length, 1, 'The player is related to video');

                assert.equal($media.length, 1, 'The rendered content contains a media element');
                assert.equal($media.is('.youtube'), true, 'The rendered content uses a placeholder to embed the youtube player');
                assert.equal($media.data('videoSrc'), url, 'The video source targets the right URL');
                assert.equal($media.data('videoId'), videoId, 'The video ID contains the right identifier');
                assert.equal($media.data('type'), 'youtube', 'The type is youtube');

                assert.equal($overlay.length, 1, 'The rendered content contains an overlay element');
                assert.equal($overlay.find('[data-control="play"]').length, 1, 'The overlay element contains a play control');
                assert.equal($overlay.find('[data-control="pause"]').length, 1, 'The overlay element contains a pause control');

                assert.equal($controls.length, 1, 'The rendered content contains a controls element');
                assert.equal($controls.find('.bar').length, 1, 'The controls element contains a bar element');
                assert.equal($controls.find('[data-control="play"]').length, 1, 'The controls element contains a play control');
                assert.equal($controls.find('[data-control="pause"]').length, 1, 'The controls element contains a pause control');
                assert.equal($controls.find('[data-control="time-cur"]').length, 1, 'The controls element contains a current position control');
                assert.equal($controls.find('[data-control="time-end"]').length, 1, 'The controls element contains a duration control');
                assert.equal($controls.find('[data-control="mute"]').length, 1, 'The controls element contains a mute control');
                assert.equal($controls.find('[data-control="unmute"]').length, 1, 'The controls element contains an unmute control');
                assert.equal($controls.find('.seek .slider').length, 1, 'The controls element contains seek slider control');
                assert.equal($controls.find('.volume .slider').length, 1, 'The controls element contains volume slider control');

                _.defer(function() {
                    instance.destroy();

                    assert.equal($container.find('.mediaplayer').length, 0, 'The media player has been removed by the destroy action');

                    QUnit.start();
                });
            }
        });
    });


    var mediaplayerTypes = [{
        title: 'audio player',
        fixture: '4',
        type: 'audio',
        url: [{
            src: 'js/test/ui/mediaplayer/samples/audio.mp3',
            type: 'audio/mp3'
        }, {
            src: 'js/test/ui/mediaplayer/samples/audio.m4a',
            type: 'audio/m4a'
        }, {
            src: 'js/test/ui/mediaplayer/samples/audio.ogg',
            type: 'audio/ogg'
        }]
    }, {
        title: 'video player',
        fixture: '5',
        type: 'video',
        url: [{
            src: 'js/test/ui/mediaplayer/samples/video.mp4',
            type: 'video/mp4'
        }, {
            src: 'js/test/ui/mediaplayer/samples/video.ogm',
            type: 'video/ogm'
        }, {
            src: 'js/test/ui/mediaplayer/samples/video.webm',
            type: 'video/webm'
        }]
    }, {
        title: 'youtube player / url',
        fixture : '6',
        type: 'youtube',
        url: '//www.youtube.com/watch?v=YJWSVUPSQqw'
    }, {
        title: 'youtube player / id',
        fixture : '6',
        type: 'youtube',
        url: 'YJWSVUPSQqw'
    }];

    QUnit
        .cases(mediaplayerTypes)
        .asyncTest('Events ', function(data, assert) {
            var $container = $('#fixture-' + data.fixture);
            var instance = mediaplayer({
                url: data.url,
                type: data.type,
                startMuted: true
            });

            var events = ['render', 'ready', 'play', 'pause', 'update', 'ended', 'destroy'];
            var checks = (events.length - 1) * 2;

            _(events).forEach(function(event) {
                $container.one(event + '.mediaplayer', function() {
                    assert.ok(true, 'The media player has triggered the ' + event + ' event through the DOM');

                    QUnit.start();
                });
            });

            instance.on('render', function($dom, player) {
                assert.ok(true, 'The media player has triggered the render event');
                assert.equal(typeof $dom, 'object', 'The render event provides the DOM');
                assert.ok($dom.is('.mediaplayer'), 'The provided DOM has the right class');
                assert.equal($dom, instance.getDom(), 'The render event provides the right DOM');
                assert.equal(player, instance, 'The render event provides the instance');

                QUnit.start();
            });

            instance.on('ready', function(player) {
                assert.ok(true, 'The media player has triggered the ready event');
                assert.equal(player, instance, 'The ready event provides the instance');

                player.play();

                QUnit.start();
            });

            instance.on('play', function(player) {
                assert.ok(true, 'The media player has triggered the play event');
                assert.equal(player, instance, 'The play event provides the instance');

                setTimeout(function(){
                    player.pause();
                }, 500);

                QUnit.start();
            });

            instance.on('pause', function(player) {
                assert.ok(true, 'The media player has triggered the pause event');
                assert.equal(player, instance, 'The pause event provides the instance');

                player.stop();

                QUnit.start();
            });

            instance.on('ended', function(player) {
                assert.ok(true, 'The media player has triggered the ended event');
                assert.equal(player, instance, 'The ended event provides the instance');

                player.destroy();

                QUnit.start();
            });

            instance.on('destroy', function(player) {
                assert.ok(true, 'The media player has triggered the destroy event');
                assert.equal(player, instance, 'The destroy event provides the instance');

                QUnit.start();
            });

            if (mediaplayer.canPlay) {
                QUnit.stop(checks);
                instance.render($container);
            } else {
                throw new Error('The browser does not support the ' + data.type + ' player!');
            }
        });


    /**
     * @param {String} config.type - The type of media to play
     * @param {String|Array} config.url - The URL to the media
     * @param {Boolean} [config.autoStart] - The player starts as soon as it is displayed
     * @param {Number} [config.autoStartAt] - The time position at which the player should start
     * @param {Boolean} [config.loop] - The media will be played continuously
     * @param {Boolean} [config.canPause] - The play can be paused
     * @param {Number} [config.maxPlays] - Sets a few number of plays (default: infinite)
     * @param {Number} [config.width] - Sets the width of the player (default: depends on media type)
     * @param {Number} [config.height] - Sets the height of the player (default: depends on media type)
     * @param {Number} [config.volume] - Sets the sound volume (default: 1)
     * @param {Boolean} [config.startMuted] - The player should be initially muted
     * @param {String|jQuery|HTMLElement} [config.renderTo] - An optional container in which renders the player
     * @param {Function} [config.onready] - Event listener called when the player is fully ready
     * @param {Function} [config.onplay] - Event listener called when the playback is starting
     * @param {Function} [config.onpause] - Event listener called when the playback is paused
     * @param {Function} [config.onended] - Event listener called when the playback is ended
     * @param {Function} [config.onupdate] - Event listener called while the player is playing
     * @param {Function} [config.onrender] - Event listener called when the player is rendering
     * @param {Function} [config.ondestroy] - Event listener called when the player is destroying
     */
});
