/*
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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

/**
 *
 * @author dieter <dieter@taotesting.com>
 */

/*
 Input:  a, b, c, d ; 2x2 matrix (rotate, scale, shear) components
 tx, ty     ; translation components
 Output: translate  ; a 2 component vector
 rotate     ; an angle
 scale      ; a 2 component vector
 skew       ; skew factor
 Returns false if the matrix cannot be decomposed, true if it can

 Supporting functions (point is a 2 component vector):
 float  length(point)                returns the length of the passed vector
 point  normalize(point)             normalizes the length of the passed point to 1
 float  dot(point, point)            returns the dot product of the passed points
 float  atan2(float y, float x)      returns the principal value of the arc tangent of
 y/x, using the signs of both arguments to determine
 the quadrant of the return value

 Decomposition also makes use of the following function:
 point combine(point a, point b, float ascl, float bscl)
 result[0] = (ascl * a[0]) + (bscl * b[0])
 result[1] = (ascl * a[1]) + (bscl * b[1])
 return result

 // Make sure the matrix is invertible
 if ((a * d - b * c) == 0)
 return false

 // Take care of translation
 translate[0] = tx
 translate[1] = matrix[3][1]

 // Put the components into a 2x2 matrix 'mat'
 mat[0][0] = a
 mat[0][1] = b
 mat[1][0] = c
 mat[1][1] = d

 // Compute X scale factor and normalize first row.
 scale[0] = length(row[0])
 row[0] = normalize(row[0])

 // Compute shear factor and make 2nd row orthogonal to 1st.
 skew = dot(row[0], row[1])
 row[1] = combine(row[1], row[0], 1.0, -skew)

 // Now, compute Y scale and normalize 2nd row.
 scale[1] = length(row[1])
 row[1] = normalize(row[1])
 skew /= scale[1];

 // Now, get the rotation out
 rotate = atan2(mat[0][1], mat[0][0])

 return true;
 */
define([
], function () {
    'use strict';


    var ns = 'scaler';

    var className = 'scaled';

    /**
     * Figure out the vendor prefix, if any
     */
    var prefix = (function() {
        var _prefixes = ['webkit', 'ms'],
        i = _prefixes.length,
        style = window.getComputedStyle(document.body);
        
        if(style.getPropertyValue('transform')) {
            return '';
        }
        while(i--) {
            if(style[_prefixes[i] + 'Transform'] !== undefined) {
                return '-' + _prefixes[i] + '-';
            }
        }
    }());


    /**
     * Scale the container with the given factor. Factors < 1 will be filtered out.
     *
     * @param $container
     * @param {number} factor
     */
    function scale($container, factor, r) {

        var cssObj = {};

        // defaults to 1
        if(isNaN(factor)) {
            factor = 1;
        }

        // avoid negative scale factors
        if(factor < 0){
            factor = 1;
        }

        // memorize old transformation
        if(!$container.data('original-transform'))

        if(r === 'reset'){
            console.log(factor)
        }

        cssObj[prefix + 'transition'] = '1s ease-in-out';
        cssObj[prefix + 'transform']  = 'scale(' + factor + ',' + factor + ')';

        $container.css(cssObj);
        if(factor !== 1){
            $container.addClass(className);
        }
        else{
            $container.removeClass(className);
        }

        $container.trigger('scale.' + ns, { factor: factor });
    }


    /**
     * @exports
     */
    return {
        scale: scale,
        reset: function($container) {
            this.scale($container, 1, 'reset');
            $container.trigger('reset.' + ns);
        },
        toggle: function($container, factor) {
            if($container.hasClass(className)) {
               this.reset($container);
            }
            else {
                this.scale($container, factor);
            }
        }
    };
});