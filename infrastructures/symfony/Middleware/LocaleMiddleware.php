<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
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
        string $locale = null,
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
