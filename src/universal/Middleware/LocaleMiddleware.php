<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Foundation\Session\SessionInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class LocaleMiddleware implements MiddlewareInterface
{
    public const SESSION_KEY = 'locale';
    public const MIDDLEWARE_PRIORITY = 6;

    /**
     * @var callable|null
     */
    private $translatableSetter;

    private string $defaultLocale = 'en';

    public function __construct(?callable $translatableSetter = null, string $defaultLocale = 'en')
    {
        $this->translatableSetter = $translatableSetter;
        $this->defaultLocale = $defaultLocale;
    }

    private function registerLocaleInSession(ServerRequestInterface $request, string $locale): LocaleMiddleware
    {
        $session = $request->getAttribute(SessionInterface::ATTRIBUTE_KEY);
        if ($session instanceof SessionInterface) {
            $session->set(static::SESSION_KEY, $locale);
        }

        return $this;
    }

    private function getLocaleFromSession(ServerRequestInterface &$request): string
    {
        $returnedLocale = $this->defaultLocale;
        $session = $request->getAttribute(SessionInterface::ATTRIBUTE_KEY);
        if ($session instanceof SessionInterface) {
            $session->get(
                static::SESSION_KEY,
                new Promise(
                    function (string $locale) use (&$request, &$returnedLocale) {
                        if (\is_callable($this->translatableSetter)) {
                            ($this->translatableSetter)($locale);
                        }
                        $request = $request->withAttribute('locale', $locale);
                        $returnedLocale = $locale;
                    },
                    function () use (&$request) {
                        if (\is_callable($this->translatableSetter)) {
                            ($this->translatableSetter)($this->defaultLocale);
                        }
                        $request = $request->withAttribute('locale', $this->defaultLocale);
                    }
                )
            );
        }

        return $returnedLocale;
    }

    /**
     * @return array<string, mixed>
     */
    private function getViewParameters(ServerRequestInterface $request): array
    {
        return $request->getAttribute(ViewParameterInterface::REQUEST_PARAMETER_KEY, []);
    }

    private function updateViewParameters(string $locale, ServerRequestInterface $request): ServerRequestInterface
    {
        $parameters = $this->getViewParameters($request);
        $parameters['locale'] = $locale;

        return $request->withAttribute(ViewParameterInterface::REQUEST_PARAMETER_KEY, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(
        ClientInterface $client,
        ServerRequestInterface $request,
        ManagerInterface $manager
    ): MiddlewareInterface {
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['locale'])) {
            $locale = $queryParams['locale'];
            if (\is_callable($this->translatableSetter)) {
                ($this->translatableSetter)($locale);
            }
            $this->registerLocaleInSession($request, $locale);
            $request = $request->withAttribute('locale', $locale);
        } else {
            $locale = $this->getLocaleFromSession($request);
        }

        $request = $this->updateViewParameters($locale, $request);

        $manager->continueExecution($client, $request);

        return $this;
    }
}
