<?php

namespace League\Dic;

interface ContainerAwareInterface
{
    /**
     * Set a container
     *
     * @param \League\Dic\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Get the container
     *
     * @return \League\Dic\ContainerInterface
     */
    public function getContainer();
}
