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

namespace Teknoo\East\Website\Service;

use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\CategoryLoader;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MenuGenerator
{
    /**
     * @var CategoryLoader
     */
    private $categoryLoader;

    /**
     * MenuGenerator constructor.
     * @param CategoryLoader $categoryLoader
     */
    public function __construct(CategoryLoader $categoryLoader)
    {
        $this->categoryLoader = $categoryLoader;
    }

    /**
     * @param string $location
     * @return MenuGenerator
     */
    public function extract(string $location)
    {
        $stacks = [];
        $promise = new Promise(function ($categories) use (&$stacks) {
            $stacks = $categories;
        });

        $this->categoryLoader->topBySlug($location, $promise);

        foreach ($stacks as $element) {
            yield $element;
        }

        return $this;
    }
}
