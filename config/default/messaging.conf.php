<?php

declare(strict_types=1);

use oat\tao\model\messaging\transportStrategy\NullSink;

// drop messages by default
return new NullSink();
