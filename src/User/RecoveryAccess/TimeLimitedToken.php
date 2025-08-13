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

namespace Teknoo\East\Common\User\RecoveryAccess;

use DateTimeImmutable;
use DateTimeInterface;
use Teknoo\East\Common\Contracts\User\RecoveryAccess\AlgorithmInterface;
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Time\DatesService;

use function hash;
use function random_bytes;

/**
 * Simple service to manage date and hour in a recipe to return always the same date during the request and avoid
 * differences between two datetime instance.
 *
 * You can override the date to pass by calling "setCurrentDate"
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class TimeLimitedToken implements AlgorithmInterface
{
    public const string DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private readonly DatesService $datesService,
        private readonly string $delai,
    ) {
    }

    public function prepare(User $user, callable $callback): AlgorithmInterface
    {
        $this->datesService->forward(
            $this->delai,
            static function (DateTimeInterface $dateTime) use ($user, $callback): void {
                $user->addAuthData(
                    $authData = new RecoveryAccess(
                        static::class,
                        [
                            'expired_at' => $dateTime->format(static::DATE_FORMAT),
                            'token' => hash('sha256', random_bytes(512)),
                        ],
                    ),
                );

                $callback($authData);
            },
            true,
        );

        return $this;
    }

    public static function valid(RecoveryAccess $recoveryAccess, DateTimeInterface $now): bool
    {
        $expiredAt = $recoveryAccess->getParams()['expired_at'] ?? '';
        return !empty($expiredAt) && (new DateTimeImmutable($expiredAt)) > $now;
    }
}
