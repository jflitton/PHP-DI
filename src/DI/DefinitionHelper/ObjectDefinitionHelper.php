<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\DefinitionHelper;

use DI\Definition\ClassDefinition;
use DI\Definition\ClassInjection\MethodInjection;
use DI\Definition\ClassInjection\PropertyInjection;
use DI\Scope;

/**
 * Helps defining how to create an instance of a class.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ObjectDefinitionHelper implements DefinitionHelper
{
    /**
     * @var string|null
     */
    private $className;

    /**
     * @var boolean|null
     */
    private $lazy;

    /**
     * @var Scope|null
     */
    private $scope;

    /**
     * Array of constructor parameters.
     * @var array
     */
    private $constructor = array();

    /**
     * Array of properties and their value.
     * @var array
     */
    private $properties = array();

    /**
     * Array of methods and their parameters.
     * @var array
     */
    private $methods = array();

    /**
     * @param string|null $className You can overridde the class name to use for this entry.
     */
    public function __construct($className = null)
    {
        $this->className = $className;
    }

    /**
     * Mark the object as lazy so that it is loaded only when used.
     *
     * @return ObjectDefinitionHelper
     */
    public function lazy()
    {
        $this->lazy = true;

        return $this;
    }

    /**
     * Defines the scope of the object.
     *
     * @param Scope $scope
     *
     * @return ObjectDefinitionHelper
     */
    public function withScope(Scope $scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Defines the arguments to use with the constructor.
     *
     * Usage example: ->withConstructor('param1', 'param2', 'param3')
     *
     * @param mixed $_,... List of parameters to use for calling the constructor.
     *
     * @return ObjectDefinitionHelper
     */
    public function withConstructor($_ = null)
    {
        $this->constructor = func_get_args();

        return $this;
    }

    /**
     * Defines the value to set to a property.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return ObjectDefinitionHelper
     */
    public function withProperty($property, $value)
    {
        $this->properties[$property] = $value;

        return $this;
    }

    /**
     * Defines a method to call (setter) and its arguments.
     *
     * Usage example: ->withMethod('myMethod', 'param1', 'param2', 'param3')
     *
     * @param string $method Method to call.
     * @param mixed  $_,...  List of parameters to use for calling the method.
     *
     * @return ObjectDefinitionHelper
     */
    public function withMethod($method, $_ = null)
    {
        $args = func_get_args();
        array_shift($args);
        $this->methods[$method] = $args;

        return $this;
    }

    /**
     * @param string $entryName Container entry name
     * @return ClassDefinition
     */
    public function getDefinition($entryName)
    {
        $definition = new ClassDefinition($entryName, $this->className);

        if ($this->lazy !== null) {
            $definition->setLazy($this->lazy);
        }
        if ($this->scope !== null) {
            $definition->setScope($this->scope);
        }
        if (! empty($this->constructor)) {
            $definition->setConstructorInjection(
                new MethodInjection('__construct', $this->constructor)
            );
        }
        if (! empty($this->properties)) {
            foreach ($this->properties as $property => $value) {
                $definition->addPropertyInjection(
                    new PropertyInjection($property, $value)
                );
            }
        }
        if (! empty($this->methods)) {
            foreach ($this->methods as $method => $args) {
                $definition->addMethodInjection(
                    new MethodInjection($method, $args)
                );
            }
        }

        return $definition;
    }
}
