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
 * Class to defined persisted user's recovery access to authenticate it on a website to change its password
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RecoveryAccess implements AuthDataInterface
{
    private string $type = self::class;

    //Constructor promoted properties are not defined when object is created without calling constructor
    //(like with doctrine)

    private string $algorithm = '';

    /**
     * @var array<string, string>
     */
    private array $params = [];

    /**
     * @param array<string, string> $params
     */
    public function __construct(
        string $algorithm = '',
        #[SensitiveParameter]
        array $params = [],
    ) {
        $this->algorithm = $algorithm;
        $this->params = $params;
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function setAlgorithm(string $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array<string, string> $params
     * @return $this
     */
    public function setParams(#[SensitiveParameter] array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function eraseCredentials(): self
    {
        $this->params = [];

        return $this;
    }
}
