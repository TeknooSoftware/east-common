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

namespace Teknoo\East\Common\Writer;

use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

/**
 * Trait to share standard implementation of persist and delete methods of loaders
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template TSuccessArgType
 */
trait PersistTrait
{
    public function __construct(
        private ManagerInterface $manager,
        private ?DatesService $datesService = null,
        protected bool $preferRealDateOnUpdate = false,
    ) {
    }

    /**
     * @param TSuccessArgType $object
     * @param PromiseInterface<TSuccessArgType, mixed>|null $promise
     * @throws Throwable
     */
    private function persist(
        ObjectInterface $object,
        ?PromiseInterface $promise = null,
        ?bool $preferRealDateOnUpdate = null,
    ): self {
        try {
            if ($object instanceof TimestampableInterface && $this->datesService instanceof DatesService) {
                $this->datesService->passMeTheDate(
                    setter: $object->setUpdatedAt(...),
                    preferRealDate: $preferRealDateOnUpdate ?? $this->preferRealDateOnUpdate,
                );
            }

            $this->manager->persist($object);
            $this->manager->flush();

            if ($promise instanceof PromiseInterface) {
                $promise->success($object);
            }
        } catch (Throwable $error) {
            if ($promise instanceof PromiseInterface) {
                $promise->fail($error);
            } else {
                throw $error;
            }
        }

        return $this;
    }

    /**
     * @param TSuccessArgType $object
     * @param PromiseInterface<TSuccessArgType, mixed>|null $promise
     * @throws Throwable
     */
    public function remove(ObjectInterface $object, ?PromiseInterface $promise = null): WriterInterface
    {
        try {
            $this->manager->remove($object);
            $this->manager->flush();

            if ($promise instanceof PromiseInterface) {
                $promise->success();
            }
        } catch (Throwable $error) {
            if ($promise instanceof PromiseInterface) {
                $promise->fail($error);
            } else {
                throw $error;
            }
        }

        return $this;
    }
}
