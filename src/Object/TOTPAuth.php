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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Object;

use SensitiveParameter;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;

/**
 * Class to defined persisted user's top secrets to manage its Time based One Time Password (like Google Authenticator)
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class TOTPAuth implements AuthDataInterface
{
    public const PROVIDER_GOOGLE_AUTHENTICATOR = 'google_authenticator';

    //Constructor promoted properties are not defined when object is created without calling constructor
    //(like with doctrine)

    private string $type = self::class;

    private ?string $provider = '';

    private ?string $topSecret = '';

    private string $algorithm = 'sha1';

    private int $period = 30;

    private int $digits = 6;

    private bool $enabled = false;

    public function __construct(
        ?string $provider = '',
        #[SensitiveParameter]
        ?string $topSecret = '',
        string $algorithm = 'sha1',
        int $period = 30,
        int $digits = 6,
        bool $enabled = false,
    ) {
        $this->provider = $provider;
        $this->topSecret = $topSecret;
        $this->algorithm = $algorithm;
        $this->period = $period;
        $this->digits = $digits;
        $this->enabled = $enabled;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): TOTPAuth
    {
        $this->provider = $provider;

        return $this;
    }

    public function getTopSecret(): ?string
    {
        return $this->topSecret;
    }

    public function setTopSecret(?string $topSecret): TOTPAuth
    {
        $this->topSecret = $topSecret;

        return $this;
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function setAlgorithm(string $algorithm): TOTPAuth
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    public function getPeriod(): int
    {
        return $this->period;
    }

    public function setPeriod(int $period): TOTPAuth
    {
        $this->period = $period;

        return $this;
    }

    public function getDigits(): int
    {
        return $this->digits;
    }

    public function setDigits(int $digits): TOTPAuth
    {
        $this->digits = $digits;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): TOTPAuth
    {
        $this->enabled = $enabled;

        return $this;
    }
}
