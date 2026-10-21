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

use DomainException;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\CheckCsrfTokenInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;

use function is_array;
use function is_string;

/**
 * Recipe step to check, with the Symfony CSRF Token Manager, the CSRF token sent with the request, in its body or in
 * its query string (to keep links usable, like Symfony's logout). The check is enabled only when the ingredient
 * `csrfTokenId` is defined, by example as a route's default value. The token is read from the field
 * `csrfTokenField` (`_token` by default).
 * An invalid or missing token stops the recipe with a 403 error. If the check is enabled but the CSRF protection
 * is not available, the recipe is also stopped.
 * Symfony implementation for `CheckCsrfTokenInterface`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class CheckCsrfToken implements CheckCsrfTokenInterface
{
    public function __construct(
        private readonly ?CsrfTokenManagerInterface $csrfTokenManager = null,
    ) {
    }

    private function getTokenValue(ServerRequestInterface $request, string $csrfTokenField): ?string
    {
        $body = $request->getParsedBody();
        if (is_array($body) && isset($body[$csrfTokenField]) && is_string($body[$csrfTokenField])) {
            return $body[$csrfTokenField];
        }

        $query = $request->getQueryParams();
        if (isset($query[$csrfTokenField]) && is_string($query[$csrfTokenField])) {
            return $query[$csrfTokenField];
        }

        return null;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ManagerInterface $manager,
        ?string $csrfTokenId = null,
        string $csrfTokenField = '_token',
    ): CheckCsrfTokenInterface {
        if (empty($csrfTokenId)) {
            return $this;
        }

        if (null === $this->csrfTokenManager) {
            $manager->error(
                new RuntimeException('The CSRF protection is not available to check the token', 500)
            );

            return $this;
        }

        $tokenValue = $this->getTokenValue($request, $csrfTokenField);
        if (
            null === $tokenValue
            || !$this->csrfTokenManager->isTokenValid(new CsrfToken($csrfTokenId, $tokenValue))
        ) {
            $manager->error(new DomainException('Invalid CSRF token', 403));
        }

        return $this;
    }
}
