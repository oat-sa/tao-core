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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

/**
 * Track connectivity,
 * intentionally global.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'core/eventifier'
], function(eventifier){
    'use strict';

    /**
     * @type {Boolean} the current status, true means online
     */
    var status = navigator.onLine;

    /**
     * The connectivity module
     * @typedef {connectivity}
     */
    var connectivity = eventifier({

        /**
         * Set manually as online
         * @returns {connectivity} chains
         * @fires {connectivity#online}
         * @fires {connectivity#change}
         */
        setOnline : function setOnline(){
            if(this.isOffline()){
                status = true;

                this.trigger('online')
                    .trigger('change', status);
            }
            return this;
        },

        /**
         * Set manually as offline
         * @returns {connectivity} chains
         * @fires {connectivity#offline}
         * @fires {connectivity#change}
         */
        setOffline : function setOffline(){
            if(this.isOnline()){
                status = false;

                this.trigger('offline')
                    .trigger('change', status);
            }
            return this;
        },

        /**
         * Are we online ?
         * @returns {Boolean}
         */
        isOnline : function isOnline(){
            return status;
        },

        /**
         * Are we offline
         * @returns {Boolean}
         */
        isOffline : function isOffline(){
            return !status;
        }

    });

    //DOM Events : online/offline
    window.addEventListener('online',  function(){
        connectivity.setOnline();
    });
    window.addEventListener('offline', function(){
        connectivity.setOffline();
    });

    return connectivity;
});
