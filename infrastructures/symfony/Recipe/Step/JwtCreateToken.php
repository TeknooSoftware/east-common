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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use DateTimeInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\User\JwtCreateTokenInterface;
use Teknoo\East\Common\Object\DTO\JWTConfiguration;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Recipe\Step\Exception\MissingPackageException;
use Teknoo\East\Foundation\Time\DatesService;

use function min;

/**
 * Symfony implementation of JwtCreateTokenInterface to create a JWT token for the current authenticated user, thanks
 * to the bundle `lexik/jwt-authentication-bundle`. The User must be an East Common bundle user, wrapping an East
 * Common user. The token expires at the date passed into the JWTConfiguration instance, limited to the maximum
 * number of days to live passed to this step. The token is passed to the view under the key `jwtToken`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JwtCreateToken implements JwtCreateTokenInterface
{
    use UserTrait;

    private const int SECONDS_PER_DAY = 24 * 60 * 60;

    public function __construct(
        private readonly ?JWTTokenManagerInterface $jwtManager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly DatesService $datesService,
        private readonly int $maxDaysToLive = 1,
    ) {
    }

    public function __invoke(
        ParametersBag $viewParameterBag,
        JWTConfiguration $configuration,
    ): JwtCreateTokenInterface {
        if (null === ($jwtManager = $this->jwtManager)) {
            throw new MissingPackageException(
                'No `' . JWTTokenManagerInterface::class . '`, install it with '
                    . 'composer require lexik/jwt-authentication-bundle',
            );
        }

        $symfonyUser = $this->getUser();
        if (!$symfonyUser instanceof AbstractUser) {
            return $this;
        }

        $this->datesService->passMeTheDate(
            function (
                DateTimeInterface $date,
            ) use (
                $jwtManager,
                $symfonyUser,
                $configuration,
                $viewParameterBag,
            ): void {
                $now = $date->getTimestamp();

                $maxExpiration = $now + ($this->maxDaysToLive * self::SECONDS_PER_DAY);
                $requestedExp = $configuration->expirationDate?->getTimestamp() ?? $maxExpiration;
                $exp = min($requestedExp, $maxExpiration);

                $token = $jwtManager->createFromPayload(
                    user: $symfonyUser,
                    payload: [
                        'exp' => $exp,
                    ],
                );

                $viewParameterBag->set('jwtToken', $token);
            }
        );

        return $this;
    }
}
