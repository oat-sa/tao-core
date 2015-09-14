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
    'i18n',
    'urlParser',
    'tpl!ui/mediaplayer/tpl/player',
    'css!ui/mediaplayer/css/player',
    'nouislider'
], function ($, _, __, UrlParser, playerTpl) {
    'use strict';

    /**
     * CSS namespace
     * @type {String}
     * @private
     */
    var _ns = '.mediaplayer';

    /**
     * A Regex to extract ID from Youtube URLs
     * @type {RegExp}
     * @private
     */
    var _reYoutube = /([?&\/]v[=\/])([\w-]+)([&\/]?)/g;

    /**
     * Array slice method needed to slice arguments
     * @type {Function}
     * @private
     */
    var _slice = [].slice;

    /**
     * Some default values
     * @type {Object}
     * @private
     */
    var _defaults = {
        type : 'video/mp4',
        video : {
            width : 480,
            height : 270
        },
        audio : {
            width : 400,
            height : 30
        },
        options : {
            volume : 80,
            maxPlays : 0,
            canFullscreen : false,
            canPause : true,
            loop : false,
            autoStart : false
        }
    };

    /**
     * Extracts the ID of a Youtube video from an URL
     * @param {String} url
     * @returns {String}
     * @private
     */
    var _extractYoutubeId = function(url) {
        var res = _reYoutube.exec(url);
        return res && res[2];
    };

    /**
     * Format a number to string with leading zeros
     * @param {Number} n
     * @param {Number} len
     * @returns {String}
     * @private
     */
    var _leadingZero = function(n, len) {
        var value = n.toString();
        while (value.length < len) {
            value = '0' + value;
        }
        return value;
    };

    /**
     * Formats a time value to string
     * @param {Number} time
     * @returns {String}
     * @private
     */
    var _timerFormat = function(time) {
        var seconds = Math.floor(time % 60);
        var minutes = Math.floor(time / 60) % 60;
        var hours = Math.floor(time / 3600);
        var parts = [];

        if (hours) {
            parts.push(hours);
        }
        parts.push(_leadingZero(minutes, 2));
        parts.push(_leadingZero(seconds, 2));

        return parts.join(':');
    };

    /**
     * Defines a player object dedicated to audio media
     * @param {Object} config
     * @private
     */
    var _audioPlayer = function(config) {

    };

    /**
     * Defines a player object dedicated to video media
     * @param {Object} config
     * @private
     */
    var _videoPlayer = function(config) {

    };

    /**
     * Defines a player object dedicated to youtube media
     * @param {Object} config
     * @private
     */
    var _youtubePlayer = function(config) {

    };

    /**
     * Defines the list of available players
     * @type {Object}
     * @private
     */
    var _players = {
        'audio' : _audioPlayer,
        'video' : _videoPlayer,
        'video/youtube' : _youtubePlayer
    };

    /**
     * Defines a media player object
     * @type {Object}
     */
    var mediaplayer = {
        /**
         * Initializes the media player
         * @param {Object} config
         * @param {String} config.type - The type of media to play
         * @param {String} config.url - The URL to the media
         * @param {Boolean} [config.autoStart] - The player start as soon as it is displayed
         * @param {Boolean} [config.loop] - The media will be played continuously
         * @param {Boolean} [config.canPause] - The play can be paused
         * @param {Boolean} [config.canFullscreen] - The media can be displayed in fullscreen (video only)
         * @param {Number} [config.maxPlays] - Sets a few number of plays (default: infinite)
         * @param {Number} [config.width] - Sets the width of the player (default: depends on media type)
         * @param {Number} [config.height] - Sets the height of the player (default: depends on media type)
         * @param {Number} [config.volume] - Sets the sound volume (default: 1)
         * @param {String|jQuery|HTMLElement} [config.renderTo] - An optional container in which renders the player
         */
        init : function init(config) {
            var initConfig = config || {};

            this.config = _.omit(initConfig, function(value) {
                return value === undefined || value === null;
            });

            this.config.is = {};

            this._reset();
            this._initType(initConfig);
            this._initSize(initConfig);
            this._initSources(initConfig);
            this._initOptions(initConfig);

            this.volume = this.config.volume;
            this.duration = 0;

            if (initConfig.renderTo) {
                this.render(initConfig.renderTo);
            }
        },

        /**
         * Uninstall the media player
         */
        destroy : function destroy() {
            this.pause();

            this._destroySlider(this.$seek);
            this._destroySlider(this.$volume);

            if (this.$media) {
                this.$media.off(_ns);
            }
            if (this.$controls) {
                this.$controls.off(_ns);
            }
            if (this.$component) {
                this.$component.off(_ns);

                if (this.config.renderTo) {
                    this.$component.remove();
                }
            }

            this._reset();
        },

        /**
         * Renders the media player according to the media type
         * @param {String|jQuery|HTMLElement} [to]
         * @returns {jQuery}
         */
        render : function render(to) {
            var self = this;
            var page = new UrlParser(window.location);
            var player;

            this._setState('cors', false);
            this._setState('ready', false);

            if (!this.is('youtube')) {
                _.forEach(this.config.sources, function(source) {
                    var url = new UrlParser(source.src);
                    if (!url.checkCORS(page)) {
                        self.config.is.cors = true;
                    }
                });
            }

            this.$component = $(playerTpl(this.config));
            this.$media = this.$component.find('.media');
            this.$controls = this.$component.find('.controls');
            this.media = this.$media.get(0);

            this.$seek = this.$controls.find('.seek .slider');
            this.$volume = this.$controls.find('.volume .slider');
            this.$position = this.$controls.find('[data-control="time-cur"]');
            this.$duration = this.$controls.find('[data-control="time-end"]');

            this._renderSlider(this.$seek, 0, this.duration);
            this._renderSlider(this.$volume, this.volume, 100, true);
            this._bindEvents();
            this._setState('paused', true);

            player = _players[this.type];
            if (_.isFunction(player)) {
                this.player = player(this);
            }

            if (!this.player) {
                this._setState('error', true);
                this.$media = this.$component.find('.error');
            }

            this.resize(this.config.width, this.config.height);

            if (to) {
                $(to).append(this.$component);
            }

            return this.$component;
        },

        /**
         * Sets the start position inside the media
         * @param {Number} time - The start position in seconds
         * @param {*} [internal] - Internal use
         * @returns {mediaplayer}
         */
        seek : function seek(time, internal) {
            this.execute('seek', time);

            this._updatePosition(time, internal);

            return this;
        },

        /**
         * Plays the media
         * @param {Number} [time] - An optional start position in seconds
         * @returns {mediaplayer}
         */
        play : function play(time) {
            if (undefined !== time) {
                this.seek(time);
            }

            this.execute('play');
            this._setState('playing', true);
            this._setState('paused', false);

            return this;
        },

        /**
         * Pauses the media
         * @param {Number} [time] - An optional time position in seconds
         * @returns {mediaplayer}
         */
        pause : function pause(time) {
            this.execute('pause');
            this._setState('playing', false);
            this._setState('paused', true);

            if (undefined !== time) {
                this.seek(time);
            }

            return this;
        },

        /**
         * Resumes the media
         * @returns {mediaplayer}
         */
        resume : function resume() {
            if (this._canResume()) {
                this.play();
            }

            return this;
        },

        /**
         * Stops the play
         * @returns {mediaplayer}
         */
        stop : function stop() {
            this.pause(0);

            return this;
        },

        /**
         * Restart the media from the begining
         * @returns {mediaplayer}
         */
        loop : function loop() {
            this.play(0);

            return this;
        },

        /**
         * Mutes the media
         * @param {Boolean} [state] - A flag to set the mute state (default: true)
         * @returns {mediaplayer}
         */
        mute : function mute(state) {
            if (undefined === state) {
                state = true;
            }
            this.execute('setVolume', state ? 0 : this.volume);
            this._setState('muted', state);

            return this;
        },

        /**
         * Restore the sound of the media after a mute
         * @param {Boolean} [state] - A flag to set the mute state (default: false)
         * @returns {mediaplayer}
         */
        unmute : function unmute(state) {
            if (undefined === state) {
                state = false;
            }
            this.mute(state);

            return this;
        },

        /**
         * Sets the sound volume of the media being played
         * @param {Number} value - A value between 0 and 100
         * @param {*} [internal] - Internal use
         * @returns {mediaplayer}
         */
        setVolume : function setVolume(value, internal) {
            this.volume = Math.max(0, Math.min(100, value));

            this.execute('setVolume', this.volume);

            this._updateVolume(this.volume, internal);

            return this;
        },

        /**
         * Gets the sound volume applied to the media being played
         * @returns {Number} Returns a value between 0 and 100
         */
        getVolume : function getVolume() {
            return this.volume;
        },

        /**
         * Changes the size of the player
         * @param {Number} width
         * @param {Number} height
         * @returns {mediaplayer}
         */
        resize : function resize(width, height) {
            this.$media.width(width).height(height);

            return this;
        },

        /**
         * Add a media source
         * @param {String|Object} src - The media URL, or an object containing the source and the type
         * @param {String} [type] - The media MIME type
         */
        addSource : function addSource(src, type) {
            var source;

            if (_.isString(src)) {
                source = {
                    src : src
                };
            } else {
                source = src;
            }

            if (!source.type) {
                source.type = type || _defaults.type;
            }

            if (this.is('youtube')) {
                source.id = _extractYoutubeId(source.src);
            }

            this.config.sources.push(source);
        },

        /**
         * Tells if the media is in a particular state
         * @param {String} state
         * @returns {Boolean}
         */
        is : function is(state) {
            return !!this.config.is[state];
        },

        /**
         * Enables the media player
         * @returns {mediaplayer}
         */
        enable : function enable() {
            this._fromState('disabled');

            return this;
        },

        /**
         * Disables the media player
         * @returns {mediaplayer}
         */
        disable : function disable() {
            this._toState('disabled');

            return this;
        },

        /**
         * Shows the media player
         * @returns {mediaplayer}
         */
        show : function show() {
            this._fromState('hidden');

            return this;
        },

        /**
         * hides the media player
         * @returns {mediaplayer}
         */
        hide : function hide() {
            this._toState('hidden');

            return this;
        },

        /**
         * Install an event handler on the underlying DOM element
         * @param {String} eventName
         * @returns {mediaplayer}
         */
        on: function on(eventName) {
            var dom = this.$component;
            if (dom) {
                dom.on.apply(dom, arguments);
            }

            return this;
        },

        /**
         * Uninstall an event handler from the underlying DOM element
         * @param {String} eventName
         * @returns {mediaplayer}
         */
        off: function off(eventName) {
            var dom = this.$component;
            if (dom) {
                dom.off.apply(dom, arguments);
            }

            return this;
        },

        /**
         * Triggers an event on the underlying DOM element
         * @param {String} eventName
         * @param {Array|Object} extraParameters
         * @returns {mediaplayer}
         */
        trigger : function trigger(eventName, extraParameters) {
            var dom = this.$component;

            if (dom) {
                if (undefined === extraParameters) {
                    extraParameters = [];
                }
                if (!_.isArray(extraParameters)) {
                    extraParameters = [extraParameters];
                }

                extraParameters.push(this);

                dom.trigger(eventName, extraParameters);
            }

            return this;
        },

        /**
         * Gets the underlying DOM element
         * @returns {jQuery}
         */
        getDom : function getDom() {
            return this.$component;
        },

        /**
         * Resets the internals attributes
         * @private
         */
        _reset : function _reset() {
            this.$component = null;
            this.$media = null;
            this.$controls = null;
            this.$seek = null;
            this.$volume = null;
            this.$position = null;
            this.$duration = null;

            this.media = null;
            this.player = null;

        },

        /**
         * Ensures the right media type is set
         * @private
         */
        _initType : function _initType() {
            var type = '' + (this.config.type || _defaults.type);
            var isYoutube = false;
            var isVideo = false;
            var isAudio = false;

            if (type.indexOf('youtube') !== -1) {
                type = 'video/youtube';
                isYoutube = true;
                isVideo = true;
            } else if (type.indexOf('video') === 0 || type.indexOf('application/ogg') !== -1) {
                type = 'video';
                isVideo = true;
            } else if (type.indexOf('audio') === 0) {
                type = 'audio';
                isAudio = true;
            }

            this.config.type = type;
            _.merge(this.config.is, {
                audio : isAudio,
                video : isVideo,
                youtube : isYoutube
            });
        },

        /**
         * Ensures the right size is set according to the media type
         * @private
         */
        _initSize : function _initSize() {
            var type = this.is('video') ? 'video' : 'audio';
            var defaults = _defaults[type] || _defaults.video;

            this.config.width = _.parseInt(this.config.width) || defaults.width;
            this.config.height = _.parseInt(this.config.height) || defaults.height;
        },

        /**
         * Ensures the sources are correctly set
         * @param {Object} config - The initial config set
         * @private
         */
        _initSources : function _initSources(config) {
            var self = this;
            var sources = config.sources;

            if (sources && !_.isArray(sources)) {
                sources = [sources];
            }

            this.config.sources = [];

            if (sources) {
                _.forEach(sources, function(source) {
                    self.addSource(source);
                });
            }

            if (config.url) {
                this.addSource(config.url, config.type);
            }
        },



        /**
         * Ensures some options are sets
         * @private
         */
        _initOptions : function _initOptions() {
            _.defaults(this.config, _defaults.options);
        },

        /**
         * Renders a slider onto an element
         * @param {jQuery} $elt - The element on which renders the slider
         * @param {Number} [start] - The current value of the slider
         * @param {Number} [max] - The max value of the slider
         * @param {Boolean} [vertical] - Tells if the slider must be vertical
         * @returns {jQuery} - Returns the element
         * @private
         */
        _renderSlider : function _renderSlider($elt, start, max, vertical) {
            var orientation, direction;

            if (vertical) {
                orientation = 'vertical';
                direction = 'rtl';
            } else {
                orientation = 'horizontal';
                direction = 'ltr';
            }

            return $elt.noUiSlider({
                start: start || 0,
                step: 1,
                connect: 'lower',
                orientation: orientation,
                direction: direction,
                animate: true,
                range: {
                    min: 0,
                    max : max || 100
                }
            })
        },

        /**
         * Destroys a slider bound to an element
         * @param {jQuery} $elt
         * @private
         */
        _destroySlider : function _destroySlider($elt) {
            if ($elt) {
                $elt.noUiSlider('destroy');
                $elt.off(_ns);
            }
        },

        /**
         * Binds events onto the rendered player
         * @private
         */
        _bindEvents : function _bindEvents() {
            var self = this;

            this.$controls.on('click' + _ns, '.action', function(event) {
                var $target = $(event.target);
                var $action = $target.closest('.action');
                var id = $action.data('control');

                if (_.isFunction(self[id])) {
                    self[id]();
                }
            });

            this.$seek.on('change' + _ns, function(event, value) {
                self.seek(value, true);
            });

            this.$volume.on('change' + _ns, function(event, value) {
                self.setVolume(value, true);
            });
        },

        /**
         * Updates the volume slider
         * @param {Number} value
         * @private
         */
        _updateVolumeSlider : function _updateVolumeSlider(value) {
            if (this.$volume) {
                this.$volume.val(value);
            }
        },

        /**
         * Updates the displayed volume
         * @param {Number} value
         * @param {*} [internal]
         * @private
         */
        _updateVolume : function _updateVolume(value, internal) {
            if (!internal) {
                this._updateVolumeSlider(value);
            }
        },

        /**
         * Updates the time slider
         * @param {Number} value
         * @private
         */
        _updatePositionSlider : function _updatePositionSlider(value) {
            if (this.$position) {
                this.$position.text(_timerFormat(value));
            }
        },

        /**
         * Updates the time label
         * @param {Number} value
         * @private
         */
        _updatePositionLabel : function _updatePositionLabel(value) {
            if (this.$position) {
                this.$position.text(_timerFormat(value));
            }
        },

        /**
         * Updates the displayed time position
         * @param {Number} value
         * @param {*} [internal]
         * @private
         */
        _updatePosition : function _updatePosition(value, internal) {
            if (!internal) {
                this._updatePositionSlider(value);
            }
            this._updatePositionLabel(value);
        },

        /**
         * Event called when the media is ready
         * @param event
         * @private
         */
        _onReady : function _onReady(event) {
            this._renderSlider(this.$seek, 0, this.duration);
            this.$duration.text(_timerFormat(this.duration));

            this.setVolume(this.volume);

            /**
             * Triggers a media ready event
             * @event mediaplayer#ready
             */
            this.trigger('ready' + _ns);

            if (this.config.autoStart) {
                this.play();
            }
        },

        /**
         * Checks if the playback can be resumed
         * @returns {Boolean}
         * @private
         */
        _canResume : function _canResume() {
            return  this.is('paused') && !this.is('disabled') && !this.is('hidden');
        },

        /**
         * Sets the media is in a particular state
         * @param {String} name
         * @param {Boolean} value
         * @returns {mediaplayer}
         */
        _setState : function _setState(name, value) {
            value = !!value;

            this.config.is[name] = value;

            if (this.$component) {
                this.$component.toggleClass(name, value);
            }

            return this;
        },

        /**
         * Restores the media player from a particular state and resumes the playback
         * @param {String} stateName
         * @returns {mediaplayer}
         * @private
         */
        _fromState : function _fromState(stateName) {
            this._setState(stateName, false);
            this.resume();

            return this;
        },

        /**
         * Sets the media player to a particular state and pauses the playback
         * @param {String} stateName
         * @returns {mediaplayer}
         * @private
         */
        _toState : function _toState(stateName) {
            this.pause();
            this._setState(stateName, true);

            return this;
        },

        /**
         * Executes a command onto the media
         * @param {String} command - The name of the command to execute
         * @returns {*}
         * @private
         */
        execute : function execute(command) {
            var ctx = this.player;
            var method = ctx && ctx[command];

            if (_.isFunction(method)) {
                return method.apply(ctx, _slice.call(arguments, 1));
            }
        }
    };

    /**
     * Builds a media player instance
     * @param {Object} config
     * @param {String} config.type - The type of media to play
     * @param {String} config.url - The URL to the media
     * @param {Boolean} [config.autoStart] - The player start as soon as it is displayed
     * @param {Boolean} [config.loop] - The media will be played continuously
     * @param {Boolean} [config.canPause] - The play can be paused
     * @param {Boolean} [config.canFullscreen] - The media can be displayed in fullscreen (video only)
     * @param {Number} [config.maxPlays] - Sets a few number of plays (default: infinite)
     * @param {Number} [config.width] - Sets the width of the player (default: depends on media type)
     * @param {Number} [config.height] - Sets the height of the player (default: depends on media type)
     * @returns {mediaplayer}
     */
    var mediaplayerFactory = function mediaplayerFactory(config) {
        var player = _.clone(mediaplayer);
        player.init(config);
        return player;
    };

    return mediaplayerFactory;
});
