<?php

namespace League\Dic\Definition;

interface ClassDefinitionInterface extends DefinitionInterface
{
    /**
     * Add a method to be invoked
     *
     * @param  string $method
     * @param  array  $args
     * @return \League\Dic\Definition\ClassDefinitionInterface
     */
    public function withMethodCall($method, array $args = []);

    /**
     * Add multiple methods to be invoked
     *
     * @param  array $methods
     * @return \League\Dic\Definition\ClassDefinitionInterface
     */
    public function withMethodCalls(array $methods = []);
}
