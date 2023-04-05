<?php

/*
 * East Common.
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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\EndPoint;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\EndPoint\RedirectingInterface;
use Teknoo\East\Foundation\Session\SessionInterface;
use Teknoo\East\FoundationBundle\EndPoint\RoutingTrait;

/**
 * Default east endpoint implementation to redirect a visitor to an oauth2 provider
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ConnectEndPoint implements RedirectingInterface
{
    use RoutingTrait;

    /**
     * @param array<int|string, string> $scope
     */
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly string $provider,
        private readonly array $scope = [],
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        ?SessionInterface $session = null,
    ): self {
        $oauthClient = $this->clientRegistry->getClient($this->provider);
        $provider = $oauthClient->getOAuth2Provider();

        $url = $provider->getAuthorizationUrl([
            'scope' => $this->scope,
        ]);

        // set the state (unless we're stateless)
        if ($session instanceof SessionInterface) {
            $session->set(
                OAuth2Client::OAUTH2_SESSION_STATE_KEY,
                $provider->getState()
            );
        }

        $this->redirect($client, $url);

        return $this;
    }
}
