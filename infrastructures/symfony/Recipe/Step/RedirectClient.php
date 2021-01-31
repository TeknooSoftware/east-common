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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\WebsiteBundle\Recipe\Step;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Website\Recipe\Step\Traits\ResponseTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RedirectClient implements RedirectClientInterface
{
    use ResponseTrait;

    private UrlGeneratorInterface $router;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        UrlGeneratorInterface $router
    ) {
        $this->responseFactory = $responseFactory;
        $this->router = $router;
    }

    /**
     * @param array<mixed, mixed> $parameters
     */
    private function generateUrl(
        string $route,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        return $this->router->generate($route, $parameters, $referenceType);
    }

    private function redirect(
        ClientInterface $client,
        string $url,
        int $status
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse($status);

        $headers = ['location' => $url ];
        $response = $this->addHeadersIntoResponse($response, $headers);

        $client->acceptResponse($response);

        return $response;
    }

    public function __invoke(
        ManagerInterface $manager,
        ClientInterface $client,
        string $route,
        int $status = 302,
        array $parameters = array()
    ): RedirectClientInterface {
        $response = $this->redirect(
            $client,
            $this->generateUrl(
                $route,
                $parameters
            ),
            $status,
        );

        $manager->finish($response);

        return $this;
    }
}