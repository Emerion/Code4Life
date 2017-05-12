<?php

/**
 * @param mixed $val
 */
function debug($val)
{
    fwrite(STDERR, $val . PHP_EOL);
}
