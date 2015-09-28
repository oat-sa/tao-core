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
                        forward();
                        assert.equal(player, instance, 'The play event provides the instance');

                        setTimeout(function () {
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

});
