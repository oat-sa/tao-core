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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\helpers;

class ArrayValidator
{
    const STR = 'string';
    const INT = 'integer';
    const FLOAT = 'float';
    const BOOL = 'boolean';
    const ARR = 'array';
    const OBJ = 'object';

    /**
     * @var array[]
     */
    private $rules = [];

    /**
     * @var bool
     */
    private $allowExtraKeys = true;

    /**
     * @var string[]
     */
    private $missedKeys;

    /**
     * @var string[]
     */
    private $typeMismatchKeys;

    /**
     * @var string[]
     */
    private $extraKeys;

    /**
     * @param int|int[]|string|string[] $key
     * @param bool $required
     * @param bool $nullable
     * @return $this
     */
    public function assertString($key, $required = true, $nullable = false)
    {
        return $this->assertType($key, self::STR, $required, $nullable);
    }

    /**
     * @param int|int[]|string|string[] $key
     * @param bool $required
     * @param bool $nullable
     * @return $this
     */
    public function assertInt($key, $required = true, $nullable = false)
    {
        return $this->assertType($key, self::INT, $required, $nullable);
    }

    /**
     * @param int|int[]|string|string[] $key
     * @param bool $required
     * @param bool $nullable
     * @return $this
     */
    public function assertFloat($key, $required = true, $nullable = false)
    {
        return $this->assertType($key, self::FLOAT, $required, $nullable);
    }

    /**
     * @param int|int[]|string|string[] $key
     * @param bool $required
     * @param bool $nullable
     * @return $this
     */
    public function assertBool($key, $required = true, $nullable = false)
    {
        return $this->assertType($key, self::BOOL, $required, $nullable);
    }

    /**
     * @param int|int[]|string|string[] $key
     * @param bool $required
     * @param bool $nullable
     * @return $this
     */
    public function assertArray($key, $required = true, $nullable = false)
    {
        return $this->assertType($key, self::ARR, $required, $nullable);
    }

    /**
     * @param int|int[]|string|string[] $key
     * @param bool $required
     * @param bool $nullable
     * @return $this
     */
    public function assertObject($key, $required = true, $nullable = false)
    {
        return $this->assertType($key, self::OBJ, $required, $nullable);
    }

    public function assertExists($key)
    {
        return $this->assertType($key, null, true, true);
    }

    /**
     * @param bool $allow
     * @return $this
     */
    public function allowExtraKeys($allow = true)
    {
        $this->allowExtraKeys = $allow;
        return $this;
    }

    public function validate($data)
    {
        $this->cleanResults();

        foreach ($this->rules as $key => $rule) {
            $this->validateKey($data, $key, $rule);
        }

        if (!$this->allowExtraKeys) {
            $this->extraKeys = array_diff(array_keys($data), array_keys($this->rules));
        }

        return $this->isValid();
    }

    public function isValid()
    {
        return count($this->missedKeys) === 0 &&
            count($this->typeMismatchKeys) === 0 &&
            count($this->extraKeys) === 0;
    }

    /**
     * @return string[]
     */
    public function getMissedKeys()
    {
        return $this->missedKeys;
    }

    /**
     * Key: mismatch key name
     * Value: error message
     * @return string[]
     */
    public function getTypeMismatchKeys()
    {
        return $this->typeMismatchKeys;
    }

    /**
     * @return string[]
     */
    public function getExtraKeys()
    {
        return $this->extraKeys;
    }

    /**
     * return string
     */
    public function getErrorMessage()
    {
        $errors = [];
        if (count($this->missedKeys) > 0) {
            $errors[] = 'missed keys: ' . implode(', ' , $this->missedKeys);
        }
        foreach ($this->typeMismatchKeys as $key => $msg) {
            $errors[] = $key . ' ' . $msg;
        }
        if (count($this->extraKeys) > 0) {
            $errors[] = 'unexpected keys: ' . implode(', ' , $this->extraKeys);
        }
        return count($errors) > 0
            ? implode('; ', $errors)
            : null;
    }

    private function cleanResults() {
        $this->missedKeys = $this->typeMismatchKeys = $this->extraKeys = [];
    }

    /**
     * @param array $data
     * @param string|int $key
     * @param array $rule
     */
    private function validateKey($data, $key, $rule)
    {
        if (!array_key_exists($key, $data)) {
            if ($rule['req']) {
                $this->missedKeys[] = $key;
            }
            return;
        }

        if ($rule['type'] === null) {
            return;
        }

        $val = $data[$key];
        if ($val === null) {
            if (!$rule['nullable']) {
                $this->typeMismatchKeys[$key] = 'is null';
            }
            return;
        }

        $type = $rule['type'];
        if (!$this->isType($val, $type)) {
            $this->typeMismatchKeys[$key] = "is not $type";
        }
    }

    /**
     * @param int|int[]|string|string[] $keys
     * @param string|null $typeName
     * @param bool $required
     * @param bool $nullable
     * @return $this
     */
    private function assertType($keys, $typeName, $required, $nullable)
    {
        $keys = (array) $keys;
        foreach ($keys as $key) {
            $this->rules[$key] = ['type' => $typeName, 'req' => $required, 'nullable' => $nullable];
        }
        return $this;
    }

    /**
     * @param mixed $value
     * @param string $type
     * @return bool
     */
    private function isType($value, $type)
    {
        switch ($type) {
            case self::STR: return is_string($value);
            case self::INT: return is_int($value);
            case self::FLOAT: return is_float($value);
            case self::BOOL: return is_bool($value);
            case self::ARR: return is_array($value);
            case self::OBJ: return is_object($value);
            default: throw new \InvalidArgumentException('Unsupported type: ' . $type);
        }
    }
}
