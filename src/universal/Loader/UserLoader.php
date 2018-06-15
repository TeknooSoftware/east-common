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
class UserLoader implements LoaderInterface
{
    use CollectionLoaderTrait,
        LoaderTrait;

    /**
     * UserLoader constructor.
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $email
     * @param PromiseInterface $promise
     * @return LoaderInterface
     */
    public function byEmail(string $email, PromiseInterface $promise): LoaderInterface
    {
        return $this->load(['email' => $email], $promise);
    }
}
