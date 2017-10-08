<?php

/**
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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Website\Middleware;

use Gedmo\Translatable\TranslatableListener;
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
    const SESSION_KEY = 'locale';

    /**
     * @var TranslatableListener
     */
    private $listenerTranslatable;

    /**
     * LocaleRouter constructor.
     * @param TranslatableListener $listenerTranslatable
     */
    public function __construct(TranslatableListener $listenerTranslatable)
    {
        $this->listenerTranslatable = $listenerTranslatable;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $locale
     * @return LocaleMiddleware
     */
    private function registerLocaleInSession(ServerRequestInterface $request, string $locale): LocaleMiddleware
    {
        $session = $request->getAttribute(SessionInterface::ATTRIBUTE_KEY);
        if ($session instanceof SessionInterface) {
            $session->set(static::SESSION_KEY, $locale);
        }

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return LocaleMiddleware
     */
    private function getLocaleFromSession(ServerRequestInterface $request): LocaleMiddleware
    {
        $session = $request->getAttribute(SessionInterface::ATTRIBUTE_KEY);
        if ($session instanceof SessionInterface) {
            $session->get(
                static::SESSION_KEY,
                new Promise(function (string $locale) {
                   $this->listenerTranslatable->setTranslatableLocale($locale);
                })
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(
        ClientInterface $client,
        ServerRequestInterface $request,
        ManagerInterface $manager
    ): MiddlewareInterface {
        if (null !== ($locale = $request->getAttribute('locale', null))) {
            $this->listenerTranslatable->setTranslatableLocale($locale);
            $this->registerLocaleInSession($request, $locale);
        } else {
            $this->getLocaleFromSession($request);
        }

        return $this;
    }
}
