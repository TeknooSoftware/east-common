<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Recipe\Step\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Contracts\User\RecoveryAccess\AlgorithmInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Recipe\Step\User\PrepareRecoveryAccess;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PrepareRecoveryAccess::class)]
class PrepareRecoveryAccessTest extends TestCase
{
    public function testInvoke(): void
    {
        $algorithm = $this->createMock(AlgorithmInterface::class);
        $algorithm->expects($this->once())
            ->method('prepare')
            ->willReturnCallback(
                function (User $user, callable $callback) use ($algorithm): \PHPUnit\Framework\MockObject\MockObject {
                    $callback($this->createStub(AuthDataInterface::class));

                    return $algorithm;
                }
            );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        $this->assertInstanceOf(
            PrepareRecoveryAccess::class,
            (new PrepareRecoveryAccess())(
                $manager,
                $this->createStub(User::class),
                $algorithm,
            ),
        );
    }
}
