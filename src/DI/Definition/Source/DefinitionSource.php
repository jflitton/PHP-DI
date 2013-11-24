<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;

/**
 * Source of Dependency Injection definitions
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DefinitionSource
{

    /**
     * Returns DI definition for the entry name
     * @param string $name
     * @throws DefinitionException Invalid DI definitions
     * @return Definition|null
     */
    public function getDefinition($name);

}
