<?php

require __DIR__ . '/../vendor/autoload.php';

// ConcreteCMS defines t() when booted; tests run without it.
if (!function_exists('t')) {
    function t($s, ...$a)
    {
        return $a ? vsprintf($s, $a) : $s;
    }
}
