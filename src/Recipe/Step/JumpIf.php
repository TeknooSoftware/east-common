<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Recipe\Step;

use Stringable;
use Teknoo\East\Foundation\Manager\ManagerInterface;

use function is_callable;

/**
 * Special step to jump to another step when a $testValue is present in the workplan, or if a $testValue equal to
 * an expected value. If the $testValue is not null and stringable, the testValue will be casted to string.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class JumpIf
{
    protected bool $conditionMustBe = true;

    public function __invoke(
        ManagerInterface $manager,
        string $nextStep,
        mixed $testValue = null,
        mixed $expectedJumpValue = null,
    ): self {
        if ($testValue instanceof Stringable) {
            $testValue = (string) $testValue;
        }

        $conditionResult = (
            (is_callable($expectedJumpValue) && $expectedJumpValue($testValue))
            || (null === $expectedJumpValue && !empty($testValue))
            || (null !== $expectedJumpValue && !is_callable($expectedJumpValue) && $testValue === $expectedJumpValue)
        );

        if ($conditionResult === $this->conditionMustBe) {
            $manager->continue([], $nextStep);
        }

        return $this;
    }
}
