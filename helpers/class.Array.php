<?php
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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013 (update and modification) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */

/**
 * Utility class on Arrays.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 */
class tao_helpers_Array
{
    /**
     * Sort an associative array on a key criterion. Uses sort or asort PHP
     * functions to implement the sort depending on the value of the descending
     * parameter.
     *
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array input The associative array to sort.
     * @param  string field The key criterion.
     * @param  boolean (optional, default = Ascending sort) descending Descending or Ascending order.
     * @return array An associative array.
     */
    public static function sortByField($input, $field, $descending = false)
    {
        $returnValue = array();
		
		$sorted = array();
		foreach($input as $key => $value ){
			$sorted[$key] = $value[$field];
		}

		if($descending){
			arsort($sorted);
		}
		else{
			asort($sorted);
		}

		foreach ($sorted as $key => $value ){
			$returnValue[$key] = $input[$key];
		}

        return (array) $returnValue;
    }
    
    /**
     * remove duplicate from array of objects implementing the __equal() function
     * 
     * @param array $array
     * @return array $array
     */
    public static function array_unique($array)
    {
        $keys = array_keys($array);
        $toDrop = array();
        for ($i = count($keys) - 1; $i >=0 ; $i-- ) {
            for ($j = $i - 1; $j >=0 ; $j--) {
                if ($array[$keys[$i]]->__equals($array[$keys[$j]])) {
                    $toDrop[] = $keys[$i];
                    break;
                }
            }
        }
        foreach ($toDrop as $key) {
            unset($array[$key]);
        }
        return $array;
    }
    
    /**
     * Test if ann array is associative or not
     * 
     * taken from http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
     * 
     * @param array $arr
     * @return boolean
     */
    public static function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
    
    /**
     * Does an array contains only a given value.
     * 
     * Whether or not a given array contains only a given value.
     * 
     * <code>
     * // Example 1
     * $container = [1, 1, 1]; $value = 1; // true
     * $container = [1, 1, 2]; $value = 1; // false
     * </code>
     * 
     * When the $strict parameter is false, values contained in the $container array
     * will be compared with $value using the PHP == operator. Otherwise, the comparison
     * will be proceed with the PHP === operator.
     * 
     * Some particular indexes of $container can be ignored with the help of the $exceptAtIndex parameter.
     * 
     * <code>
     * // Example 2
     * $container = [1, 1, 2]; $value = 1; $exceptAtIndex = [2]; // true
     * $container = [1, 1, 2]; $value = 1; $exceptAtIndex = [1]; // false
     * </code>
     * 
     * * When $value is not a scalar value, the method returns false.
     * * When $container is empty, the method returns false.
     * 
     * @param mixed $value
     * @param array $container
     * @param boolean $strict
     * @param array $exceptAtIndex
     * @return boolean
     */
    public static function containsOnlyValue($value, array $container, $strict = false, $exceptAtIndex = array())
    {
        if (!is_scalar($value)) {
            return false;
        }
        
        if (empty($container)) {
            return false;
        }
        
        $matchCount = 0;
        
        foreach ($container as $key => $val) {
            if (in_array($key, $exceptAtIndex, true)) {
                continue;
            }
            
            $match = ($strict === false) ? $value == $val : $value === $val;
            
            if (!$match) {
                return false;
            } else {
                $matchCount++;
            }
        }
        
        return $matchCount !== 0;
    }
    
    /**
     * Does a collection of arrays contain only a given value.
     * 
     * Whether or not a given collection of arrays contain only a given $value.
     * 
     * * You can specify that some indexes of contained arrays do not have to match $value by setting the $exceptAtIndex array with the indexes to be ignored.
     * * When $exceptNContainers > 0, it is allowed that $containers contains $exceptNContainers arrays not matching $value.
     * * The $invalidContainers parameter is an optional reference that will be filled with the index of arrays from $containers that do not contain the $value value only.
     * * The $validContainers parameter is an optional reference that will be filled with the index of array from $containers that do contain the $value value only.
     * 
     * @param array $containers
     * @param mixed $value
     * @param integer $exceptNContainers
     * @param array $exceptAtIndex
     * @param array $invalidContainers
     * @param array $validContainers
     * @return boolean
     */
    public static function arraysContainOnlyValue(array $containers, $value, $exceptNContainers = 0, array $exceptAtIndex = [], array &$invalidContainers = [], array &$validContainers = [])
    {
        if ($exceptNContainers < 0) {
            $exceptNContainers = 0;
        } else {
            $exceptNContainers = intval($exceptNContainers);
        }
        
        $validCount = 0;
        $rowCount = 0;
        
        $expectedValidCount = count($containers) - intval($exceptNContainers);
        
        foreach ($containers as $row) {
            
            if (!is_array($row)) {
                return false;
            }
            
            $valid = true;
            $exceptCount = 0;
            
            for ($i = 0; $i < count($row); $i++) {
                
                if (in_array($i, $exceptAtIndex, true)) {
                    $exceptCount++;
                    continue;
                } else if ($row[$i] !== $value) {
                    $valid = false;
                    break;
                }
            }
            
            if ($exceptCount !== 0 && $exceptCount === count($row)) {
                $valid = false;
            }
            
            if ($valid == true) {
                $validContainers[] = $rowCount;
                $validCount++;
            } else {
                $invalidContainers[] = $rowCount;
            }
            
            $rowCount++;
        }
        
        return $validCount === $expectedValidCount;
    }
    
    /**
     * Detect Row with Minimum of Value(s)
     * 
     * This method helps you to dectect which is the array contained in $arrays containing
     * the less amount of specific value(s).
     * 
     * Pleae note that the detection comparison is NOT strict (using the == PHP operator).
     * 
     * @param mixed $values Can be either scalar or array.
     * @param array $arrays An array of arrays.
     * @param boolean $returnAll Wheter or not return an array of keys when some amounts of specific values are similar accross $arrays.
     * @return mixed Key(s) of the array(s) containing the less amount of $values. or false if it not possible to designate a row.
     */
    public static function minArrayCountValues($values, array $arrays, $returnAll = false)
    {
        if (!is_array($values)) {
            $values = [$values];
        }
        
        $counts = [];
        
        foreach ($arrays as $index => $arr) {
            
            $counts[$index] = 0;
            
            if (!is_array($arr)) {
                return false;
            }
            
            $arrayCountValues = array_count_values($arr);
            
            foreach ($values as $value) {
                $keys = array_keys($arrayCountValues);
                if (($search = array_search($value, $keys)) !== false) {
                    $counts[$index] += $arrayCountValues[$keys[$search]];
                }
            }
        }
        
        if (count($counts) > 0) {
            $mins = array_keys($counts, min($counts));
        
            return ($returnAll) ? $mins : $mins[0];
        } else {
            
            return false;
        }
    }
    
    /**
     * Count the Amount of Consistent Columns in Matrix
     * 
     * This method aims at counting the number of columns in a $matrix containing strictly similar values.
     * 
     * @param array $matrix An array containing exclusively arrays.
     * @param array $ignoreValues An array of values to be ignored while comparing values within columns.
     * @param array $emptyIsConsistent (optional) Consider empty columns (after ignoring values) as consistent.
     * @return mixed The amount of consistent columns in $matrix or false if $matrix is not a well formed matrix.
     */
    public static function countConsistentColumns(array $matrix, array $ignoreValues = [], $emptyIsConsistent = false)
    {
        $consistentCount = 0;
        
        if (!self::isValidMatrix($matrix)) {
            return $consistentCount;
        }
        
        for ($i = 0; $i < count($matrix[0]); $i++) {
            $column = array_column($matrix, $i);
            if (count($column) !== count($matrix)) {
                // Malformed matrix.
                return false;
            }
            
            $column = array_unique($column);
            
            foreach ($ignoreValues as $ignoreVal) {
                if (($search = array_search($ignoreVal, $column, true)) !== false) {
                    unset($column[$search]);
                }
            }
            
            if (count($column) === 1) {
                $consistentCount++;
            } elseif (count($column) === 0 && $emptyIsConsistent) {
                $consistentCount++;
            }
        }
        
        return $consistentCount;
    }

    /**
     * Checks if provided matrix satisfies requirements.
     *   - as of PHP 7.2 count() expect array or countable object and will emit an E_WARNING in other cases.
     *
     * @param array $matrix
     * @return bool
     */
    private static function isValidMatrix(array $matrix)
    {
        return is_array($matrix) && count($matrix) > 0 && is_array($matrix[0]);
    }
}
