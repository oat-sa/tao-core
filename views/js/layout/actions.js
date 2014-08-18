define(['jquery', 'lodash', 'context', 'handlebars'], function($, _, context, hbs){

    
    var $container = $('#section-actions');

    var actionManager = {

            actions : [],

            load : function(){

                var self = this;

                //TODO move template to it's own file
                var actionTpl       =  hbs.compile('<li><a href="{{url}}" data-action="{{name}}" title="{{dispay}}" >{{display}}</a></li>');
 
                $.getJSON(context.root_url + 'tao/Main/getSectionActions', {
                    section   : context.section,		
                    structure : context.shownStructure,
                    ext       : context.shownExtension
                }, function(response){

                    var actions = _.reduce(response, function(res, action){
                        self.actions.push(action);
                        return res  +   actionTpl(action);
                    }, '');
                     
                    $container.html('<ul>' + actions + '</ul>').show();

                    console.log(self.actions);
                });
            },

            update : function(uri, classUri, acl){
                
            }
    };
    
    return actionManager;
});
