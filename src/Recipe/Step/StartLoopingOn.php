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

use Iterator;
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;

use function is_object;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class StartLoopingOn
{
    private ?Iterator $currentIterator = null;

    private bool $ended = true;

    public function __construct(
        private readonly string $endStepName = EndLooping::class,
        private readonly string $loopName = self::class,
        private readonly ?string $keyValue = null,
    ) {
    }

    /**
     * @param iterable<mixed> $collection
     */
    private function getIterator(iterable $collection): Iterator
    {
        if (null === $this->currentIterator) {
            $this->currentIterator = (fn () => yield from $collection)();
            $this->ended = false;
        }

        return $this->currentIterator;
    }

    /**
     * @param iterable<mixed> $collection
     */
    private function doLoop(
        ManagerInterface $manager,
        iterable $collection
    ): self {
        $iterator = $this->getIterator($collection);
        $this->ended = true;

        if ($iterator->valid()) {
            $value = $iterator->current();

            $this->ended = false;

            $key = $this->keyValue;
            if (empty($key) && is_object($value)) {
                $key = $value::class;
            }

            if (empty($key)) {
                $manager->error(new RuntimeException("Error, a workplay keyname is needed for {$this->loopName}"));

                return $this;
            }

            $manager->updateWorkPlan([
                $this->loopName => $this,
                $key => $value,
            ]);
        }

        if ($this->ended) {
            $manager->continue([$this->loopName => $this], $this->endStepName);
        }

        $iterator->next();
        $this->ended = !$iterator->valid();

        return $this;
    }

    /**
     * @param iterable<mixed> $collection
     */
    public function __invoke(ManagerInterface $manager, iterable $collection): self
    {
        //To allow overide this loop directly in a plan with a dedicated class with git hint typing
        return $this->doLoop($manager, $collection);
    }

    public function isEnded(): bool
    {
        return $this->ended;
    }
}
