/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'lodash', 'lib/uuid', 'layout/actions/binder', 'layout/actions/common'], function($, _, uuid, binder){

    /**
     * The data context for actions
     * @typedef {Object} ActionContext
     * @property {String} [uri] - the resource uri
     * @property {String} [classUri] - the class uri
     */

    /**
     * @exports layout/actions
     */
    var actionManager = {

        /**
         * the found actions, the key is the DOM id
         * @type Object
         */
        _actions : {},

        /**
         * contains the current data context: uri, classUri, both or none
         * @type ActionContext
         */
        _resourceContext : {},

        /**
         * Initialize the actions for the given scope. It should be done only once.
         * @constructor
         * @param {jQueryElement} [$scope = $(document)] - to scope the actions into the page
         */
        init: function init($scope){

            if($scope && $scope.length){
                this.$scope = $scope;
            } else {
                this.$scope = $(document);
            }

            this._lookup();
            this.update();
            this._listenUpdates();
            this._bind();
        },

        /** 
         * Lookup for existing actions in the page and add them to the _actions property
         * @private
         */
        _lookup : function _lookup(){
            var self = this;
            $('.actions-bar .action', this.$scope).each(function(){
    
                var $this = $(this);
                var id;
                
                //use the element id
                if($this.attr('id')){
                    id = $this.attr('id');
                } else {
                    //or generate one
                    do {
                        id = 'action-' + uuid(8, 16);
                    } while (self._actions[id]);

                    $this.attr('id', id);
                }

                self._actions[id] = {
                    name    : $this.attr('title'),
                    binding : $this.data('action'),
                    url     : $('a', $this).attr('href'),
                    context : $this.data('context'),
                    state : {
                        disabled    : $this.hasClass('disabled'),
                        hidden      : $this.hasClass('hidden')
                    }
                };
            });
        },

        /** 
         * Bind actions' events: try to execute the binding registered for this action.
         * The behavior depends on the binding name of the action.
         * @private
         */
        _bind : function _bind(){
            var self = this;
            var actionSelector = this.$scope.selector + ' .actions-bar .action';

            $(document)
              .off('click', actionSelector) 
              .on('click', actionSelector, function(e){
                e.preventDefault();
                var $this = $(this);
                var action = self._actions[$this.attr('id')];

                if(!$this.hasClass('disabled') && !$this.hasClass('hidden')){
                    binder.exec(action, self._resourceContext);
                }
            });
        }, 

        /**
         * Listen for event that could update the actions. 
         * Those events may change the current context.
         * @private
         */
        _listenUpdates : function _listenUpdates(){
            var self = this;
            var treeSelector = this.$scope.selector + ' .tree';  
 
            //listen for tree changes
            $(document)
              .off('change.taotree.actions', treeSelector)
              .on('change.taotree.actions', treeSelector, function(e, context){
                context = context || {};
                context.tree = this;
                self.update(context);
            });
        },

        /**
         * Update the current context. Context update may change the visibility of the actions.
         * @param {ActionContext} context - the new context
         */
        update : function update(context){
            var self = this;
            var current;
            
            context = context || {};
            current = context.uri ? 'instance' : context.classUri ? 'class' : 'none'; 
            
            this._resourceContext = context;

            _.forEach(this._actions, function(action, id){
                var $elt = $('#' + id); 
                    
                if( (current === 'none' && action.context !== '*') || 
                    (action.context !== '*' && action.context !== 'resource' && current !== action.context) ){

                    $elt.addClass('hidden');
                } else {
                    $elt.removeClass('hidden');
                }
            });
        }
    };
    
    return actionManager;
});
