define([
    'jquery', 
    'lodash', 
    'context',
    'history'

], function($, _, context){


    var sectionParamExp = /&section=([^&]*)/;
    var History = window.History;

    //back & forward button, and push state
    History.Adapter.bind(window, 'statechange', function stateChange(){
        //console.log('stateChange');
        restoreState(History.getState());
    });
    
    function restoreState(state){
        
        //console.log('restore state', state); 

        if(state && state.data && state.data.section){
		    return sectionApi.get(state.data.section.id)['_' + state.data.restoreWith]();
        }
    }

    function pushState(section, restoreWith){
        //console.log('push state', section);
        var stateUrl = window.location.search + '';
        if(!stateUrl){
          stateUrl = '?';
        }
        stateUrl = stateUrl.replace(sectionParamExp, '') + '&section=' + section.id ;

        History.pushState({
                section : section,
                restoreWith    : restoreWith || 'activate'
            }, 
            section.name || '', 
            stateUrl
        );
    }

    var sectionApi = {
        
        scope : $('.section-container'), 
        sections : {},
        selected : null,
    
        init : function($scope){
            var self = this;
            var $openersContainer;
            var defaultSection;

            var paramResult = window.location.toString().match(sectionParamExp);
            if(paramResult && paramResult.length){
                defaultSection = paramResult[1];
            }            
 
            this.scope = $scope || this.scope || $('.section-container');
            $openersContainer = $('.tab-container', this.scope);

            //load sections from the DOM
            $('li', $openersContainer).each(function(index){

                 var $sectionOpener = $(this);
                 var $link = $sectionOpener.children('a');
                 var id = $link.attr('href').replace('#panel-', '');
                 var $panel = $('#panel-' + id);
                 var active = false;
                                      
                 self.sections[id] = {
                    id          : id,
                    url         : $link.data('url'),
                    name        : $link.text(),
                    panel       : $('#panel-' + id),
                    opener      : $sectionOpener,
                    type        : $panel.find('.section-trees').children().length ? 'tree' : 'content',
                    active      : defaultSection ? defaultSection === id : index === 0
                 };
            });
            
            //to be sure at least one is active, for example when the given default section does not exists
            if(_(this.sections).where({'active' : true }).size() === 0){
                for(var id in this.sections){
                    this.sections[id].active =  true;
                    break;
                }
            }

            //bind click on openers 
            $openersContainer
                .off('click.section', 'li')
                .on('click.section', 'li', function(e){
                     e.preventDefault();

                     var id = $(this).children('a').attr('href').replace('#panel-', '');
                     var section = self.sections[id];
                     self.get(id).activate();
                });

            //display the openers only if there is more than 1 section
            if($('li:not(.hidden)', $openersContainer).length < 2){
                $openersContainer.hide();
            } else {
                $openersContainer.show();
            }

            this.scope.trigger('init.section');    
    
            if(!restoreState(History.getState())){
                //console.log('no state to restore, default is ', defaultSection);

                return this.activate();
            }
            return this;
        },

        activate : function(){
            if(!this.selected){
                this.current();
            }

            pushState(this.selected);

            return this;
        },

        _activate : function(){
            this._show();    
            this.scope.trigger('activate.section', [this.selected]);    

            return this;
        },

        show : function(){
            if(!this.selected){
                this.current();
            }

            pushState(this.selected, 'show');

            return this;
        },
        

        _show : function(){
    
            var self = this;
            var active = _(this.sections).where({'active' : true }).first();

            //switch the active section if set previously
            if(this.selected && this.selected.id !== active.id){
                _.forEach(this.sections, function(section){
                    section.active = false;
                });
                this.sections[this.selected.id].active = true;
             } else {
                this.current();
            }

            _.where(this.sections, {'active' : false }).forEach(function(section){
                section.opener.removeClass('active');
                section.panel.hide();
            });
            _.where(this.sections, {'active' : true }).forEach(function(section){
                section.opener.addClass('active');
                section.panel.show();
                //pushState(section);
            });
            return this;
        },

        refresh : function(){
            this.sections = {};
            return this.init();
        },

        current : function(){
            this.selected =  _(this.sections).where({'active' : true }).first();
            return this;
        },

        create : function(data){
            var $openersContainer = this.scope.find('.tab-cotnainer');
            var $sectionOpener, 
                $sectionPanel, 
                section;

            if(!_.isObject(data)){
                throw new TypeError("The create() method requires an object with section data as parameter.");
            }    
            if(!_.isString(data.id) || !_.isString(data.url) || !_.isString(data.name)){
                throw new TypeError("The create() method requires data with id, url and name to create a new section.");
            }
            
            this.get(data.id);
            section = this.selected && this.selected.id === data.id ? this.selected : undefined;
             
            if(!section){
                
                $sectionPanel = $('<div id="panel-' + data.id +'" class="clear"></div>');
                $sectionOpener = $('<li class="small ' + (!!data.visible ? 'hidden' : '') +'"><a title="'+data.name+'" data-url="'+data.url+'" href="#panel-' + data.id +'>'+data.name+'</a></li>');
                $openersContainer.append($sectionOpener);
                this.scope.append($sectionPanel);
        
                section =  {
                    id          : data.id,
                    url         : data.url,
                    name        : data.name,
                    panel       : $sectionPanel,
                    opener      : $sectionOpener,
                    type        : 'content',
                    active      : false
                };
                this.sections[data.id] = section;
                this.selected = section;


            }
                        
            if(data.content){
                section.panel.html(data.content);
            }
                
            //display the openers only if there is more than 1 section
            if($('li:not(.hidden)', $openersContainer).length < 2){
                $openersContainer.hide();
            } else {
                $openersContainer.show();
            }

            return this;
        },

        get : function(value){
            var section;
            if(!_.isString(value)){
                throw new TypeError("The get() method requires a string parameter, the section id or url.");
            }

            //try to get the section assuming the value is the id or the url.
            section = 
                this.sections[value] || 
                this.sections[value.replace('panel-', '')] ||
                _(this.sections).where({'url' : value }).first() ||
                _(this.sections).where({'url' : context.root_url + value }).first();
            if(section){
                this.selected = section;
            } else {
                this.current();
            }        
            
            return this;
        },

        load : function(url, data){
            var self = this;
            var wideDifferenciator = '[data-content-target="wide"]';
            var $contentBlock;

            if(!this.selected){
                this.current();
            }
            url = url || this.selected.url;

            if(this.selected.type === 'tree'){
                this.selected.panel.addClass('content-panel'); 
            } else {
                this.selected.panel.removeClass('content-panel'); 
            }

            this.selected.panel.empty().load(url, data); 

            return this;
        },

        loadContentBlock : function(url, data){
            var $contentblock;

            if(!this.selected){
                this.current();
            }
            url = url || this.selected.url;

            if(this.selected.type === 'tree'){
                this.selected.panel.addClass('content-panel'); 
            } else {
                this.selected.panel.removeClass('content-panel'); 
            }

            $contentblock = $('.content-block', this.selected.panel);

            if($contentblock.length){
                $contentblock.empty().load(url, data);
                return this;
            }
                
            return this.load(url, data); 
        },

        updateContentBlock : function(html){
            var $contentblock = $('.content-block', this.selected.panel);
            if($contentblock.length){
                $contentblock.empty().html(html);
            } else {
                this.selected.panel.empty().html(html);
            }
            return this;
        },


        on : function(eventName, cb){
            var self = this;
            this.scope.on(eventName + '.section', function(e){
                cb.apply(self, Array.prototype.slice.call(arguments, 1));
            }); 
            return this;
        }
    };

    return sectionApi;
});
