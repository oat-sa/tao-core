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
 * Copyright (c) 2015-2018 (original work) Open Assessment Technologies SA;
 *
 */
define(['jquery', 'lodash', 'core/dataattrhandler',   'lib/popper/popper', 'lib/popper/tooltip'], function($, _, DataAttrHandler, Popper, Tooltip ){
    'use strict';

    var themes = ['dark', 'default', 'info', 'warning', 'error', 'success', 'danger'],
        themesMap = {
            'default' : '<div class="tooltip qtip-rounded qtip-plain" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner qtip-content"></div></div>',
            'dark' : '<div class="tooltip qtip-rounded qtip-dark" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner qtip-content"></div></div>',
            'error' : '<div class="tooltip qtip-rounded qtip-red" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner qtip-content"></div></div>',
            'success' :'<div class="tooltip qtip-rounded qtip-green" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner qtip-content"></div></div>',
            'info' : '<div class="tooltip qtip-rounded qtip-blue" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner qtip-content"></div></div>',
            'warning' : '<div class="tooltip qtip-rounded qtip-orange" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner qtip-content"></div></div>',
            'danger' : '<div class="tooltip qtip-rounded qtip-danger" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner qtip-content"></div></div>'
        },
        // mapping from old qtip API to new Popper.js+Tooltip.js API calls
        commandsMap = {
            'hide':'hide',
            'blur':'hide',
            'show':'show',
            'toggle':'toggle',
            'update':'updateTitleContent',
            'destroy':'dispose',
            'set':'updateTitleContent',
            'content.text':'updateTitleContent'
        },
        positionMap = {
            'right':'end',
            'left':'begin',
            'top':'begin',
            'bottom':'end'
        },
        defaultOptions = {
            template:themesMap['default'],
            html:true,
            popperOptions:{
                positionFixed: true,
                placement:'auto',
                modifiers:{
                    preventOverflow:{
                        escapeWithReference:false,
                        enabled:true,
                        padding:6,
                        boundariesElement:'viewport'
                    }
                }
            }
        };

    /**
     * redefinition of jquery.qtip plugin http://qtip2.com/
     * the goal is to substitude outdated lib code with new solution (https://popper.js.org/)
     * so it is just a jquery rapper for Popper.js library.
     * The Popper Instance will be stored inside .data('$tooltip') of wrapped element
     * leaving its original interfaces that are widely used through project.
     * https://github.com/FezVrasta/popper.js/blob/master/docs/_includes/tooltip-documentation.md
     */
    var qtip = function (command, message, messageData) {
        // there were two types of requests for qtip jquery plugin (with object or string):
        // 1) initialize object for the first time : $el.qtip({ options object});
        if (typeof command === 'object') {
            // .data('$tooltip') - is the way to store current state inside jquery plugin
            command = _.merge({}, defaultOptions, command);
            if(this.data('$tooltip')) {
                this.data('$tooltip').dispose();
                this.removeData('$tooltip');
                this.removeAttr("data-hasqtip");

            }
            // fit old  options  format for themes to new
            if (themesMap[command.theme]){
                command.template = themesMap[command.theme];
                delete command.theme;
            }
            // fit old content option to new
            if(command.content){
                command.title = command.content.text ;
                delete command.content;
            }
            // map posititon settings from old to new format
            if(command.position && typeof command.position.at === 'string'){
                // eslint-disable-next-line vars-on-top
                var pos = command.position.at.split(' '),
                    position;

                if(pos.length) {
                    position = pos[0] ;
                    position += pos[1] && pos[1] !== 'center' ? '-'+ positionMap[pos[1]] : '';
                }

                command.placement = position;
            }
            // map container settings from old to new format
            if(command.position && command.position.container){
                command.container = command.position.container;
                delete command.position.container;
            }
            if (this.length){
                this.data('$tooltip', new Tooltip(this, command));
                // compatibility polyfill
                this.attr('data-hasqtip', 1);
                if(command.show){
                    if(command.show === true || command.show.ready === true){
                        this.data('$tooltip').show();
                    }
                }
            }

        // 2) sending text (String) commands to  element that is already initialized : $el.qtip("show")
        }else if(this.data('$tooltip') && typeof command === 'string'){
            switch (command) {
                case 'theme':
                    this.data('$tooltip').template = themesMap[message];
                    break;
                case 'content.text':
                case 'update':
                    this.data('$tooltip')[commandsMap[command]](message);
                    break;
                case 'set':
                // covers this particular behavior:
                // $scoreInput.qtip('set', 'content.text', options.tooltipContent.required);
                    if(message === 'content.text'){
                        this.data('$tooltip')[commandsMap[command]](messageData);
                    }
                    break;
                default:
                    if(command === 'api'){
                        return this.data('$tooltip');
                    }
                    this.data('$tooltip')[commandsMap[command]]();
                    if(commandsMap[command] === 'dispose'){
                        this.removeData('$tooltip');
                        this.removeAttr("data-hasqtip");
                    }
            }
        }

        return this;
    };
    $.fn.qtip = qtip;
    $.qtip = qtip;

    /**
     * Look up for tooltips and initialize them
     *
     * @public
     * @param {jQueryElement} $container - the root context to lookup inside
     */
    return function lookupSelector(element, options){
        var themeName;
        var template;
        var instance;
        var setTooltip = function (el, inst) {
            if($(el).data('$tooltip')){
                $(el).data('$tooltip').dispose();
                $(el).removeData('$tooltip');
            }
            $(el).data('$tooltip', inst);
        };
        if(element && (element instanceof Element || element instanceof HTMLDocument || element.jquery)){
            if(!options){
                $('[data-tooltip]', element).each(function(){
                    var $content = DataAttrHandler.getTarget('tooltip', $(this));
                    var opt;
                    themeName = _.contains(themes, element.data('tooltip-theme')) ? element.data('tooltip-theme') : 'default';
                    opt = {
                        template:themesMap[themeName]
                    };
                    if($content.length){
                        _.merge(opt, { title: $content[0] }, defaultOptions);
                    }
                    setTooltip(this, new Tooltip(this, opt));
                });
            }else{
                themeName = _.contains(themes, options.theme) ? options.theme : 'default';
                template = {
                    template:themesMap[themeName]
                };
                instance = new Tooltip(element, _.merge(defaultOptions, template, options));
                setTooltip(element, instance);
                return instance;
            }
        } else {
            throw new Error("Tooltip should be connected to DOM Element");
        }

    };
});
