<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition;

use DI\Scope;

/**
 * Definition
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface Definition
{
    /**
     * Returns the name of the entry in the container
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the scope of the entry
     *
     * @return Scope
     */
    public function getScope();

    /**
     * Merge another definition into the current definition
     *
     * In case of conflicts, the latter prevails (i.e. the other definition)
     *
     * @param Definition $definition
     */
    public function merge(Definition $definition);

    /**
     * Returns true if the definition can be cached, false otherwise
     *
     * @return bool
     */
    public function isCacheable();

    /**
     * Returns true if the definition is mergeable with other definitions
     *
     * @return bool
     */
    public static function isMergeable();
}
