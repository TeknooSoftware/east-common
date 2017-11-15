<?php

declare(strict_types=1);

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

namespace Teknoo\East\WebsiteBundle\AdminEndPoint;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class AdminListEndPoint implements EndPointInterface
{
    use EastEndPointTrait,
        AdminEndPointTrait;

    /**
     * @param ServerRequestInterface $request
     * @param ClientInterface $client
     * @param int $page
     * @param string|null $viewPath
     * @return self
     */
    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        int $page = 1,
        string $viewPath = null
    ) :AdminListEndPoint {
        if ($page < 1) {
            $page = 1;
        }

        if (null == $viewPath) {
            $viewPath = $this->viewPath;
        }

        $this->loader->loadCollection(
            [],
            new Promise(function ($objects) use ($client, $page, $viewPath) {
                $this->render(
                    $client,
                    $viewPath,
                    [
                        'objectsCollection' => $objects,
                        'page' => $page
                    ]
                );
            }, function ($error) use ($client) {
                $client->errorInRequest($error);
            }),
            [],
            15,
            ($page-1)
        );

        return $this;
    }
}
