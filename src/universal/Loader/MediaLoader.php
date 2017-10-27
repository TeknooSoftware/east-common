<?php

declare(strict_types=1);

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
use Teknoo\East\Foundation\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MediaLoader implements LoaderInterface
{
    use CollectionLoaderTrait;

    /**
     * MediaLoader constructor.
     * @param ObjectRepository $repository
     */
    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return ObjectRepository
     */
    protected function getRepository(): ObjectRepository
    {
        return $this->repository;
    }

    /**
     * @param array $criteria
     * @param PromiseInterface $promise
     * @return LoaderInterface|self
     */
    public function load(array $criteria, PromiseInterface $promise): LoaderInterface
    {
        $criteria['deletedAt'] = null;
        $entity = $this->getRepository()->findOneBy($criteria);

        if (!empty($entity)) {
            $promise->success($entity);
        } else {
            $promise->fail(new \DomainException('Object not found'));
        }

        return $this;
    }

    /**
     * @param string $id
     * @param PromiseInterface $promise
     * @return MediaLoader|PublishableLoaderInterface
     */
    public function byId(string $id, PromiseInterface $promise): MediaLoader
    {
        return $this->load(['id' => $id], $promise);
    }
}
