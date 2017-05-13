<?php

/**
 * @param mixed $val
 */
function debug($val)
{
    fwrite(STDERR, var_export($val, true) . PHP_EOL);
}
