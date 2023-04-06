<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
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

namespace Teknoo\East\Common\Object;

use Teknoo\East\Common\Contracts\User\AuthDataInterface;

/**
 * Class to defined persisted user's tokens and ids on a third party auth provider
 * This class is not dedicated to a specific protocol.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ThirdPartyAuth implements AuthDataInterface
{
    private string $type = self::class;

    //Constructor promoted properties are not defined when object is created without calling constructor
    //(like with doctrine)

    private ?string $protocol = '';

    private ?string $provider = '';

    private ?string $token = '';

    private ?string $userIdentifier = '';

    public function __construct(
        ?string $protocol = '',
        ?string $provider = '',
        ?string $token = '',
        ?string $userIdentifier = '',
    ) {
        $this->protocol = $protocol;
        $this->provider = $provider;
        $this->token = $token;
        $this->userIdentifier = $userIdentifier;
    }

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
