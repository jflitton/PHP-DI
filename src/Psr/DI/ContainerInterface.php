<?php

namespace Psr\DI;

/**
 * Describes a dependency injection container instance
 */
interface ContainerInterface
{
    /**
     * Returns an object associated to the identifier.
     * Returns null if no object is associated to this identifier.
     *
     * @param string $identifier The identifier MUST be a string.
     * @return object|null
     */
    public function get($identifier);

    /**
     * Returns true if an object associated to the identifier.
     * Returns false otherwise.
     *
     * @param string $identifier The identifier MUST be a string.
     * @return boolean
     */
    public function has($identifier);
}
