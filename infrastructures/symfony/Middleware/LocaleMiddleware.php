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

namespace Teknoo\East\CommonBundle\Middleware;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * Recipe step to use in main East Foundation recipe as middleware, to configure the Symfony's translator with the
 * selected locale
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LocaleMiddleware
{
    final public const MIDDLEWARE_PRIORITY = 7;

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

        $locale = (string) $message->getAttribute('locale', $locale);

        if (!empty($locale)) {
            $this->translator->setLocale($locale);
        }

        return $this;
    }
}
