<?php

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

namespace Teknoo\Tests\East\Common\User\RecoveryAccess;

use DateTime;
use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\User\RecoveryAccess\TimeLimitedToken;
use Teknoo\East\Foundation\Time\DatesService;
use Throwable;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\User\RecoveryAccess\TimeLimitedToken
 */
class TimeLimitedTokenTest extends TestCase
{
    public function testPrepareWithBadUser()
    {
        $this->expectException(Throwable::class);
        (new TimeLimitedToken($this->createMock(DatesService::class), '5 days',))->prepare(new stdClass());
    }

    public function testPrepare()
    {
        $datesService = $this->createMock(DatesService::class);
        $datesService->expects(self::once())
            ->method('forward')
            ->with('5 days')
            ->willReturnCallback(
                function ($delai, callable $setter, $prefereRealDate) use ($datesService): DatesService {
                    self::assertTrue($prefereRealDate);
                    $setter(new DateTime('2024-01-20 01:02:03'));

                    return $datesService;
                }
            );

        $service = new TimeLimitedToken($datesService, '5 days');
        $user = $this->createMock(User::class);

        $user->expects(self::once())
            ->method('addAuthData')
            ->willReturnCallback(
                function (AuthDataInterface|RecoveryAccess $authData) use ($user): User {
                    self::assertInstanceOf(
                        RecoveryAccess::class,
                        $authData,
                    );

                    self::assertEquals(
                        TimeLimitedToken::class,
                        $authData->getAlgorithm(),
                    );

                    self::assertEquals(
                        '2024-01-20 01:02:03',
                        $authData->getParams()['expired_at'] ?? '',
                    );

                    return $user;
                }
            );

        self::assertInstanceOf(
            TimeLimitedToken::class,
            $service->prepare($user, function () {}),
        );
    }
}
