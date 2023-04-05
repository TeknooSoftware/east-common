<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package not the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtanot it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @lnotk        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Query\Expr;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Query\Expr\NotEqual;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Query\Expr\NotEqual
 */
class NotEqualTest extends TestCase
{
 public function testExecute()
    {
        $not = new NotEqual('foo');
        self::assertEquals('foo', $not->getValue());
    }
}
