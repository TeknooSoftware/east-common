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

namespace Teknoo\East\Website\Query\Content;

use DateTimeInterface;
use DomainException;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\PublishableInterface;
use Teknoo\East\Website\Query\QueryInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;
use Throwable;

/**
 * Class implementing query to load a non soft-deleted Content instance from its slug, and pass result to the
 * passed promise.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class PublishedContentFromSlugQuery implements QueryInterface, ImmutableInterface
{
    use ImmutableTrait;

    private string $slug;

    public function __construct(string $slug)
    {
        $this->uniqueConstructorCheck();

        $this->slug = $slug;
    }

    public function execute(
        LoaderInterface $loader,
        RepositoryInterface $repository,
        PromiseInterface $promise
    ): QueryInterface {
        $fetchingPromise = new Promise(
            static function ($object, PromiseInterface $next) {
                if (
                    $object instanceof PublishableInterface
                    && $object->getPublishedAt() instanceof DateTimeInterface
                ) {
                    $next->success($object);
                } else {
                    $next->fail(new DomainException('Object not found'));
                }
            },
            static function (Throwable $e, PromiseInterface $next) {
                $next->fail($e);
            }
        );

        $repository->findOneBy(
            [
                'slug' => $this->slug,
                'deletedAt' => null,
            ],
            $fetchingPromise->next($promise)
        );

        return $this;
    }
}
