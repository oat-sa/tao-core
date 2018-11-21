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

    var isMobile = !!(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) ||
                    /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4)));


    var isHeadless = /HeadlessChrome/.test(navigator.userAgent);

    QUnit.module('mediaplayer');


    QUnit.test('module', 8, function(assert) {
        assert.equal(typeof mediaplayer, 'function', "The mediaplayer module exposes a function");
        assert.equal(typeof mediaplayer(), 'object', "The mediaplayer factory produces an object");
        assert.equal(typeof mediaplayer.canPlay, 'function', "The mediaplayer factory exposes a function telling if the browser can play video and audio");
        assert.equal(typeof mediaplayer.canPlayAudio, 'function', "The mediaplayer factory exposes a function telling if the browser can play audio");
        assert.equal(typeof mediaplayer.canPlayVideo, 'function', "The mediaplayer factory exposes a function telling if the browser can play video");
        assert.equal(typeof mediaplayer.canControl, 'function', "The mediaplayer factory exposes a function telling if the browser allows to control the media playback");
        assert.equal(typeof mediaplayer.youtubePolling, 'number', "The mediaplayer factory exposes a config entry to set the YouTube polling delay");
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
        { name : 'getContainer', title : 'getContainer' },
        { name : 'getElement', title : 'getElement' },
        { name : 'getSources', title : 'getSources' },
        { name : 'setSource', title : 'setSource' },
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
            renderTo: $container
        });

        instance.on('render', function($dom) {
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
        })
        .on('error', function(err){

            assert.ok(false, err.message);
            QUnit.start();
        });
    });


    QUnit.asyncTest('DOM [video player]', function(assert) {
        var url = 'js/test/ui/mediaplayer/samples/video.mp4';
        var $container = $('#fixture-2');
        var instance = mediaplayer({
            url: url,
            type: 'video/mp4',
            renderTo: $container
        });

        instance.on('render', function($dom) {
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
        })
        .on('error', function(err){

            assert.ok(false, err.message);
            QUnit.start();
        });
    });


    QUnit.asyncTest('DOM [youtube player]', function(assert) {
        var videoId = 'YJWSVUPSQqw';
        var url = '//www.youtube.com/watch?v=' + videoId;
        var $container = $('#fixture-3');
        var instance = mediaplayer({
            url: url,
            type: 'video/youtube',
            renderTo: $container
        });

        instance.on('render', function($dom) {
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
        })
        .on('error', function(err){

            assert.ok(false, err.message);
            QUnit.start();
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
        url: 'YJWSVUPSQqw',
        url2: 'YUHRY27pg8g'
    }];

    if (skipPlaybackIfUnsupported && !mediaplayer.canPlay()) {
        QUnit.test('Playback', function(assert) {
            assert.ok(true, 'The browser does not support the video or audio tags!');
        });
    } else if (skipPlaybackIfUnsupported && (isMobile || isHeadless || !mediaplayer.canControl())) {
        QUnit.test('Playback', function(assert) {
            assert.ok(true, 'The browser does not support autoplay of video or audio!');
        });
    } else {
        mediaplayer.youtubePolling = 1000; // large polling window to avoid useless update events

        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Events ', function (data, assert) {
                if (!mediaplayer.canPlay(data.type)) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                // pointer inside the life cycle path
                var current = 0;

                // the media player life cycle must follow this path during this test
                var path = [{
                    render: function ($dom) {
                        assert.equal(typeof $dom, 'object', 'The render event provides the DOM');
                        assert.ok($dom.is('.mediaplayer'), 'The provided DOM has the right class');
                        assert.equal($dom, instance.getElement(), 'The render event provides the right DOM');
                        assert.equal(this, instance, 'The render event is bound to the instance');
                    },
                    ready: function () {
                        forward();
                        assert.equal(this, instance, 'The ready event is bound to the instance');
                        assert.ok(true, 'command #1: play()');
                        this.play();
                    }
                }, {
                    play: function () {
                        var player = this;
                        assert.equal(this, instance, 'The play event is bound to the instance');

                        setTimeout(function () {
                            forward();
                            assert.ok(true, 'command #2: pause()');
                            player.pause();
                        }, 500);
                    },
                    update: true
                }, {
                    pause: function () {
                        forward();
                        assert.equal(this, instance, 'The pause event is bound to the instance');
                        assert.ok(true, 'command #3: resume()');
                        this.resume();
                    },
                    update: true
                }, {
                    play: function () {
                        forward();
                        assert.equal(this, instance, 'The play event is bound to the instance');
                        assert.ok(true, 'command #4: seek(1)');
                        this.seek(1);
                    },
                    update: true
                }, {
                    update: function () {
                        forward();
                        assert.equal(this, instance, 'The update event is bound to the instance');
                        assert.equal(Math.floor(this.player.getPosition()), 1, 'The media player has moved forward to the right position');
                        assert.ok(true, 'command #5: rewind()');
                        this.rewind();
                    },
                    play: true
                }, {
                    update: function () {
                        forward();
                        assert.equal(this, instance, 'The update event is bound to the instance');
                        assert.equal(Math.floor(this.player.getPosition()), 0, 'The media player has restarted from the beginning');
                        assert.ok(true, 'command #6: seek(1)');
                        this.seek(1);
                    },
                    play: true
                }, {
                    update: function () {
                        forward();
                        assert.equal(this, instance, 'The update event is bound to the instance');
                        assert.equal(Math.floor(this.player.getPosition()), 1, 'The media player has moved forward to the right position');
                        assert.ok(true, 'command #7: pause()');
                        this.pause();
                    },
                    play: true
                }, {
                    pause: function () {
                        forward();
                        assert.equal(this, instance, 'The pause event is bound to the instance');
                        assert.ok(true, 'command #8: restart()');
                        this.restart();
                    },
                    update: true
                }, {
                    play: function () {
                        forward();
                        assert.equal(this, instance, 'The play event is bound to the instance');
                        assert.equal(Math.floor(this.player.getPosition()), 0, 'The media player has restarted from the beginning');
                        assert.ok(true, 'command #9: hide()');
                        this.hide();
                    },
                    update: true
                }, {
                    pause: function () {
                        var player = this;
                        forward();
                        assert.equal(this, instance, 'The pause event is bound to the instance');
                        assert.ok(true, 'command #10: play()');
                        this.play();

                        setTimeout(function () {
                            assert.ok(!player.is('playing'), 'The player cannot be played while hidden!');
                            assert.ok(true, 'command #11: show()');
                            player.show();
                        }, 500);
                    },
                    update: true
                }, {
                    play: function () {
                        forward();
                        assert.equal(this, instance, 'The play event is bound to the instance');
                        assert.ok(true, 'command #12: disable()');
                        this.disable();
                    },
                    update: true
                }, {
                    pause: function () {
                        var player = this;
                        forward();
                        assert.equal(this, instance, 'The pause event is bound to the instance');
                        assert.ok(true, 'command #13: play()');
                        this.play();

                        setTimeout(function () {
                            assert.ok(!player.is('playing'), 'The player cannot be played while disabled!');
                            assert.ok(true, 'command #14: enable()');
                            player.enable();
                        }, 500);
                    },
                    update: true
                }, {
                    play: function () {
                        forward();
                        assert.equal(this, instance, 'The play event is bound to the instance');
                        assert.ok(true, 'command #15: stop()');
                        this.stop();
                    },
                    update: true
                }, {
                    ended: function () {
                        forward();
                        assert.equal(this, instance, 'The ended event is bound to the instance');
                        assert.ok(true, 'command #16: destroy()');
                        this.destroy();
                    },
                    pause: true,
                    update: true
                }, {
                    destroy: function () {
                        forward();
                        assert.equal(this, instance, 'The destroy event is bound to the instance');
                        QUnit.start();
                    }
                }];

                var $container = $('#fixture-' + data.fixture);
                var events = ['render', 'ready', 'play', 'pause', 'update', 'ended', 'destroy'];
                var instance = mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true
                });

                // move to next step into the life cycle path
                function forward() {
                    current++;
                }

                // checks that the media player has correctly reached the current step in the life cycle path
                function checkPath(event, args) {
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
                }

                instance
                    .on('render', function () {
                        checkPath('render', arguments);
                    })
                    .on('ready', function () {
                        checkPath('ready', arguments);
                    })
                    .on('play', function () {
                        checkPath('play', arguments);
                    })
                    .on('update', function () {
                        checkPath('update', arguments);
                    })
                    .on('pause', function () {
                        checkPath('pause', arguments);
                    })
                    .on('ended', function () {
                        checkPath('ended', arguments);
                    })
                    .on('destroy', function () {
                        checkPath('destroy', arguments);
                    });

                _.forEach(events, function (event) {
                    $container.one(event + '.mediaplayer', function () {
                        assert.ok(true, 'The media player has triggered the ' + event + ' event through the DOM');

                        QUnit.start();
                    });
                });

                QUnit.stop(events.length + 2);
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
                if (!mediaplayer.canPlay(data.type)) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true,
                    autoStart: true,
                    renderTo: '#fixture-' + data.fixture
                })
                    .on('play', function () {
                        var player = this;
                        assert.ok(true, 'The media player has auto started the playback');

                        _.defer(function () {
                            player.destroy();
                        });
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    })
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option autoStartAt ', function (data, assert) {
                if (!mediaplayer.canPlay(data.type)) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                var expected = 1;
                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true,
                    autoStartAt: expected,
                    renderTo: '#fixture-' + data.fixture
                })
                    .on('play', function () {
                        var player = this;
                        this.pause();
                        assert.ok(true, 'The media player has auto started the playback');
                        assert.equal(Math.floor(this.player.getPosition()), expected, 'The media player has auto started the playback at the right position');

                        _.defer(function () {
                            player.destroy();
                        });
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option canPause ', function (data, assert) {
                if (!mediaplayer.canPlay(data.type)) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                QUnit.stop();

                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true,
                    autoStart: true,
                    canPause: false,
                    renderTo: '#fixture-' + data.fixture
                })
                    .on('play', function () {
                        var player = this;
                        assert.ok(true, 'The media player has auto started the playback');
                        this.pause();

                        setTimeout(function () {
                            player.destroy();
                        }, 500);
                    })
                    .on('pause', function () {
                        assert.ok(false, 'The media player cannot be paused!');
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true,
                    autoStart: true,
                    canPause: true,
                    renderTo: '#fixture-' + data.fixture
                })
                    .on('play', function () {
                        assert.ok(true, 'The media player has auto started the playback');
                        this.pause();
                    })
                    .on('pause', function () {
                        var player = this;
                        assert.ok(true, 'The media player can be paused');

                        _.defer(function () {
                            player.destroy();
                        });
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option startMuted ', function (data, assert) {
                if (!mediaplayer.canPlay(data.type)) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                QUnit.stop();

                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: true,
                    autoStart: true,
                    renderTo: '#fixture-' + data.fixture
                })
                    .on('play', function () {
                        var player = this;
                        assert.ok(true, 'The media player has auto started the playback');
                        assert.ok(this.player.isMuted(), 'The media player is muted');
                        assert.ok(this.is('muted'), 'The media player is muted');

                        this.pause();

                        _.defer(function () {
                            player.destroy();
                        });
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });

                mediaplayer({
                    url: data.url,
                    type: data.type,
                    startMuted: false,
                    autoStart: true,
                    renderTo: '#fixture-' + data.fixture
                })
                    .on('play', function () {
                        var player = this;
                        assert.ok(true, 'The media player has auto started the playback');
                        assert.ok(!this.player.isMuted(), 'The media player is not muted');
                        assert.ok(!this.is('muted'), 'The media player is not muted');

                        this.pause();

                        _.defer(function () {
                            player.destroy();
                        });
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option volume ', function (data, assert) {
                if (!mediaplayer.canPlay(data.type)) {
                    throw new Error('The browser does not support the ' + data.type + ' player!');
                }

                var expected = 30;
                mediaplayer({
                    url: data.url,
                    type: data.type,
                    volume: expected,
                    autoStart: true,
                    renderTo: '#fixture-' + data.fixture
                })
                    .on('play', function () {
                        var player = this;
                        assert.ok(true, 'The media player has auto started the playback');
                        assert.equal(this.player.getVolume(), expected, 'The media player has the right volume set');
                        assert.equal(this.getVolume(), expected, 'The media player must provide the right volume');

                        this.pause();

                        _.defer(function () {
                            player.destroy();
                        });
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option loop ', function (data, assert) {
                if (!mediaplayer.canPlay(data.type)) {
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
                    renderTo: '#fixture-' + data.fixture
                })
                    .on('play', function () {
                        assert.ok(true, 'The media player has started the playback');

                        this.seek(this.getDuration() - 0.1);
                    })
                    .on('ended', function () {
                        var player = this;
                        count++;
                        assert.ok(true, 'The media player has finished the playback');
                        assert.equal(this.getTimesPlayed(), count, 'The media player must provide the right number of plays');

                        if (count >= expected) {
                            assert.ok(true, 'The media player has looped the playback');
                            this.loop = false;

                            _.defer(function () {
                                player.destroy();
                            });
                        }
                    })
                    .on('destroy', function () {
                        QUnit.start();
                    });
            });


        QUnit
            .cases(mediaplayerTypes)
            .asyncTest('Option maxPlays ', function (data, assert) {
                if (!mediaplayer.canPlay(data.type)) {
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
                    renderTo: '#fixture-' + data.fixture
                })
                    .on('play', function () {
                        var player = this;
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
                    })
                    .on('ended', function () {
                        var player = this;
                        assert.ok(true, 'The media player has finished the playback');
                        assert.equal(this.getTimesPlayed(), count, 'The media player must provide the right number of plays');

                        _.defer(function () {
                            player.play();
                        });
                    })
                    .on('limitreached', function () {
                        var player = this;
                        if (this.is('playing') || count > expected) {
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
                    })
                    .on('destroy', function () {
                        QUnit.start();
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
                    renderTo: place.container
                })
                    .on('render', function($dom) {
                        var player = this;
                        assert.ok($dom.parent().is(selector), 'The media player has been rendered in the container provided using ' + place.type);

                        _.defer(function() {
                            player.destroy();
                        });
                    })
                    .on('destroy', function() {
                        QUnit.start();
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
                renderTo: selector
            })
                .on('render', function($dom) {
                    var player = this;
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
                })
                .on('destroy', function() {
                    QUnit.start();
                });
        });


    QUnit
        .cases(mediaplayerTypes)
        .asyncTest('Enable/Disable ', function(data, assert) {
            var selector = '#fixture-' + data.fixture;
            mediaplayer({
                url: data.url,
                type: data.type,
                renderTo: selector
            })
                .on('render', function($dom) {
                    var player = this;
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
                })
                .on('destroy', function() {
                    QUnit.start();
                });
        });


    QUnit
        .cases(mediaplayerTypes)
        .test('Sources management ', function(data, assert) {
            var url1 = _.isArray(data.url) ? data.url[0] : data.url;
            var url2 = data.url2 || data.url[1];
            var player = mediaplayer({
                type: data.type
            });
            var res = player.getSources();

            assert.equal(res.length, 0, 'The media player has an empty list of sources');

            player.setSource(url1);
            res = player.getSources();

            assert.equal(res.length, 1, 'The media player has one media in its list of sources');
            if ('object' === typeof url1) {
                assert.equal(res[0].src, url1.src, 'The media player has the right media at the first position in its list of sources');
            } else {
                assert.equal(res[0].src, url1, 'The media player has the right media at the first position in its list of sources');
            }

            player.addSource(url2);
            res = player.getSources();

            assert.equal(res.length, 2, 'The media player has two media in its list of sources');
            if ('object' === typeof url2) {
                assert.equal(res[1].src, url2.src, 'The media player has the right media at the second position in its list of sources');
            } else {
                assert.equal(res[1].src, url2, 'The media player has the right media at the second position in its list of sources');
            }

            player.setSource(url1);
            res = player.getSources();

            assert.equal(res.length, 1, 'The media player has one media in its list of sources');
            if ('object' === typeof url1) {
                assert.equal(res[0].src, url1.src, 'The media player has the right media at the first position in its list of sources');
            } else {
                assert.equal(res[0].src, url1, 'The media player has the right media at the first position in its list of sources');
            }

            player.destroy();
        });

});
