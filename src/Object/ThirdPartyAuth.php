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

namespace Teknoo\East\Website\Object;

use Teknoo\East\Website\Contracts\User\AuthDataInterface;

/**
 * Class to defined persisted user's tokens and ids on a third party auth provider
 * This class is not dedicated to a specific protocol.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ThirdPartyAuth implements AuthDataInterface
{
    private string $type = self::class;

    private ?string $protocol = '';

    private ?string $provider = '';

    private ?string $token = '';

    private ?string $userIdentifier = '';

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(?string $protocol): ThirdPartyAuth
    {
        $this->protocol = $protocol;
        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): ThirdPartyAuth
    {
        $this->provider = $provider;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): ThirdPartyAuth
    {
        $this->token = $token;
        return $this;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(?string $userIdentifier): ThirdPartyAuth
    {
        $this->userIdentifier = $userIdentifier;
        return $this;
    }
}
