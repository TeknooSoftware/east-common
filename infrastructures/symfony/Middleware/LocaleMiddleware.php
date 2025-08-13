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

namespace Teknoo\East\CommonBundle\Middleware;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;

use function is_scalar;

/**
 * Recipe step to use in main East Foundation recipe as middleware, to configure the Symfony's translator with the
 * selected locale
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LocaleMiddleware
{
    final public const int MIDDLEWARE_PRIORITY = 7;

    public function __construct(
        private readonly LocaleAwareInterface $translator
    ) {
    }

    public function execute(
        MessageInterface $message,
        ?string $locale = null,
    ): self {
        if (!$message instanceof ServerRequestInterface) {
            return $this;
        }

        $locale = $message->getAttribute('locale', $locale);

        if (!empty($locale) && is_scalar($locale)) {
            $this->translator->setLocale((string) $locale);
        }

        return $this;
    }
}
