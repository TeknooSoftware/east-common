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

namespace Teknoo\East\WebsiteBundle\AdminEndPoint;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\RedirectingInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\FoundationBundle\EndPoint\RoutingTrait;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Service\DeletingService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class AdminDeleteEndPoint implements RedirectingInterface
{
    use RoutingTrait;
    use AdminEndPointTrait;

    private DeletingService $deletingService;

    public function setDeletingService(DeletingService $deletingService): AdminDeleteEndPoint
    {
        $this->deletingService = $deletingService;

        return $this;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        string $id,
        string $nextRoute
    ): self {
        $this->loader->load(
            $id,
            new Promise(
                function (ObjectInterface $object) use ($client, $nextRoute) {
                    $this->deletingService->delete($object);

                    $this->redirectToRoute($client, $nextRoute);
                },
                static function ($throwable) use ($client) {
                    $client->errorInRequest($throwable);
                }
            )
        );

        return $this;
    }
}
