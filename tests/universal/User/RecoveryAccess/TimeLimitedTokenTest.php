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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\User\RecoveryAccess;

use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\User\RecoveryAccess\TimeLimitedToken;
use Teknoo\East\Foundation\Time\DatesService;
use Throwable;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(TimeLimitedToken::class)]
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
        $datesService->expects($this->once())
            ->method('forward')
            ->with('5 days')
            ->willReturnCallback(
                function ($delai, callable $setter, $preferRealDate) use ($datesService): DatesService {
                    self::assertTrue($preferRealDate);
                    $setter(new DateTime('2024-01-20 01:02:03'));

                    return $datesService;
                }
            );

        $service = new TimeLimitedToken($datesService, '5 days');
        $user = $this->createMock(User::class);

        $user->expects($this->once())
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

    public function testValid()
    {
        self::assertTrue(
            TimeLimitedToken::valid(
                new RecoveryAccess(
                    algorithm: TimeLimitedToken::class,
                    params: [
                        'expired_at' => '2024-02-01',
                        'token' => hash('sha256', random_bytes(512)),
                    ],
                ),
                new DateTimeImmutable('2024-01-31'),
            )
        );

        self::assertFalse(
            TimeLimitedToken::valid(
                new RecoveryAccess(
                    algorithm: TimeLimitedToken::class,
                    params: [
                        'expired_at' => '2024-01-30',
                        'token' => hash('sha256', random_bytes(512)),
                    ],
                ),
                new DateTimeImmutable('2024-01-31'),
            )
        );

        self::assertFalse(
            TimeLimitedToken::valid(
                new RecoveryAccess(
                    algorithm: TimeLimitedToken::class,
                    params: [],
                ),
                new DateTimeImmutable('2024-01-31'),
            )
        );
    }
}
