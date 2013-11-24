<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\Definition;

use DI\Definition\AliasDefinition;
use DI\Scope;

/**
 * Test class for AliasDefinition
 */
class AliasDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $definition = new AliasDefinition('foo', 'bar');

        $this->assertEquals('foo', $definition->getName());
        $this->assertEquals('bar', $definition->getTargetEntryName());
    }

    public function testScope()
    {
        $definition = new AliasDefinition('foo', 'bar');

        $this->assertEquals(Scope::PROTOTYPE(), $definition->getScope());
    }

    public function testCacheable()
    {
        $definition = new AliasDefinition('foo', 'bar');
        $this->assertTrue($definition->isCacheable());
    }

    public function testMergeable()
    {
        $this->assertFalse(AliasDefinition::isMergeable());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testMerge()
    {
        $definition1 = new AliasDefinition('foo', 'bar');
        $definition2 = new AliasDefinition('foo', 'baz');
        $definition1->merge($definition2);
    }
}
