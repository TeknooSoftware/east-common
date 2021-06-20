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
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Middleware;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Foundation\Session\SessionInterface;
use Teknoo\East\Website\View\ParametersBag;

use function is_callable;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class LocaleMiddleware
{
    public const SESSION_KEY = 'locale';
    public const MIDDLEWARE_PRIORITY = 6;

    /**
     * @var callable|null
     */
    private $translatableSetter;

    public function __construct(
        ?callable $translatableSetter = null,
        private string $defaultLocale = 'en',
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
        $returnedLocale = $this->defaultLocale;
        $session = $request->getAttribute(SessionInterface::ATTRIBUTE_KEY);
        if ($session instanceof SessionInterface) {
            $session->get(
                self::SESSION_KEY,
                new Promise(
                    function (string $locale) use (&$request, &$returnedLocale) {
                        if (is_callable($this->translatableSetter)) {
                            ($this->translatableSetter)($locale);
                        }
                        $request = $request->withAttribute('locale', $locale);
                        $returnedLocale = $locale;
                    },
                    function () use (&$request) {
                        if (is_callable($this->translatableSetter)) {
                            ($this->translatableSetter)($this->defaultLocale);
                        }
                        $request = $request->withAttribute('locale', $this->defaultLocale);
                    }
                )
            );
        }

        return $returnedLocale;
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

        $bag->set('locale', $locale);

        return $this;
    }
}
