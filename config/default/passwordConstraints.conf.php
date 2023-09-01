<?php

/**
 * Default config header
 */

return new \oat\tao\model\password\PasswordConstraintsService([
    'constraints' =>
        [
            'length' => 4,
            'upper'  => false,
            'lower'  => true,
            'number' => false,
            'spec'   => false
        ]
]);
