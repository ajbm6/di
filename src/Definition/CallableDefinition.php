<?php

namespace League\Dic\Definition;

use League\Dic\ContainerInterface;

class CallableDefinition extends AbstractDefinition implements DefinitionInterface
{

    /**
     * @var callable
     */
    protected $callable;

    /**
     * Constructor
     *
     * @param string                      $alias
     * @param callable                    $concrete
     * @param \League\Dic\ContainerInterface $container
     */
    public function __construct($alias, callable $concrete, ContainerInterface $container)
    {
        parent::__construct($alias, $container);

        $this->callable = $concrete;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $args = [])
    {
        $resolved = $this->resolveArguments($args);

        return call_user_func_array($this->callable, $resolved);
    }
}
