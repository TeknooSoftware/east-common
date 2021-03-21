<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Query\Expr;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Query\Expr\In;
use Teknoo\East\Website\Query\Expr\ObjectReference;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Query\Expr\ObjectReference
 */
class ObjectReferenceTest extends TestCase
{
 public function testExecute()
    {
        $obj = new ObjectReference($this->createMock(ObjectInterface::class));
        self::assertInstanceOf(ObjectInterface::class, $obj->getObject());
    }
}
