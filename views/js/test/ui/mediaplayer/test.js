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

    var skipPlaybackIfUnsupported = true;

    QUnit.module('mediaplayer');


    QUnit.test('module', 6, function(assert) {
        assert.equal(typeof mediaplayer, 'function', "The mediaplayer module exposes a function");
        assert.equal(typeof mediaplayer(), 'object', "The mediaplayer factory produces an object");
        assert.equal(typeof mediaplayer.canPlay, 'boolean', "The mediaplayer factory exposes a flag telling if the browser can play video and audio");
        assert.equal(typeof mediaplayer.isMobile, 'boolean', "The mediaplayer factory exposes a flag telling if the browser is a mobile version");
        assert.equal(typeof mediaplayer.isMobileBrowser, 'function', "The mediaplayer factory exposes a function telling if a browser is a mobile version");
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
        { name : 'restart', title : 'restart' },
        { name : 'rewind', title : 'rewind' },
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
        title: 'youtube player',
        fixture : '6',
        type: 'youtube',
        url: 'YJWSVUPSQqw'
    }];

    if (skipPlaybackIfUnsupported && !mediaplayer.canPlay) {
        QUnit.test('Playback', function(assert) {
            assert.ok(true, 'The browser does not support the video or audio tags!');
        });
    } else {
        mediaplayer.youtubePolling = 1000; // large polling window to avoid useless update events

        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Events ', function (data, assert) {
                if (!mediaplayer.canPlay) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                var current = 0;
                var path = [{
                    render: function ($dom, player) {
                        assert.equal(typeof $dom, 'object', 'The render event provides the DOM');
                        assert.ok($dom.is('.mediaplayer'), 'The provided DOM has the right class');
                        assert.equal($dom, instance.getDom(), 'The render event provides the right DOM');
                        assert.equal(player, instance, 'The render event provides the instance');
                    },
                    ready: function (player) {
                        forward();
                        assert.equal(player, instance, 'The ready event provides the instance');

                        assert.ok(true, 'command #1: play()');
                        player.play();
                    }
                }, {
                    play: function (player) {
                        assert.equal(player, instance, 'The play event provides the instance');

                        setTimeout(function () {
                            forward();
                            assert.ok(true, 'command #2: pause()');
                            player.pause();
                        }, 500);
                    },
                    update: true
                }, {
                    pause: function (player) {
                        forward();
                        assert.equal(player, instance, 'The pause event provides the instance');

                        assert.ok(true, 'command #3: resume()');
                        player.resume();
                    },
                    update: true
                }, {
                    play: function (player) {
                        forward();
                        assert.equal(player, instance, 'The play event provides the instance');

                        assert.ok(true, 'command #4: seek(1)');
                        player.seek(1);
                    },
                    update: true
                }, {
                    update: function (player) {
                        forward();
                        assert.equal(player, instance, 'The update event provides the instance');

                        assert.equal(Math.floor(player.player.getPosition()), 1, 'The media player has moved forward to the right position');

                        assert.ok(true, 'command #5: rewind()');
                        player.rewind();
                    },
                    play: true
                }, {
                    update: function (player) {
                        forward();
                        assert.equal(player, instance, 'The update event provides the instance');

                        assert.equal(Math.floor(player.player.getPosition()), 0, 'The media player has restarted from the beginning');

                        assert.ok(true, 'command #6: seek(1)');
                        player.seek(1);
                    },
                    play: true
                }, {
                    update: function (player) {
                        forward();
                        assert.equal(player, instance, 'The update event provides the instance');

                        assert.equal(Math.floor(player.player.getPosition()), 1, 'The media player has moved forward to the right position');

                        assert.ok(true, 'command #7: pause()');
                        player.pause();
                    },
                    play: true
                }, {
                    pause: function (player) {
                        forward();
                        assert.equal(player, instance, 'The pause event provides the instance');

                        assert.ok(true, 'command #8: restart()');
                        player.restart();
                    },
                    update: true
                }, {
                    play: function (player) {
                        forward();
                        assert.equal(player, instance, 'The play event provides the instance');

                        assert.equal(Math.floor(player.player.getPosition()), 0, 'The media player has restarted from the beginning');

                        assert.ok(true, 'command #9: hide()');
                        player.hide();
                    },
                    update: true
                }, {
                    pause: function (player) {
                        forward();
                        assert.equal(player, instance, 'The pause event provides the instance');

                        assert.ok(true, 'command #10: play()');
                        player.play();

                        setTimeout(function () {
                            assert.ok(!player.is('playing'), 'The player cannot be played while hidden!');

                            assert.ok(true, 'command #11: show()');
                            player.show();
                        }, 500);
                    },
                    update: true
                }, {
                    play: function (player) {
                        forward();
                        assert.equal(player, instance, 'The play event provides the instance');

                        assert.ok(true, 'command #12: disable()');
                        player.disable();
                    },
                    update: true
                }, {
                    pause: function (player) {
                        forward();
                        assert.equal(player, instance, 'The pause event provides the instance');

                        assert.ok(true, 'command #13: play()');
                        player.play();

                        setTimeout(function () {
                            assert.ok(!player.is('playing'), 'The player cannot be played while disabled!');

                            assert.ok(true, 'command #14: enable()');
                            player.enable();
                        }, 500);
                    },
                    update: true
                }, {
                    play: function (player) {
                        forward();
                        assert.equal(player, instance, 'The play event provides the instance');

                        assert.ok(true, 'command #15: stop()');
                        player.stop();
                    },
                    update: true
                }, {
                    ended: function (player) {
                        forward();
                        assert.equal(player, instance, 'The ended event provides the instance');

                        assert.ok(true, 'command #16: destroy()');
                        player.destroy();
                    },
                    pause: true,
                    update: true
                }, {
                    destroy: function (player) {
                        forward();
                        assert.equal(player, instance, 'The destroy event provides the instance');

                        QUnit.start();
                    }
                }];

                var forward = function () {
                    current++;
                };

                var checkPath = function (event, args) {
                    var step = path[current];
                    var stepEvent = step && step[event];
                    if (_.isFunction(stepEvent)) {
                        if (!stepEvent.triggered) {
                            stepEvent.triggered = true;
                            assert.ok(true, 'The event ' + event + ' has been triggered!');
                            _.defer(function () {
                                stepEvent.apply(instance, args);
                            });
                        }
                    } else if (!stepEvent) {
                        assert.ok(false, 'The event ' + event + ' was unexpected!');
                    }
                };

                var $container = $('#fixture-' + data.fixture);
                var instance = mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true,
                    onrender: function () {
                        checkPath('render', arguments);
                    },
                    onready: function () {
                        checkPath('ready', arguments);
                    },
                    onplay: function () {
                        checkPath('play', arguments);
                    },
                    onupdate: function () {
                        checkPath('update', arguments);
                    },
                    onpause: function () {
                        checkPath('pause', arguments);
                    },
                    onended: function () {
                        checkPath('ended', arguments);
                    },
                    ondestroy: function () {
                        checkPath('destroy', arguments);
                    }
                });

                var events = ['render', 'ready', 'play', 'pause', 'update', 'ended', 'destroy'];
                var triggered = {};
                _.forEach(events, function (event) {
                    $container.one(event + '.mediaplayer', function () {
                        assert.ok(true, 'The media player has triggered the ' + event + ' event through the DOM');

                        QUnit.start();
                    });

                    instance.on(event, function () {
                        // todo: update eventifier to allow off() for particular handler and add once()
                        if (!triggered[event]) {
                            triggered[event] = true;
                            assert.ok(true, 'The media player has triggered the ' + event + ' event using internal handling');

                            QUnit.start();
                        }
                    });
                });

                QUnit.stop(events.length * 2 + 2);
                instance.render($container);

                $container.on('custom.mediaplayer', function () {
                    assert.ok(true, 'The media player can handle custom events through DOM');
                    QUnit.start();
                });
                instance.on('custom', function () {
                    assert.ok(true, 'The media player can handle custom events internally');
                    QUnit.start();
                });

                instance.trigger('custom');
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option autoStart ', function (data, assert) {
                if (!mediaplayer.canPlay) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true,
                    autoStart: true,
                    renderTo: '#fixture-' + data.fixture,
                    onplay: function (player) {
                        assert.ok(true, 'The media player has auto started the playback');

                        _.defer(function () {
                            player.destroy();
                        });
                    },
                    ondestroy: function () {
                        QUnit.start();
                    }
                });
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option autoStartAt ', function (data, assert) {
                if (!mediaplayer.canPlay) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                var expected = 1;
                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true,
                    autoStartAt: expected,
                    renderTo: '#fixture-' + data.fixture,
                    onplay: function (player) {
                        player.pause();
                        assert.ok(true, 'The media player has auto started the playback');
                        assert.equal(Math.floor(player.player.getPosition()), expected, 'The media player has auto started the playback at the right position');

                        _.defer(function () {
                            player.destroy();
                        });
                    },
                    ondestroy: function () {
                        QUnit.start();
                    }
                });
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option canPause ', function (data, assert) {
                if (!mediaplayer.canPlay) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                QUnit.stop();

                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true,
                    autoStart: true,
                    canPause: false,
                    renderTo: '#fixture-' + data.fixture,
                    onplay: function (player) {
                        assert.ok(true, 'The media player has auto started the playback');

                        player.pause();

                        setTimeout(function () {
                            player.destroy();
                        }, 500);
                    },
                    onpause: function () {
                        assert.ok(false, 'The media player cannot be paused!');
                    },
                    ondestroy: function () {
                        QUnit.start();
                    }
                });

                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true,
                    autoStart: true,
                    canPause: true,
                    renderTo: '#fixture-' + data.fixture,
                    onplay: function (player) {
                        assert.ok(true, 'The media player has auto started the playback');

                        player.pause();
                    },
                    onpause: function (player) {
                        assert.ok(true, 'The media player can be paused');

                        _.defer(function () {
                            player.destroy();
                        });
                    },
                    ondestroy: function () {
                        QUnit.start();
                    }
                });
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option startMuted ', function (data, assert) {
                if (!mediaplayer.canPlay) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                QUnit.stop();

                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true,
                    autoStart: true,
                    renderTo: '#fixture-' + data.fixture,
                    onplay: function (player) {
                        assert.ok(true, 'The media player has auto started the playback');
                        assert.ok(player.player.isMuted(), 'The media player is muted');
                        assert.ok(player.is('muted'), 'The media player is muted');

                        player.pause();

                        _.defer(function () {
                            player.destroy();
                        });
                    },
                    ondestroy: function () {
                        QUnit.start();
                    }
                });

                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: false,
                    autoStart: true,
                    renderTo: '#fixture-' + data.fixture,
                    onplay: function (player) {
                        assert.ok(true, 'The media player has auto started the playback');
                        assert.ok(!player.player.isMuted(), 'The media player is not muted');
                        assert.ok(!player.is('muted'), 'The media player is not muted');

                        player.pause();

                        _.defer(function () {
                            player.destroy();
                        });
                    },
                    ondestroy: function () {
                        QUnit.start();
                    }
                });
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option volume ', function (data, assert) {
                if (!mediaplayer.canPlay) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                var expected = 30;
                mediaplayer({
                    url: data.url,
                    type: data.type,
                    volume: expected,
                    autoStart: true,
                    renderTo: '#fixture-' + data.fixture,
                    onplay: function (player) {
                        assert.ok(true, 'The media player has auto started the playback');
                        assert.equal(player.player.getVolume(), expected, 'The media player has the right volume set');
                        assert.equal(player.getVolume(), expected, 'The media player must provide the right volume');

                        player.pause();

                        _.defer(function () {
                            player.destroy();
                        });
                    },
                    ondestroy: function () {
                        QUnit.start();
                    }
                });
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option loop ', function (data, assert) {
                if (!mediaplayer.canPlay) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                var count = 0;
                var expected = 2;
                mediaplayer({
                    url: data.url,
                    type: data.type,
                    loop: true,
                    autoStart: true,
                    startMuted: true,
                    renderTo: '#fixture-' + data.fixture,
                    onplay: function (player) {
                        assert.ok(true, 'The media player has started the playback');

                        player.seek(player.getDuration());
                    },
                    onended: function (player) {
                        count++;
                        assert.ok(true, 'The media player has finished the playback');
                        assert.equal(player.getTimesPlayed(), count, 'The media player must provide the right number of plays');

                        if (count >= expected) {
                            assert.ok(true, 'The media player has looped the playback');
                            player.loop = false;

                            _.defer(function () {
                                player.destroy();
                            });
                        }
                    },
                    ondestroy: function () {
                        QUnit.start();
                    }
                });
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option maxPlays ', function (data, assert) {
                if (!mediaplayer.canPlay) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                var count = 0;
                var expected = 1;
                var to;
                mediaplayer({
                    url: data.url,
                    type: data.type,
                    maxPlays: expected,
                    autoStart: true,
                    startMuted: true,
                    renderTo: '#fixture-' + data.fixture,
                    onplay: function (player) {
                        assert.ok(true, 'The media player has started the playback');

                        count++;

                        if (count > expected) {
                            assert.ok(false, 'The media player cannot play more than allowed!');

                            if (to) {
                                clearTimeout(to);
                                to = null;
                            }

                            _.defer(function () {
                                player.destroy();
                            });
                        } else {
                            _.defer(function () {
                                player.stop();
                            });
                        }
                    },
                    onended: function (player) {
                        assert.ok(true, 'The media player has finished the playback');
                        assert.equal(player.getTimesPlayed(), count, 'The media player must provide the right number of plays');

                        _.defer(function () {
                            player.play();
                        });
                    },
                    onlimitreached: function (player) {
                        if (player.is('playing') || count > expected) {
                            assert.ok(false, 'The media player must be stopped!');
                        } else {
                            assert.ok(true, 'The media player has stopped the playback after the play limit has been reached');
                        }

                        _.defer(function () {
                            to = setTimeout(function () {
                                player.destroy();
                            }, 500);

                            player.play();
                        });
                    },
                    ondestroy: function () {
                        QUnit.start();
                    }
                });
            });
    }


    QUnit
        .cases(mediaplayerTypes)
        .asyncTest('Option renderTo ', function(data, assert) {
            var selector = '#fixture-' + data.fixture;
            var places = [{
                type: 'jQuery',
                container: $(selector)
            }, {
                type: 'String',
                container: selector
            }, {
                type: 'HTMLElement',
                container: document.getElementById(selector.substr(1))
            }];

            QUnit.stop(places.length - 1);
            _.forEach(places, function(place) {
                mediaplayer({
                    url: data.url,
                    type: data.type,
                    renderTo: place.container,
                    onrender: function($dom, player) {
                        assert.ok($dom.parent().is(selector), 'The media player has been rendered in the container provided using ' + place.type);

                        _.defer(function() {
                            player.destroy();
                        });
                    },
                    ondestroy: function() {
                        QUnit.start();
                    }
                });
            });
        });


    QUnit
        .cases(mediaplayerTypes)
        .asyncTest('Show/Hide ', function(data, assert) {
            var selector = '#fixture-' + data.fixture;
            mediaplayer({
                url: data.url,
                type: data.type,
                renderTo: selector,
                onrender: function($dom, player) {
                    assert.ok($dom.parent().is(selector), 'The media player has been rendered in the container');

                    assert.equal($dom.length, 1, 'the media player exists');
                    assert.ok(!$dom.hasClass('hidden'), 'the media player is displayed');
                    assert.ok($dom.is(':visible'), 'the media player is displayed');

                    _.defer(function() {
                        player.hide();

                        _.defer(function() {
                            assert.ok($dom.hasClass('hidden'), 'the media player is hidden');
                            assert.ok(!$dom.is(':visible'), 'the media player is hidden');

                            player.show();

                            _.defer(function() {
                                assert.ok(!$dom.hasClass('hidden'), 'the media player is displayed');
                                assert.ok($dom.is(':visible'), 'the media player is displayed');

                                player.destroy();
                            });
                        });
                    });
                },
                ondestroy: function() {
                    QUnit.start();
                }
            });
        });


    QUnit
        .cases(mediaplayerTypes)
        .asyncTest('Enable/Disable ', function(data, assert) {
            var selector = '#fixture-' + data.fixture;
            mediaplayer({
                url: data.url,
                type: data.type,
                renderTo: selector,
                onrender: function($dom, player) {
                    assert.ok($dom.parent().is(selector), 'The media player has been rendered in the container');

                    assert.equal($dom.length, 1, 'the media player exists');
                    assert.ok(!$dom.hasClass('disabled'), 'the media player is enabled');

                    _.defer(function() {
                        player.disable();

                        _.defer(function() {
                            assert.ok($dom.hasClass('disabled'), 'the media player is disabled');

                            player.enable();

                            _.defer(function() {
                                assert.ok(!$dom.hasClass('disabled'), 'the media player is enabled');

                                player.destroy();
                            });
                        });
                    });
                },
                ondestroy: function() {
                    QUnit.start();
                }
            });
        });


    var browsers = [{
        title : 'Amazon Kindle Fire',
        userAgent : [
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFOT Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFTT Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFJWI Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFJWA Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFSOWI Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFTHWI Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFTHWA Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFAPWI Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFAPWA Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFARWI Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFASWI Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFSAWI Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; KFSAWA Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version',
            'Mozilla/5.0 (Linux; U; Android android-version; locale; SD4930UR Build/product-build) AppleWebKit/webkit-version (KHTML, like Gecko) Silk/browser-version like Chrome/chrome-version Safari/webkit-version'
        ],
        isMobile : true
    }, {
        title : 'Android Phone',
        userAgent : [
            'Mozilla/5.0 (Linux; <Android Version>; <Build Tag etc.>) AppleWebKit/<WebKit Rev> (KHTML, like Gecko) Chrome/<Chrome Rev> Mobile Safari/<WebKit Rev>',
            'Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
            'Mozilla/5.0 (Linux; U; Android 4.0.3; de-ch; HTC Sensation Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
            'Mozilla/5.0 (Linux; U; Android 2.3; en-us) AppleWebKit/999+ (KHTML, like Gecko) Safari/999.9',
            'Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; HTC_IncredibleS_S710e Build/GRJ90) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.3.5; en-us; HTC Vision Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.3.4; fr-fr; HTC Desire Build/GRJ22) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.3.4; en-us; T-Mobile myTouch 3G Slide Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.3.3; zh-tw; HTC_Pyramid Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.3.3; zh-tw; HTC_Pyramid Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari',
            'Mozilla/5.0 (Linux; U; Android 2.3.3; zh-tw; HTC Pyramid Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.3.3; ko-kr; LG-LU3000 Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.3.3; en-us; HTC_DesireS_S510e Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.3.3; en-us; HTC_DesireS_S510e Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile',
            'Mozilla/5.0 (Linux; U; Android 2.3.3; de-de; HTC Desire Build/GRI40) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.3.3; de-ch; HTC Desire Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.2; fr-lu; HTC Legend Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.2; en-sa; HTC_DesireHD_A9191 Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.2.1; fr-fr; HTC_DesireZ_A7272 Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.2.1; en-gb; HTC_DesireZ_A7272 Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.2.1; en-ca; LG-P505R Build/FRG83) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.2.1; de-de; HTC_Wildfire_A3333 Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.1-update1; es-mx; SonyEricssonE10a Build/2.0.A.0.504) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17',
            'Mozilla/5.0 (Linux; U; Android 1.6; ar-us; SonyEricssonX10i Build/R2BA026) AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1',
            'Mozilla/5.0 (Linux; U; Android 4.1.1; en-gb; Build/KLP) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30',
            'Mozilla/5.0 (Linux; Android 4.4; Nexus 5 Build/_BuildID_) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 5 Build/LMY48B; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/43.0.2357.65 Mobile Safari/537.36'
        ],
        isMobile : true
    }, {
        title : 'Android Tablet',
        userAgent : [
            'Mozilla/5.0 (Linux; <Android Version>; <Build Tag etc.>) AppleWebKit/<WebKit Rev>(KHTML, like Gecko) Chrome/<Chrome Rev> Safari/<WebKit Rev>',
            'Mozilla/5.0 (Linux; Android 4.0.4; Galaxy Nexus Build/IMM76B) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.133 Mobile Safari/535.19'
        ],
        isMobile : true
    }, {
        title : 'Apple iPhone',
        userAgent : [
            'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543 Safari/419.3',
            'Mozilla/5.0 (iPhone; U; CPU iPhone OS 5_1_1 like Mac OS X; en) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/9B206 Safari/7534.48.3'
        ],
        isMobile : true
    }, {
        title : 'Apple iPad',
        userAgent : 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10',
        isMobile : true
    }, {
        title : 'Apple iPod',
        userAgent : 'Mozilla/5.0 (iPod; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A101a Safari/419.3',
        isMobile : true
    }, {
        title : 'Facebook iPhone App',
        userAgent : 'Mozilla/5.0 (iPhone; CPU OS 8_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B410 [FBAN/FBIOS;FBAV/20.1.0.15.10;FBBV/5758778;FBDV/iPad5,4;FBMD/iPad;FBSN/iPhone OS;FBSV/8.1;FBSS/2; FBCR/;FBID/tablet;FBLC/fi_FI;FBOP/1]',
        isMobile : true
    }, {
        title : 'Facebook iPad App',
        userAgent : 'Mozilla/5.0 (iPad; CPU OS 8_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Mobile/12B410 [FBAN/FBIOS;FBAV/20.1.0.15.10;FBBV/5758778;FBDV/iPad5,4;FBMD/iPad;FBSN/iPhone OS;FBSV/8.1;FBSS/2; FBCR/;FBID/tablet;FBLC/fi_FI;FBOP/1]',
        isMobile : true
    }, {
        title : 'BlackBerry',
        userAgent : [
            'Mozilla/5.0 (BB10; Touch) AppleWebKit/537.35+ (KHTML, like Gecko) Version/10.2.0.1791 Mobile Safari/537.35+',
            'Mozilla/5.0 (BlackBerry; U; BlackBerry 9900; en) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.1.0.346 Mobile Safari/534.11+'
        ],
        isMobile : true
    }, {
        title : 'Opera Mini',
        userAgent : 'Opera/9.80 (J2ME/MIDP; Opera Mini/9.80 (S60; SymbOS; Opera Mobi/23.348; U; en) Presto/2.5.25 Version/10.54',
        isMobile : true
    }, {
        title : 'Firefox OS',
        userAgent : 'Mozilla/5.0 (Mobile; rv:14.0) Gecko/14.0 Firefox/14.0',
        isMobile : true
    }, {
        title : 'Chrome',
        userAgent : 'Mozilla/5.0 (Linux; Android 4.4.4; en-us; Nexus 4 Build/JOP40D) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2307.2 Mobile Safari/537.36',
        isMobile : true
    }, {
        title : 'Nexus 7',
        userAgent : 'Mozilla/5.0 (Linux; Android 4.1.1; Nexus 7 Build/JRO03D) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Safari/535.19',
        isMobile : true
    }, {
        title : 'Windows Phone',
        userAgent : 'Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0)',
        isMobile : true
    }, {
        title : 'Windows Tablet',
        userAgent : 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; ARM; Trident/6.0; Touch)',
        isMobile : true
    }, {
        title : 'Windows Touch Laptop',
        userAgent : 'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; Touch; MAGWJS; rv:11.0) like Gecko',
        isMobile : false
    }, {
        title : 'Desktop Mozilla',
        userAgent : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:43.0) Gecko/20100101 Firefox/43.0',
        isMobile : false
    }, {
        title : 'Desktop Chrome',
        userAgent : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.93 Safari/537.36',
        isMobile : false
    }, {
        title : 'Desktop Safari',
        userAgent : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/600.8.9 (KHTML, like Gecko) Version/8.0.8 Safari/600.8.9',
        isMobile : false
    }];

    QUnit
        .cases(browsers)
        .test('isMobile ', function(data, assert) {
            var userAgents = data.userAgent;
            if (!_.isArray(userAgents)) {
                userAgents = [userAgents];
            }

            _.forEach(userAgents, function(userAgent) {
                assert.equal(mediaplayer.isMobileBrowser(userAgent), data.isMobile, userAgent);
            });
        });

});
