<?php

namespace Cityware\Utility\Recursivity\Tree;

/**
 * Exception which will be thrown if a tree node references a parent
 * node (throught the parent ID) which does not exist.
 *
 * https://github.com/BlueM/Tree
 * @author Carsten Bluem <carsten@bluem.net>
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class InvalidParentException extends \RuntimeException
{

}
