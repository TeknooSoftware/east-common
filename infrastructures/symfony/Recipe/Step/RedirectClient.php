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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\Traits\ResponseTrait;

/**
 * Recipe Step to use into a HTTP EndPoint Recipe to create PSR11 response to redirect the client to a new
 * request.
 * Symfony implementation for `RedirectClientInterface`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RedirectClient implements RedirectClientInterface
{
    use ResponseTrait;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        private readonly UrlGeneratorInterface $router,
    ) {
        $this->responseFactory = $responseFactory;
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
        array $parameters = []
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
