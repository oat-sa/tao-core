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


    var dialogApi = [
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
        .cases(dialogApi)
        .test('instance API ', function(data, assert) {
            var instance = mediaplayer();
            assert.equal(typeof instance[data.name], 'function', 'The mediaplayer instance exposes a "' + data.title + '" function');
        });


    QUnit.test('audio player DOM', function(assert) {
        var url = '/audio.mp3';
        var instance = mediaplayer({
            url: url,
            type: 'audio/mp3'
        });
        var $dom = instance.render();
        var $player = $dom.find('.player');
        var $controls = $dom.find('.controls');
        var $overlay = $player.find('.overlay');
        var $media = $player.find('.media');
        var $source = $media.find('source');

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
    });


    QUnit.test('video player DOM', function(assert) {
        var url = '/video.mp4';
        var instance = mediaplayer({
            url: url,
            type: 'video/mp4'
        });
        var $dom = instance.render();
        var $player = $dom.find('.player');
        var $controls = $dom.find('.controls');
        var $overlay = $player.find('.overlay');
        var $media = $player.find('.media');
        var $source = $media.find('source');

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
    });


    QUnit.test('youtube player DOM', function(assert) {
        var videoId = 'YJWSVUPSQqw';
        var url = 'https://www.youtube.com/watch?v=' + videoId;
        var instance = mediaplayer({
            url: 'https://www.youtube.com/watch?v=' + videoId,
            type: 'video/youtube'
        });
        var $dom = instance.render();
        var $player = $dom.find('.player');
        var $controls = $dom.find('.controls');
        var $overlay = $player.find('.overlay');
        var $media = $player.find('.media');

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
    });
});
