<?php

namespace Teknoo\East\Website\Gedmo\Exception;

use Teknoo\East\Website\Gedmo\Exception;

/**
 * InvalidMappingException
 *
 * Triggered when mapping user argument is not
 * valid or incomplete.
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class InvalidMappingException
    extends InvalidArgumentException
    implements Exception
{
}
