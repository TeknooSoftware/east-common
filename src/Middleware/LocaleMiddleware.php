<?php

/*
 * East Website.
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Middleware;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Foundation\Session\SessionInterface;
use Teknoo\East\Common\View\ParametersBag;

use function is_callable;

/**
 * Middleware injected into the main East Foundation's recipe as middle ware to detect the locale/language required by
 * the user from the serveur request : from the request's parameter "locale", fallback from the session variable
 * (key "locale"), or request's attribute.
 * And register it into the session for next requests, add it as message attribute and workplan's ingredient.
 * Register also it into the view parameters bags.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LocaleMiddleware
{
    final public const SESSION_KEY = 'locale';
    final public const MIDDLEWARE_PRIORITY = 6;

    /**
     * @var callable|null
     */
    private $translatableSetter;

    public function __construct(
        ?callable $translatableSetter = null,
        private readonly string $defaultLocale = 'en',
    ) {
        $this->translatableSetter = $translatableSetter;
    }

    private function registerLocaleInSession(ServerRequestInterface $request, string $locale): LocaleMiddleware
    {
        $session = $request->getAttribute(SessionInterface::ATTRIBUTE_KEY);
        if ($session instanceof SessionInterface) {
            $session->set(self::SESSION_KEY, $locale);
        }

        return $this;
    }

    private function getLocaleFromSession(ServerRequestInterface &$request): string
    {
        $session = $request->getAttribute(SessionInterface::ATTRIBUTE_KEY);
        if (!$session instanceof SessionInterface) {
            return $this->defaultLocale;
        }

        /** @var Promise<string, string, null> $promise */
        $promise = new Promise(
            function (string $locale) use (&$request): string {
                if (is_callable($this->translatableSetter)) {
                    ($this->translatableSetter)($locale);
                }

                $request = $request->withAttribute('locale', $locale);

                return $locale;
            },
            function () use (&$request, $session): void {
                if (is_callable($this->translatableSetter)) {
                    ($this->translatableSetter)($this->defaultLocale);
                }

                $session->set(self::SESSION_KEY, $this->defaultLocale);
                $request = $request->withAttribute('locale', $this->defaultLocale);
            }
        );

        $session->get(
            self::SESSION_KEY,
            $promise
        );

        return (string) ($promise->fetchResult() ?? $this->defaultLocale);
    }

    public function execute(
        MessageInterface $message,
        ManagerInterface $manager,
        ParametersBag $bag,
    ): self {
        if (!$message instanceof ServerRequestInterface) {
            if (is_callable($this->translatableSetter)) {
                ($this->translatableSetter)($this->defaultLocale);
            }

            $bag->set('locale', $this->defaultLocale);
            $manager->updateWorkPlan(['locale' => $this->defaultLocale]);

            return $this;
        }

        $queryParams = $message->getQueryParams();
        if (isset($queryParams['locale'])) {
            $locale = $queryParams['locale'];
            if (is_callable($this->translatableSetter)) {
                ($this->translatableSetter)($locale);
            }

            $this->registerLocaleInSession($message, $locale);
            $message = $message->withAttribute('locale', $locale);

            $manager->updateMessage($message);
        } else {
            $locale = $this->getLocaleFromSession($message);
        }

        $manager->updateWorkPlan(['locale' => $locale]);
        $bag->set('locale', $locale);

        return $this;
    }
}
