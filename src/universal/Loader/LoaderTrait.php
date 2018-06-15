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

use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait LoaderTrait
{
    /**
     * @return RepositoryInterface
     */
    abstract protected function getRepository(): RepositoryInterface;

    /**
     * @param array $criteria
     * @param PromiseInterface $promise
     * @return LoaderInterface|self
     */
    public function load(array $criteria, PromiseInterface $promise): LoaderInterface
    {
        $criteria['deletedAt'] = null;
        try {
            $entity = $this->getRepository()->findOneBy($criteria);

            if (!empty($entity)) {
                $promise->success($entity);
            } else {
                $promise->fail(new \DomainException('Object not found'));
            }
        } catch (\Throwable $exception) {
            $promise->fail($exception);
        }

        return $this;
    }
}
