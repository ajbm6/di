<?php

namespace League\Dic\Test\Asset;

class FooWithDefaultArg
{
    public $name;

    public function __construct($name = 'Phil Bennett')
    {
        $this->name = $name;
    }
}
