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
     * Minimum value of the volume
     * @type {Number}
     * @private
     */
    var _volumeMin = 0;

    /**
     * Maximum value of the volume
     * @type {Number}
     * @private
     */
    var _volumeMax = 100;

    /**
     * Range value of the volume
     * @type {Number}
     * @private
     */
    var _volumeRange = _volumeMax - _volumeMin;

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
            volume : Math.floor(_volumeRange * .8),
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
     * Builds a namespaced list of events
     * @param {Array} events
     * @returns {String}
     * @private
     */
    var _nsEvents = function(events) {
        return _.reduce(events, function(result, event) {
            return result + ' ' + event + _ns;
        }, '').trim();
    };

    /**
     * A local manager for Youtube players.
     * Relies on https://developers.google.com/youtube/iframe_api_reference
     * @type {Object}
     * @private
     */
    var _youtubeManager = {
        /**
         * The Youtube API injection state
         * @type {Boolean}
         */
        injected : false,

        /**
         * The Youtube API ready state
         * @type {Boolean}
         */
        ready : false,

        /**
         * A list of pending players
         * @type {Array}
         */
        pending : [],

        /**
         * Add a Youtube player
         * @param {String|jQuery|HTMLElement} elem
         * @param {Object} player
         */
        add : function add(elem, player) {
            if (this.ready) {
                this.create(elem, player);
            } else {
                this.pending.push([elem, player]);

                if (!this.injected) {
                    this.injectApi();
                }
            }
        },

        /**
         * Install a Youtube player. The Youtube API must be ready
         * @param {String|jQuery|HTMLElement} elem
         * @param {Object} player
         */
        create : function create(elem, player) {
            var $elem;

            if (!this.ready) {
                return this.add(elem, player);
            }

            $elem = $(elem);

            new YT.Player($elem.get(0), {
                height: $elem.width(),
                width: $elem.height(),
                videoId: $elem.data('videoId'),
                playerVars: {
                    //hd: true,
                    autoplay: 0,
                    controls: 0,
                    rel: 0,
                    showinfo: 0,
                    wmode: 'transparent',
                    modestbranding: 1,
                    disablekb: 1
                },
                events: {
                    onReady: player.onReady.bind(player),
                    onStateChange: player.onStateChange.bind(player)
                }
            });
        },

        /**
         * Called when the Youtube API is ready. Should install all pending players.
         */
        apiReady : function apiReady() {
            var self = this;
            var pending = this.pending;

            this.pending = [];
            this.ready = true;

            _.forEach(pending, function(args) {
                self.create.apply(self, args);
            });
        },

        /**
         * Checks if the Youtube API is ready to use
         * @returns {Boolean}
         */
        isApiReady : function isApiReady() {
            var apiReady = (undefined !== window.YT && undefined !== window.YT.Player);
            if (apiReady && !this.ready) {
                _youtubeManager.apiReady();
            }
            return apiReady;
        },

        /**
         * Injects the Youtube API into the page
         */
        injectApi : function injectApi() {
            var self = this;
            if (!self.isApiReady()) {
                require(['//www.youtube.com/iframe_api'], function() {
                    var check = function() {
                        if (!self.isApiReady()) {
                            setTimeout(check, 100);
                        }
                    };
                    check();
                });
            }

            this.injected = true;
        }
    };

    /**
     * Defines a player object dedicated to youtube media
     * @param {mediaplayer} mediaplayer
     * @private
     */
    var _youtubePlayer = function(mediaplayer) {
        var $media;
        var media;
        var player;
        var interval;

        if (mediaplayer) {
            player = {
                init : function _youtubePlayerInit() {
                    $media = mediaplayer.$media;

                    if ($media) {
                        _youtubeManager.add($media, this);
                    }
                },

                onReady : function _youtubePlayerOnReady(event) {
                    media = event.target;
                    $media = $(media.getIframe());
                    mediaplayer._onReady();
                },

                onStateChange : function _youtubePlayerOnStateChange(event) {
                    this.stopPolling();

                    switch (event.data) {
                        // ended
                        case 0:
                            mediaplayer._onEnd();
                            break;

                        // playing
                        case 1:
                            mediaplayer._onPlay();
                            this.startPolling();
                            break;

                        // paused
                        case 2:
                            mediaplayer._onPause();
                            break;
                    }
                },

                stopPolling : function _youtubePlayerStopPolling() {
                    if (interval) {
                        clearInterval(interval);
                        interval = null;
                    }
                },

                startPolling : function _youtubePlayerStartPolling() {
                    interval = setInterval(function() {
                        mediaplayer._onTimeUpdate();
                    }, 100);
                },

                destroy : function _youtubePlayerDestroy() {
                    if (media) {
                        media.destroy();
                    }

                    this.stopPolling();

                    $media = null;
                    media = null;
                },

                getPosition : function _youtubePlayerGetPosition() {
                    if (media) {
                        return media.getCurrentTime();
                    }
                    return 0;
                },

                getDuration : function _youtubePlayerGetDuration() {
                    if (media) {
                        return media.getDuration();
                    }
                    return 0;
                },

                setVolume : function _youtubePlayerSetVolume(value) {
                    if (media) {
                        media.setVolume((parseFloat(value) - _volumeMin) / _volumeRange * 100);
                    }
                },

                setSize : function _youtubePlayerSetSize(width, height) {
                    if ($media) {
                        $media.width(width).height(height);
                    }
                    if (media) {
                        media.setSize(width, height);
                    }
                },

                seek : function _youtubePlayerSeek(value) {
                    if (media) {
                        media.seekTo(parseFloat(value), true);
                    }
                },

                play : function _youtubePlayerPlay() {
                    if (media) {
                        media.playVideo();
                    }
                },

                pause : function _youtubePlayerPause() {
                    if (media) {
                        media.pauseVideo();
                    }
                },

                mute : function _youtubePlayerMute(state) {
                    if (media) {
                        media[state ? 'mute' : 'unMute']();
                    }
                }
            };
        }

        return player;
    };

    /**
     * Defines a player object dedicated to native player
     * @param {mediaplayer} mediaplayer
     * @private
     */
    var _nativePlayer = function(mediaplayer) {
        var $player;
        var $media;
        var media;
        var player;
        var playing;
        var played;

        if (mediaplayer) {
            player = {
                init : function _nativePlayerInit() {
                    var self = this;

                    $media = mediaplayer.$media;
                    $player = mediaplayer.$player;
                    media = null;
                    playing = false;
                    played = false;

                    if ($media) {
                        media = $media.get(0);

                        $media
                            .removeAttr('controls')
                            .on(_nsEvents(['play']), function() {
                                playing = true;
                                played = true;
                                mediaplayer._onPlay();
                            })
                            .on(_nsEvents(['pause']), function() {
                                playing = false;
                                mediaplayer._onPause();
                            })
                            .on(_nsEvents(['ended']), function() {
                                playing = false;
                                mediaplayer._onEnd();
                            })
                            .on(_nsEvents(['timeupdate']), function() {
                                mediaplayer._onTimeUpdate();
                            })
                            .on(_nsEvents(['loadedmetadata']), function() {
                                mediaplayer._onReady();
                            });
                    }

                    if ($player) {
                        $player.on(_nsEvents(['click']), function() {
                            if (playing) {
                                self.pause();
                            } else {
                                self.play();
                            }
                        })
                    }
                },

                destroy : function _nativePlayerDestroy() {
                    if ($media) {
                        $media.off(_ns).attr('controls', '');
                    }
                    if ($player) {
                        $player.off(_ns);
                    }

                    $media = null;
                    $player = null;
                    media = null;
                    playing = false;
                    played = false;
                },

                getPosition : function _nativePlayerGetPosition() {
                    if (media) {
                        return media.currentTime;
                    }
                    return 0;
                },

                getDuration : function _nativePlayerGetDuration() {
                    if (media) {
                        return media.duration;
                    }
                    return 0;
                },

                setVolume : function _nativePlayerSetVolume(value) {
                    if (media) {
                        media.volume = (parseFloat(value) - _volumeMin) / _volumeRange;
                    }
                },

                setSize : function _nativePlayerSetSize(width, height) {
                    if ($media) {
                        $media.width(width).height(height);
                    }
                },

                seek : function _nativePlayerSeek(value) {
                    if (media) {
                        media.currentTime = parseFloat(value);
                        if (!played) {
                            this.play();
                        }
                    }
                },

                play : function _nativePlayerPlay() {
                    if (media) {
                        playing = true;
                        media.play();
                    }
                },

                pause : function _nativePlayerPause() {
                    if (media) {
                        playing = false;
                        media.pause();
                    }
                },

                mute : function _nativePlayerMute(state) {
                    if (media) {
                        media.muted = !!state;
                    }
                }
            };
        }

        return player;
    };

    /**
     * Defines the list of available players
     * @type {Object}
     * @private
     */
    var _players = {
        'audio' : _nativePlayer,
        'video' : _nativePlayer,
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
         * @param {String|Array} config.url - The URL to the media
         * @param {Boolean} [config.autoStart] - The player starts as soon as it is displayed
         * @param {Number} [config.autoStartAt] - The time position at which the player should start
         * @param {Boolean} [config.loop] - The media will be played continuously
         * @param {Boolean} [config.canPause] - The play can be paused
         * @param {Boolean} [config.canFullscreen] - The media can be displayed in fullscreen (video only)
         * @param {Number} [config.maxPlays] - Sets a few number of plays (default: infinite)
         * @param {Number} [config.width] - Sets the width of the player (default: depends on media type)
         * @param {Number} [config.height] - Sets the height of the player (default: depends on media type)
         * @param {Number} [config.volume] - Sets the sound volume (default: 1)
         * @param {Boolean} [config.startMuted] - The player should be initially muted
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
            this.autoStart = this.config.autoStart;
            this.autoStartAt = this.config.autoStartAt;
            this.startMuted = this.config.startMuted;
            this.duration = 0;
            this.position = 0;

            if (initConfig.renderTo) {
                this.render(initConfig.renderTo);
            }
        },

        /**
         * Uninstall the media player
         */
        destroy : function destroy() {
            this.pause();

            if (this.player) {
                this.player.destroy();
            }

            this._setState('ready', false);

            if (this.$component) {
                this._unbindEvents();
                this._destroySlider(this.$seekSlider);
                this._destroySlider(this.$volumeSlider);

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
            var page;

            if (this.$component) {
                this.destroy();
            }

            this._setState('cors', false);
            this._setState('ready', false);

            if (!this.is('youtube')) {
                page = new UrlParser(window.location);
                _.forEach(this.config.sources, function(source) {
                    var url = new UrlParser(source.src);
                    if (!url.checkCORS(page)) {
                        self._setState('cors', true);
                        return false;
                    }
                });
            }

            this._buildDom();
            this._updateDuration(0);
            this._updatePosition(0);
            this._bindEvents();
            this._playingState(false);
            this._initPlayer();

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
            this._updatePosition(time, internal);

            this.execute('seek', this.position);

            if (!this.is('ready')) {
                this.autoStartAt = this.position;
            }

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

            if (!this.is('ready')) {
                this.autoStart = true;
            }

            return this;
        },

        /**
         * Pauses the media
         * @param {Number} [time] - An optional time position in seconds
         * @returns {mediaplayer}
         */
        pause : function pause(time) {
            if (undefined !== time) {
                this.seek(time);
            }

            this.execute('pause');

            if (!this.is('ready')) {
                this.autoStart = false;
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
            this.execute('mute', state);
            this._setState('muted', state);

            if (!this.is('ready')) {
                this.startMuted = state;
            }

            return this;
        },

        /**
         * Restore the sound of the media after a mute
         * @returns {mediaplayer}
         */
        unmute : function unmute() {
            this.mute(false);

            return this;
        },

        /**
         * Sets the sound volume of the media being played
         * @param {Number} value - A value between 0 and 100
         * @param {*} [internal] - Internal use
         * @returns {mediaplayer}
         */
        setVolume : function setVolume(value, internal) {
            this._updateVolume(value, internal);

            this.execute('setVolume', this.volume);

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
            if (this.$component) {
                this.$component.width(width);
                height -= this.$controls.outerHeight();
                width -= this.$component.outerWidth() - this.$component.width();
            }

            this.execute('setSize', width, height);

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
         * Builds the DOM content
         * @private
         */
        _buildDom : function _buildDom() {
            this.$component = $(playerTpl(this.config));
            this.$player = this.$component.find('.player');
            this.$media = this.$component.find('.media');
            this.$controls = this.$component.find('.controls');

            this.$seek = this.$controls.find('.seek .slider');
            this.$volume = this.$controls.find('.volume .slider');
            this.$position = this.$controls.find('[data-control="time-cur"]');
            this.$duration = this.$controls.find('[data-control="time-end"]');

            this.$volumeSlider = this._renderSlider(this.$volume, this.volume, _volumeMin, _volumeMax, true);
        },

        /**
         * Resets the internals attributes
         * @private
         */
        _reset : function _reset() {
            this.$component = null;
            this.$player = null;
            this.$media = null;
            this.$controls = null;
            this.$seek = null;
            this.$seekSlider = null;
            this.$volume = null;
            this.$volumeSlider = null;
            this.$position = null;
            this.$duration = null;
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
            var sources = config.sources || [];

            if (!_.isArray(sources)) {
                sources = [sources];
            }

            this.config.sources = [];

            if (config.url) {
                if (_.isArray(config.url)) {
                    sources = sources.concat(config.url);
                } else {
                    sources.push(config.url);
                }
            }

            _.forEach(sources, function(source) {
                self.addSource(source, config.type);
            });
        },

        /**
         * Ensures some options are sets
         * @private
         */
        _initOptions : function _initOptions() {
            _.defaults(this.config, _defaults.options);
        },

        /**
         * Initializes the right player instance
         * @private
         */
        _initPlayer : function _initPlayer() {
            var player = _players[this.config.type];

            if (_.isFunction(player)) {
                this.player = player(this);
            }

            if (this.player) {
                this.player.init();
            } else {
                this._setState('error', true);
            }
        },

        /**
         * Renders a slider onto an element
         * @param {jQuery} $elt - The element on which renders the slider
         * @param {Number} [value] - The current value of the slider
         * @param {Number} [min] - The min value of the slider
         * @param {Number} [max] - The max value of the slider
         * @param {Boolean} [vertical] - Tells if the slider must be vertical
         * @returns {jQuery} - Returns the element
         * @private
         */
        _renderSlider : function _renderSlider($elt, value, min, max, vertical) {
            var orientation, direction;

            if (vertical) {
                orientation = 'vertical';
                direction = 'rtl';
            } else {
                orientation = 'horizontal';
                direction = 'ltr';
            }

            return $elt.noUiSlider({
                start: value || 0,
                step: 1,
                connect: 'lower',
                orientation: orientation,
                direction: direction,
                animate: true,
                range: {
                    min: min || 0,
                    max : max || 0
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
                $elt.get(0).destroy();
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
                self.unmute();
                self.setVolume(value, true);
            });
        },

        /**
         * Unbinds events from the rendered player
         * @private
         */
        _unbindEvents : function _unbindEvents() {
            this.$controls.off(_ns);
            this.$seek.off(_ns);
            this.$volume.off(_ns);
        },

        /**
         * Updates the volume slider
         * @param {Number} value
         * @private
         */
        _updateVolumeSlider : function _updateVolumeSlider(value) {
            if (this.$volumeSlider) {
                this.$volumeSlider.val(value);
            }
        },

        /**
         * Updates the displayed volume
         * @param {Number} value
         * @param {*} [internal]
         * @private
         */
        _updateVolume : function _updateVolume(value, internal) {
            this.volume = Math.max(_volumeMin, Math.min(_volumeMax, parseFloat(value)));

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
            if (this.$seekSlider) {
                this.$seekSlider.val(value);
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
            this.position = Math.max(0, Math.min(this.duration, parseFloat(value)));

            if (!internal) {
                this._updatePositionSlider(this.position);
            }
            this._updatePositionLabel(this.position);
        },

        /**
         * Updates the duration slider
         * @param {Number} value
         * @private
         */
        _updateDurationSlider : function _updateDurationSlider(value) {
            if (this.$seekSlider) {
                this._destroySlider(this.$seekSlider);
                this.$seekSlider = null;
            }

            if (value) {
                this.$seekSlider = this._renderSlider(this.$seek, 0, 0, value);
            }
        },

        /**
         * Updates the duration label
         * @param {Number} value
         * @private
         */
        _updateDurationLabel : function _updateDurationLabel(value) {
            if (this.$duration) {
                if (value) {
                    this.$duration.text(_timerFormat(value)).show();
                } else {
                    this.$duration.hide();
                }
            }
        },

        /**
         * Updates the displayed duration
         * @param {Number} value
         * @private
         */
        _updateDuration : function _updateDuration(value) {
            this.duration = Math.abs(parseFloat(value));
            this._updateDurationSlider(this.duration);
            this._updateDurationLabel(this.duration);
        },

        /**
         * Event called when the media is ready
         * @private
         */
        _onReady : function _onReady() {
            this._updateDuration(this.player.getDuration());
            this._setState('ready', true);

            /**
             * Triggers a media ready event
             * @event mediaplayer#ready.mediaplayer
             */
            this.trigger('ready' + _ns);

            // set the initial state
            this.setVolume(this.volume);
            this.mute(!!this.startMuted);
            if (this.autoStartAt) {
                this.seek(this.autoStartAt);
            } else if (this.autoStart) {
                this.play();
            }
        },

        /**
         * Event called when the media is played
         * @private
         */
        _onPlay : function _onPlay() {
            this._playingState(true);

            /**
             * Triggers a media playback event
             * @event mediaplayer#play.mediaplayer
             */
            this.trigger('play' + _ns);
        },

        /**
         * Event called when the media is paused
         * @private
         */
        _onPause : function _onPause() {
            this._playingState(false);

            /**
             * Triggers a media paused event
             * @event mediaplayer#pause.mediaplayer
             */
            this.trigger('pause' + _ns);
        },

        /**
         * Event called when the media is ended
         * @private
         */
        _onEnd : function _onPause() {
            this._playingState(false);
            this._updatePosition(0);

            /**
             * Triggers a media ended event
             * @event mediaplayer#ended.mediaplayer
             */
            this.trigger('ended' + _ns);
        },

        /**
         * Event called when the time position has changed
         * @private
         */
        _onTimeUpdate : function _onTimeUpdate() {
            this._updatePosition(this.player.getPosition());

            /**
             * Triggers a media time update event
             * @event mediaplayer#update.mediaplayer
             */
            this.trigger('update' + _ns);
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
         * Sets the playing state
         * @param {Boolean} state
         * @returns {mediaplayer}
         * @private
         */
        _playingState : function _playingState(state) {
            this._setState('playing', !!state);
            this._setState('paused', !state);

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
     * @param {String|Array} config.url - The URL to the media
     * @param {Boolean} [config.autoStart] - The player starts as soon as it is displayed
     * @param {Number} [config.autoStartAt] - The time position at which the player should start
     * @param {Boolean} [config.loop] - The media will be played continuously
     * @param {Boolean} [config.canPause] - The play can be paused
     * @param {Boolean} [config.canFullscreen] - The media can be displayed in fullscreen (video only)
     * @param {Number} [config.maxPlays] - Sets a few number of plays (default: infinite)
     * @param {Number} [config.width] - Sets the width of the player (default: depends on media type)
     * @param {Number} [config.height] - Sets the height of the player (default: depends on media type)
     * @param {Number} [config.volume] - Sets the sound volume (default: 1)
     * @param {Boolean} [config.startMuted] - The player should be initially muted
     * @param {String|jQuery|HTMLElement} [config.renderTo] - An optional container in which renders the player
     * @returns {mediaplayer}
     */
    var mediaplayerFactory = function mediaplayerFactory(config) {
        var player = _.clone(mediaplayer);
        player.init(config);
        return player;
    };

    return mediaplayerFactory;
});
