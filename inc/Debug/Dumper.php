<?php

namespace WPDev\Debug;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class Dumper
{
    /**
     * Dump a value with elegance.
     *
     * @param  mixed  $value
     */
    public static function dump($value): void
    {
        if (class_exists(CliDumper::class)) {
            $dumper = in_array(PHP_SAPI, ['cli', 'phpdbg']) ? new CliDumper : new HtmlDumper;

            $dumper->dump((new VarCloner())->cloneVar($value));
        } else {
            var_dump($value);
        }
    }
}
