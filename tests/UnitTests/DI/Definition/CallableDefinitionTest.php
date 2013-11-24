<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\CallableDefinition;
use DI\Scope;

/**
 * Test class for CallableDefinition
 */
class CallableDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $callable = function () {
        };
        $definition = new CallableDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
        // Default scope
        $this->assertEquals(Scope::SINGLETON(), $definition->getScope());
    }

    /**
     * Test that the definition accepts callable (not closures)
     */
    public function testAcceptArrayCallable()
    {
        $callable = array($this, 'foo');
        $definition = new CallableDefinition('foo', $callable);

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals($callable, $definition->getCallable());
    }

    public function testScope()
    {
        $definition = new CallableDefinition('foo', function () {
        }, Scope::PROTOTYPE());

        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function testMergeable()
    {
        $this->assertFalse(CallableDefinition::isMergeable());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testMerge()
    {
        $definition1 = new CallableDefinition('foo', function () {
            return 1;
        });
        $definition2 = new CallableDefinition('foo', function () {
            return 2;
        });
        $definition1->merge($definition2);
    }
}
