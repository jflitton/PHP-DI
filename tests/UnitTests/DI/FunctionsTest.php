<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI;

/**
 * Tests the helper functions.
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testObject()
    {
        $definition = \DI\object();

        $this->assertInstanceOf('DI\DefinitionHelper\ObjectDefinitionHelper', $definition);
        $this->assertEquals('entry', $definition->getDefinition('entry')->getClassName());

        $definition = \DI\object('foo');

        $this->assertInstanceOf('DI\DefinitionHelper\ObjectDefinitionHelper', $definition);
        $this->assertEquals('foo', $definition->getDefinition('entry')->getClassName());
    }

    public function testFactory()
    {
        $definition = \DI\factory(function () {
            return 42;
        });

        $this->assertInstanceOf('DI\DefinitionHelper\CallableDefinitionHelper', $definition);
        $callable = $definition->getDefinition('entry')->getCallable();
        $this->assertEquals(42, $callable());
    }

    public function testLink()
    {
        $reference = \DI\link('foo');

        $this->assertInstanceOf('DI\Definition\EntryReference', $reference);
        $this->assertEquals('foo', $reference->getName());
    }
}
