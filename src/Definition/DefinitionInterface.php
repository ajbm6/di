<?php

namespace League\Dic\Definition;

interface DefinitionInterface
{
    /**
     * Handle instansiation and manipulation of value and return
     *
     * @param  array $args
     * @return mixed
     */
    public function __invoke(array $args = []);

    /**
     * Add an argument to be injected
     *
     * @param  mixed $arg
     * @return \League\Dic\Definition\DefinitionInterface
     */
    public function withArgument($arg);

    /**
     * Add multiple arguments to be injected
     *
     * @param  array $args
     * @return \League\Dic\Definition\DefinitionInterface
     */
    public function withArguments(array $args);
}
