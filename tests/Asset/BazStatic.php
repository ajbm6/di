<?php

namespace League\Dic\Test\Asset;

class BazStatic
{
    public static function baz($foo)
    {
        return $foo;
    }

    public function qux()
    {
        return 'qux';
    }
}
