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
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Writer;

use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

/**
 * Trait to share standard implementation of persist and delete methods of loaders
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @template TSuccessArgType
 */
trait PersistTrait
{
    public function __construct(
        private ManagerInterface $manager,
        private ?DatesService $datesService = null,
        protected bool $prefereRealDateOnUpdate = false,
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
        ?bool $prefereRealDateOnUpdate = null,
    ): self {
        try {
            if ($object instanceof TimestampableInterface && $this->datesService instanceof DatesService) {
                $this->datesService->passMeTheDate(
                    setter: $object->setUpdatedAt(...),
                    preferRealDate: $prefereRealDateOnUpdate ?? $this->prefereRealDateOnUpdate,
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
    public function remove(ObjectInterface $object, PromiseInterface $promise = null): WriterInterface
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
