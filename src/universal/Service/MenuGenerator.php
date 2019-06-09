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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Website\Service;

use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\ItemLoader;
use Teknoo\East\Website\Query\Item\TopItemByLocationQuery;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MenuGenerator
{
    /**
     * @var ItemLoader
     */
    private $itemLoader;

    /**
     * MenuGenerator constructor.
     * @param ItemLoader $itemLoader
     */
    public function __construct(ItemLoader $itemLoader)
    {
        $this->itemLoader = $itemLoader;
    }

    /**
     * @param string $location
     * @return MenuGenerator
     */
    public function extract(string $location)
    {
        $stacks = [];
        $promise = new Promise(function ($items) use (&$stacks) {
            $stacks = $items;
        });

        $this->itemLoader->query(new TopItemByLocationQuery($location), $promise);

        foreach ($stacks as $element) {
            yield $element;
        }

        return $this;
    }
}
