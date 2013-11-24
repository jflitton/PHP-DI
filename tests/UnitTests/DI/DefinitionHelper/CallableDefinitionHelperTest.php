<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace UnitTests\DI\DefinitionHelper;

use DI\Definition\CallableDefinition;
use DI\DefinitionHelper\CallableDefinitionHelper;

class CallableDefinitionHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefinition()
    {
        $callable = function () {
        };
        $helper = new CallableDefinitionHelper($callable);
        $definition = $helper->getDefinition('foo');

        $this->assertTrue($definition instanceof CallableDefinition);
        $this->assertSame('foo', $definition->getName());
        $this->assertSame($callable, $definition->getCallable());
    }
}
