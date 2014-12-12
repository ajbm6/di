<?php
namespace OrnoTest\Assets;

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