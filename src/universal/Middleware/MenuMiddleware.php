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
use Teknoo\East\Website\Service\MenuGenerator;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MenuMiddleware implements ViewParameterInterface
{
    private MenuGenerator $menuGenerator;

    public function __construct(MenuGenerator $menuGenerator)
    {
        $this->menuGenerator = $menuGenerator;
    }

    /**
     * @return array<string, mixed>
     */
    private function getViewParameters(ServerRequestInterface $request): array
    {
        return $request->getAttribute(self::REQUEST_PARAMETER_KEY, []);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(
        ClientInterface $client,
        ServerRequestInterface $request,
        ManagerInterface $manager
    ): MiddlewareInterface {
        $parameters = $this->getViewParameters($request);
        $parameters['menuGenerator'] = $this->menuGenerator;

        $request = $request->withAttribute(self::REQUEST_PARAMETER_KEY, $parameters);

        $manager->continueExecution($client, $request);

        return $this;
    }
}
