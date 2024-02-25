<?php

/*
/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Contracts\Object;

use Teknoo\East\Common\Object\Exception\BadMethodCallException;

/**
 * Interface to define a visitable object, to expose indirectly internal values / attributes to an method.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface VisitableInterface
{
    /**
     * The name of the attribute or any value' identifier (can be other than an objet's attribute). The object is not
     * mandatory to pass the value to the callable.
     *
     * You can also pass directly the name of the identified value and the callable as second parameter. The callable
     * is forbidden if $visitor is an array, and required if $visitor is a string
     *
     * @param string|array<string, callable> $visitors
     * @throws BadMethodCallException
     */
    public function visit(string|array $visitors, ?callable $callable = null): VisitableInterface;
}
