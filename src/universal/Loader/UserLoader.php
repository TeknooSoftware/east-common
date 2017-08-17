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
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\Object\User;

class UserLoader implements LoaderInterface
{
    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * UserLoader constructor.
     * @param ObjectRepository $repository
     */
    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $criteria
     * @param PromiseInterface $promise
     * @return LoaderInterface
     */
    public function load(array $criteria, PromiseInterface $promise): LoaderInterface
    {
        $entity = $this->repository->findBy($criteria);

        if ($entity instanceof User) {
            $promise->success($entity);
        } else {
            $promise->fail(new \DomainException('Entity not found'));
        }

        return $this;
    }

    /**
     * @param string $email
     * @param PromiseInterface $promise
     * @return LoaderInterface
     */
    public function byEmail(string $email, PromiseInterface $promise): LoaderInterface
    {
        $encodedEmail = \hash('sha512', $email);
        return $this->load(['email' => $encodedEmail], $promise);
    }
}