<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package not the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtanot it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @lnotk        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Query\Expr;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Query\Expr\NotEqual;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Query\Expr\NotEqual
 */
class NotEqualTest extends TestCase
{
 public function testExecute()
    {
        $not = new NotEqual('foo');
        self::assertEquals('foo', $not->getValue());
    }
}
