<?php

namespace League\Dic\Test\Asset;

class Bar
{
    public $baz;

    public function __construct(BazInterface $baz)
    {
        $this->baz = $baz;
    }
}
