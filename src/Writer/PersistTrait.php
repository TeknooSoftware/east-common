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

namespace Teknoo\East\Website\Writer;

use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\Contracts\ObjectInterface;
use Teknoo\East\Website\Object\TimestampableInterface;
use Teknoo\East\Website\Service\DatesService;
use Throwable;

/**
 * Trait to share standard implementation of persist and delete methods of loaders
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait PersistTrait
{
    public function __construct(
        private ManagerInterface $manager,
        private ?DatesService $datesService = null,
    ) {
    }

    /**
     * @throws Throwable
     */
    private function persist(ObjectInterface $object, ?PromiseInterface $promise = null): self
    {
        try {
            if ($object instanceof TimestampableInterface && $this->datesService instanceof DatesService) {
                $this->datesService->passMeTheDate($object->setUpdatedAt(...));
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
