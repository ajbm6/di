<?php

namespace League\Dic\Definition;

use League\Dic\ContainerInterface;
use League\Dic\Exception;

class ClosureDefinition extends AbstractDefinition implements DefinitionInterface
{
    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * Constructor
     *
     * @param string                      $alias
     * @param \Closure                    $closure
     * @param \League\Dic\ContainerInterface $container
     */
    public function __construct($alias, \Closure $closure, ContainerInterface $container)
    {
        parent::__construct($alias, $container);

        $this->closure = $closure;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $args = [])
    {
        return call_user_func_array($this->closure, $this->resolveArguments($args));
    }
}
