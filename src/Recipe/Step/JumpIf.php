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

namespace Teknoo\East\Common\Recipe\Step;

use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * Special step to jump to another step when a $testValue is present in the workplan, or if a $testValue equal to
 * an expected value
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JumpIf
{
    public function __invoke(
        ManagerInterface $manager,
        string $nextStep,
        mixed $testValue = null,
        mixed $expectedJumpValue = null,
    ): self {
        if (
            (null === $expectedJumpValue && null !== $testValue)
            || (null !== $expectedJumpValue && $testValue === $expectedJumpValue)
        ) {
            $manager->continue([], $nextStep);
        }

        return $this;
    }
}
