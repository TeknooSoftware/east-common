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
use Doctrine\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait MongoDbCollectionLoaderTrait
{
    /**
     * @return ObjectRepository|DocumentRepository
     */
    abstract protected function getRepository(): ObjectRepository;

    /**
     * @param array $criteria
     * @param array $order
     * @param int|null $limit
     * @param int|null $offset
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function prepareQuery(
        array &$criteria,
        ?array $order,
        ?int $limit,
        ?int $offset
    ) {
        $query = $this->getRepository()->createQueryBuilder();
        $query->equals($criteria);
        $query->sort($order);
        $query->limit($limit);
        $query->skip($offset);

        return $query;
    }

    /**
     * @param Builder $query
     * @return mixed
     */
    protected function executeQuery($query)
    {
        if ($query instanceof Builder) {
            $query = $query->getQuery();
        }

        return $query->execute();
    }
}
