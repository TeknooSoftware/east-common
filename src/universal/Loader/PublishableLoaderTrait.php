<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Website\Loader;

use Doctrine\Common\Persistence\ObjectRepository;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Foundation\Promise\PromiseInterface;

/**
 * @mixin PublishableLoaderInterface
 */
trait PublishableLoaderTrait
{
    /**
     * @return ObjectRepository
     */
    abstract protected function getRepository(): ObjectRepository;

    /**
     * @param string $id
     * @param PromiseInterface $promise
     * @return LoaderInterface
     */
    public function load(string $id, PromiseInterface $promise): LoaderInterface
    {
        $entity = $this->getRepository()->find($id);

        if (!empty($entity)) {
            $promise->success($entity);
        } else {
            $promise->fail(new \DomainException('Entity not found'));
        }

        return $this;
    }

    /**
     * @param string $id
     * @param PromiseInterface $promise
     * @return PublishableLoaderInterface
     */
    public function loadPublished(string $id, PromiseInterface $promise): PublishableLoaderInterface
    {
        $promise = new Promise(function ($category) use ($promise) {
            if ($category->getPublishedAt() instanceof \DateTime) {
                $promise->success($category);
            } else {
                $promise->fail(new \DomainException('Entity not found'));
            }
        }, function (\Throwable $e) use ($promise) {
            $promise->fail($e);
        });

        $this->load($id, $promise);

        return $this;
    }
}